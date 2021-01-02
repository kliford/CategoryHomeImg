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
						<div class="dummyfile input-group">
							<input id="{$input.name}" type="file" name="{$input.name}" class="hide" />
							<span class="input-group-addon"><i class="icon-file"></i></span>
							<input id="{$input.name}-name" type="text" class="disabled" name="filename" readonly />
							<span class="input-group-btn">
								<button id="{$input.name}-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
									<i class="icon-folder-open"></i> {l s='Choose a file' mod='categoryhomeimg'}
								</button>
							</span>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div id="{$input.name}-images-thumbnails" class="col-lg-12 block_img">
						<img src="{$uri}/images/{$fields_value[$input.name]}" class="img-thumbnail form-img"/>
					</div>
				</div>
			<script type="text/javascript">
				$(document).ready(function(){
					$('#{$input.name}-selectbutton').click(function(e) {
						$('#{$input.name}').trigger('click');
					});
					$('#{$input.name}-name').click(function(e) {
						$('#{$input.name}').trigger('click');
					});
					$('#{$input.name}-name').on('dragenter', function(e) {
						e.stopPropagation();
						e.preventDefault();
					});
					$('#{$input.name}-name').on('dragover', function(e) {
						e.stopPropagation();
						e.preventDefault();
					});
					$('#{$input.name}-name').on('drop', function(e) {
						e.preventDefault();
						var files = e.originalEvent.dataTransfer.files;
						$('#{$input.name}')[0].files = files;
						$(this).val(files[0].name);
					});
					$('#{$input.name}').change(function(e) {
						if ($(this)[0].files !== undefined)
						{
							var files = $(this)[0].files;
							var name  = '';
							$.each(files, function(index, value) {
								name += value.name+', ';
							});
							$('#{$input.name}-name').val(name.slice(0, -2));
						}
						else // Internet Explorer 9 Compatibility
						{
							var name = $(this).val().split(/[\\/]/);
							$('#{$input.name}-name').val(name[name.length-1]);
						}
					});
					if (typeof url_img_max_files !== 'undefined')
					{
						$('#{$input.name}').closest('form').on('submit', function(e) {
							if ($('#{$input.name}')[0].files.length > url_img_max_files) {
								e.preventDefault();
								alert('You can upload a maximum of  files');
							}
						});
					}
				});
			</script>
			{if isset($input.desc) && !empty($input.desc)}
				<p class="help-block">
					{$input.desc}
				</p>
			{/if}
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}