{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/form/form.tpl"}
{block name="field"}
	{if $input.type == 'file'}
		<div class="col-lg-9">
				<div class="form-group">
					<div class="col-lg-6">
						<input id="{$input.name_category}" type="file" name="{$input.name_category}" class="hide" />
						<div class="dummyfile input-group">
							<span class="input-group-addon"><i class="icon-file"></i></span>
							<input id="{$input.name_category}-name" type="text" class="disabled" name="filename" readonly />
							<span class="input-group-btn">
								<button id="{$input.name_category}-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
									<i class="icon-folder-open"></i> {l s='Choose a file' mod='blockbanner'}
								</button>
							</span>
						</div>
					</div>
				</div>
				<div class="form-group">
					{if isset($fields_value[$input.id_category_home]) && $fields_value[$input.name_category] != ''}
					<div id="{$input.name_category}images-thumbnails" class="col-lg-12">
						<img src="{$uri}img/{$fields_value[$input.name_category]}" class="img-thumbnail"/>
					</div>
					{/if}
				</div>
				<script>
				$(document).ready(function(){
					$('#{$input.name_category}-selectbutton').click(function(e){
						$('#{$input.name_category}').trigger('click');
					});
					$('#{$input.name_category}').change(function(e){
						var val = $(this).val();
						var file = val.split(/[\\/]/);
						$('#{$input.name_category}-name').val(file[file.length-1]);
					});
				});
			</script>
			{if isset($input.active) && !empty($input.active)}
				<p class="help-block">
					{$input.active}
				</p>
			{/if}
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
