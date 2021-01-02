<?php
# /modules/categoryhomeimg/categoryhomeimg.php

/**
 * Category Home Image - A Prestashop Module
 * 
 * My Module Description
 * 
 * @author Ivan Bolonnyi <ivan.bolonnyi@gmail.com>
 * @version 1.0.0
 */

if ( !defined('_PS_VERSION_') ) exit;

class CategoryHomeImg extends Module
{
	protected $default_category_view = 0;

	public function __construct()
	{
		$this->initializeModule();
	}

	public function install()
	{

		/* Adds Module */
		if (parent::install() 
			&& $this->registerHook('displayHeader') 
			&& $this->registerHook('displayTopColumn')
			&& $this->registerHook('displayHome') 
			&& $this->registerHook('actionShopDataDuplication')
			&& $this->registerHook('displayBackOfficeHeader')
		)
		{
			$shops = Shop::getContextListShopID();
			$shop_groups_list = array();

			/* Creates tables */
			$res = $this->createTables();

			/* Adds samples */
			if ($res)
				$this->installSamples();

			return (bool)$res;
		}

		return false;
	}

	public function uninstall()
	{
		/* Deletes Module */
		if (parent::uninstall())
		{
			/* Deletes tables */
			$res = $this->deleteTables();
		
			/* Unsets configuration */
			parent::uninstall()
			&& $this->uninstallTab()
			&& $this->unregisterHook('displayHeader')
			&& $this->unregisterHook('displayTopColumn')
			&& $this->unregisterHook('displayHome') 
			&& $this->unregisterHook('actionShopDataDuplication')
			&& $this->unregisterHook('displayBackOfficeHeader');
		
			return (bool)$res;
		}
		return false;
	}

	protected function createTables()
	{

		/* Slides lang configuration */
		return Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'category_img` (
			  `id_category_home` int(10) unsigned NOT NULL,
			  `name_category` varchar(255) NOT NULL,
			  `id_lang` int(10) unsigned NOT NULL,
			  `id_shop` int(10) unsigned NOT NULL,
			  `active` varchar(255) NOT NULL,
			  `url_img` varchar(255) NOT NULL,
			  `link_rewrite` varchar(255) NOT NULL,
			  PRIMARY KEY (`id_category_home`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;
		');

	}

	protected function installSamples()
	{
		$res &= Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'category_img` (id_category_home, name_category, id_shop, id_lang, link_rewrite)
			SELECT `id_category`, `name`, `id_shop`, `id_lang`, `link_rewrite`
			FROM `'._DB_PREFIX_.'category_lang`
		');

		$res &= Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'category_img` SET `active` = "0";
			UPDATE `'._DB_PREFIX_.'category_img` SET `url_img` = "default-img-category.png";
		');
	}

	protected function deleteTables()
	{

		return Db::getInstance()->execute('
			DROP TABLE IF EXISTS `'._DB_PREFIX_.'category_img`;
		');
	}

	public function getCategory($active = null)
	{
		$this->context = Context::getContext();
		$id_shop = $this->context->shop->id;
		$id_lang = $this->context->language->id;
		$helper->actions = array('edit');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'category_img`
			WHERE `id_shop` = '.(int)$id_shop.'
			AND `id_lang` = '.(int)$id_lang.'
		');
	}

	protected function getListContent($params)
	{
		$id_shop = $this->context->shop->id;
		$id_lang = $this->context->language->id;

		$content = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'category_img`
			WHERE `id_shop` = '.(int)$id_shop.'
			AND `id_lang` = '.(int)$id_lang.'
		');

		foreach ($content as $key  => $value)
		{
			$content[$key]['id_category_home'] = substr(strip_tags($value['id_category_home']), 0, 200);
			$content[$key]['name_category'] = substr(strip_tags($value['name_category']), 0, 200);
			$content[$key]['active']  = $this->displayStatus($value['active']);
			$content[$key]['url_img']  = $this->displayStatusImg($value['url_img']);
		}

		return $content;
	}

	protected function renderList()
	{
		$this->fields_list = array();
		$this->fields_list['id_category_home'] = array(
			'title' => $this->l('Category ID'),
			'type' => 'text',
			'search' => false,
			'orderby' => false,
		);

		$this->fields_list['name_category'] = array(
			'title' => $this->l('Category Name'),
			'type' => 'text',
			'search' => false,
			'orderby' => false,
		);
		$this->fields_list['active'] = array(
			'title' => $this->l('Status'),
			'type' => 'price',
			'search' => false,
			'orderby' => false,
		);
		$this->fields_list['url_img'] = array(
			'title' => $this->l('Image'),
			'type' => 'price',
			'search' => false,
			'orderby' => false,
		);

		$helper = new HelperList();
		$helper->shopLinkType = '';
		$helper->simple_header = false;
		$helper->identifier = 'id_category_home';
		$helper->actions = array('edit');
		$helper->show_toolbar = false;
		$helper->imageType = 'jpg';

		$helper->title = $this->displayName;
		$helper->table = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		$content = $this->getListContent($this->context->language->id);
			
		return $helper->generateList($content, $this->fields_list);
	}

	protected function renderForm()
	{

		$fields_form = array(
				'legend' => array(
					'title' => $this->l('Edit category block'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'hidden',
						'name' => 'id_category_home'
					),
					array(
						'type' => 'file',
						'label' => $this->l('Select a file'),
						'name' => 'url_img',
						'required' => false,
						'lang' => false,
						'desc' => sprintf($this->l('Maximum image size: %s.'), ini_get('upload_max_filesize'))
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Enable'),
						'name' => 'active',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				),
				'buttons' => array(
					array(
						'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
						'title' => $this->l('Back to list'),
						'icon' => 'process-icon-back'
					)
				)
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->module = $this;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'savecategoryhomeimg';
		//$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->toolbar_scroll = false;
		$helper->title = $this->displayName;
		$helper->tpl_vars = array(
			'uri' => $this->getPathUri(),
			'fields_value' => $this->getFormValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array(array('form' => $fields_form)));
	}

	protected function _postProcess()
	{
		$id_lang = $this->context->language->id;
		$id_category = (int)Tools::getValue('id_category_home');
		$id_active = (int)Tools::getValue('active');
		$languages = Language::getLanguages(false);
		

		$type = Tools::strtolower(Tools::substr(strrchr($_FILES['url_img']['name'], '.'), 1));
		$imagesize = @getimagesize($_FILES['url_img']['tmp_name']);

		if (isset($_FILES['url_img']) &&
		isset($_FILES['url_img']['tmp_name']) &&
		!empty($_FILES['url_img']['tmp_name']) &&
		!empty($imagesize) &&
		in_array(
			Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)), array(
				'jpg',
				'gif',
				'jpeg',
				'png'
			)
		) &&
		in_array($type, array('jpg', 'gif', 'jpeg', 'png'))
		)
		{
			$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
			$salt = sha1(microtime());

			if ($error = ImageManager::validateUpload($_FILES['url_img']))
				return $this->displayError($this->l('if-1'));
			elseif (!$temp_name || !move_uploaded_file($_FILES['url_img']['tmp_name'], $temp_name))
				return $this->displayError($this->l('if-2'));
			elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/images/'.$salt.'_'.$_FILES['url_img']['name'], null, null, $type))
				$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));
			
				if (isset($temp_name))
			{
				$newFileImg = $salt.'_'.$_FILES['url_img']['name'];
				@unlink($temp_name);
				move_uploaded_file($_FILES['url_img']['tmp_name'], $newFileImg);
				$value_img = $salt.'_'.$_FILES['url_img']['name'];
			}

			$update_images_values = true;
		}

		if ($update_images_values)
		{
			$result_url_img = Db::getInstance()->execute('
				UPDATE '._DB_PREFIX_.'category_img
				SET `url_img` = "'.$value_img.'"
				WHERE id_category_home = '.$id_category.'
				AND `id_lang` = '.(int)$id_lang
			);
		}
		
		$result_active = Db::getInstance()->execute('
			UPDATE '._DB_PREFIX_.'category_img
			SET `active` = '.$id_active.'
			WHERE id_category_home = '.$id_category.'
			AND `id_lang` = '.(int)$id_lang
		);
	}


	protected function displayStatusImg($params)
	{
		$url_img = $params == 'default-img-category.png' ? 0 : 1;
		$title = ((int)$url_img == 0 ? $this->l('Default Image') : $this->l('Custom image'));
		$icon = ((int)$url_img == 0 ? 'icon-remove' : 'icon-check');
		$class = ((int)$url_img == 0 ? 'btn-danger' : 'btn-success');
		$html = '<span class="btn '.$class.'"><i class="'.$icon.'"></i> '.$title.'</span>';

		return $html;
	}

	protected function displayStatus($active)
	{
		$title = ((int)$active == 0 ? $this->l('Disabled') : $this->l('Enabled'));
		$icon = ((int)$active == 0 ? 'icon-remove' : 'icon-check');
		$class = ((int)$active == 0 ? 'btn-danger' : 'btn-success');
		$html = '<span class="btn '.$class.'"><i class="'.$icon.'"></i> '.$title.'</span>';

		return $html;
	}

	public function getFormValues()
	{
		$languages = Language::getLanguages(false);
		$id_lang = $this->context->language->id;
		$fields = array();
		$id_category = Tools::getValue('id_category_home');

		$name_category = Db::getInstance()->getValue('
			SELECT name_category
			FROM '._DB_PREFIX_.'category_img
			WHERE id_category_home = '.$id_category.'
			AND id_land = '.(int)$id_lang
		);

		$category_active = Db::getInstance()->getValue('
			SELECT active
			FROM '._DB_PREFIX_.'category_img
			WHERE id_category_home = '.$id_category.'
			AND `id_lang` = '.(int)$id_lang
		);

		$img_url = Db::getInstance()->getValue('
			SELECT url_img
			FROM '._DB_PREFIX_.'category_img
			WHERE id_category_home = '.$id_category.'
			AND `id_lang` = '.(int)$id_lang
		);

		foreach ($languages as $lang)
		{
			$fields['url_img'][$lang['id_lang']] = $img_url;
		}

			$fields['id_category_home'] = $id_category;
			$fields['name_category'] = $name_category;
			$fields['url_img'] = $img_url;
			$fields['active'] = $category_active;
		

		return $fields;
	}
	public function getContentValues($id_lang, $id_shop)
	{

		return Db::getInstance()->executeS('
			SELECT `id_category_home`, `name_category`, `url_img`, `link_rewrite`
			FROM `'._DB_PREFIX_.'category_img`
			WHERE `id_lang` = '.(int)$id_lang.' 
			AND  `id_shop` = '.(int)$id_shop.'
			AND `active` = 1'
		);

	}

	public function hookDisplayHeader($params)
	{

		$this->context->controller->addCss($this->_path . 'views/css/categoryhomeimg.css', 'all');
		$this->context->controller->addJS(($this->_path) . 'views/js/script.js');

	}
	public function hookDisplayBackOfficeHeader()
	{
		$this->context->controller->addCss($this->_path . 'views/css/categoryhomeimg.css', 'all');
	}
		
	public function getContent()
	{
		$id_category_home = (int)Tools::getValue('id_category_home');

		if (Tools::isSubmit('savecategoryhomeimg'))
		{
			return $this->_postProcess().$this->renderList();
		}
		elseif (Tools::isSubmit('updatecategoryhomeimg'))
		{
			return $this->renderForm();
		}
		else
		{
			return $this->_postProcess().$this->renderList();
		}
	}

	public function hookdisplayTopColumn($params)
	{
		$this->context->controller->addCSS($this->_path.'categoryhomeimg.css', 'all');

		$categories = $this->getContentValues($this->context->language->id, $this->context->shop->id);
		$this->context->smarty->assign(array('categories' => $categories));

		return $this->display(__FILE__, 'categoryhomeimg.tpl');
	}

	public function hookdisplayHome($params)
	{
		$this->context->controller->addCSS($this->_path.'categoryhomeimg.css', 'all');

		$categories = $this->getContentValues($this->context->language->id, $this->context->shop->id);
		$this->context->smarty->assign(array('categories' => $categories));

		return $this->display(__FILE__, 'categoryhomeimg.tpl');
	}

	private function initializeModule()
	{
		$this->name = 'categoryhomeimg';
		$this->tab = 'front_office_features';
		$this->version = '1.0.1';
		$this->author = 'Ivan Bolonnyi';
		$this->need_instance = 1;
		$this->ps_versions_compliancy = [
			'min' => '1.6',
			'max' => _PS_VERSION_,
		];
		$this->bootstrap = true;
		
		parent::__construct();

		$this->displayName = $this->l('Category Home Image');
		$this->description = $this->l('My Module Description');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall this module ?');
	}

	/** Set module default configuration into database */
	private function initDefaultConfigurationValues()
	{
		foreach ( self::DEFAULT_CONFIGURATION as $key => $value )
		{
			if ( !Configuration::get($key) )
			{
				Configuration::updateValue($key, $value);
			}
		}

		return true;
	}

	/** Install module tab, to your admin controller */
	private function installTab()
	{
		$languages = Language::getLanguages();
		
		$tab = new Tab();
		$tab->class_name = 'AdminMyAdminExample';
		$tab->module = $this->name;
		$tab->id_parent = (int) Tab::getIdFromClassName('DEFAULT');

		foreach ( $languages as $lang )
		{
			$tab->name[$lang['id_lang']] = 'Category Home Image';
		}

		try
		{
			$tab->save();
		}
		catch ( Exception $e )
		{
			return false;
		}

		return true;
	}

	/** Uninstall module tab */
	private function uninstallTab()
	{
		$tab = (int) Tab::getIdFromClassName('AdminMyAdminExample');

		if ( $tab )
		{
			$mainTab = new Tab($tab);
			
			try
			{
				$mainTab->delete();
			}
			catch ( Exception $e )
			{
				echo $e->getMessage();
				return false;
			}
		}

		return true;
	}
}
