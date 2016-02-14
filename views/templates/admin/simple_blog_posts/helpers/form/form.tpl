{*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/form/form.tpl"}
{block name="input"}
	{if $input.name == "link_rewrite"}
		<script type="text/javascript">
		
		{if isset($PS_ALLOW_ACCENTED_CHARS_URL) && $PS_ALLOW_ACCENTED_CHARS_URL}
			var PS_ALLOW_ACCENTED_CHARS_URL = 1;
		{else}
			var PS_ALLOW_ACCENTED_CHARS_URL = 0;
		{/if}
		</script>
		{$smarty.block.parent}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
{block name="input"}
	{if $input.type == 'select_category'}
		<select name="id_parent">
			{$input.options.html}
		</select>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
{block name="input"}
	{if $input.type == 'select_category'}
	<select name="id_parent">
		
			{$input.options.html}
		</select>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name="input"}
	{if $input.type == 'file' && $input.name == 'cover'}
		{if isset($input.display_image) && $input.display_image}
			{if isset($fields_value.cover) && $fields_value.cover}
				<div id="image">
					{$fields_value.cover}
					<p align="center">{l s='File size'} {$fields_value.cover_size}kb</p>
					<a class="btn btn-default" href="{$current}&{$identifier}={$form_id}&token={$token}&deleteCover=1">
						<i class="icon-trash"></i> {l s='Delete'}
					</a>
				</div>
			{/if}
		{/if}
		<input type="file" name="{$input.name}" {if isset($input.id)}id="{$input.id}"{/if} />
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name="input"}
	{if $input.type == 'file' && $input.name == 'featured'}
		{if isset($input.display_image) && $input.display_image}
			{if isset($fields_value.featured) && $fields_value.featured}
				<div id="image">
					{$fields_value.featured}
					<p align="center">{l s='File size'} {$fields_value.featured_size}kb</p>
					<a class="btn btn-default" href="{$current}&{$identifier}={$form_id}&token={$token}&deleteFeatured=1">
						<i class="icon-trash"></i> {l s='Delete'}
					</a>
				</div>
			{/if}
		{/if}
		<input type="file" name="{$input.name}" {if isset($input.id)}id="{$input.id}"{/if} />
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name="input"}
	{if $input.name == "tags"}
		{if isset($input.display_tags) && $input.display_tags}
			{if $is_16}
				{foreach from=$languages item=language}
					{if $languages|count > 1}
					<div class="translatable-field row lang-{$language.id_lang}">
						<div class="col-lg-9">
					{/if}
							<input type="text"
							name="{$input.name}_{$language.id_lang}"
							value="{$simpleblogpost->getTags($language.id_lang, true)|htmlentitiesUTF8}"
							 />
					{if $languages|count > 1}
						</div>
						<div class="col-lg-2">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
								{$language.iso_code}
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								{foreach from=$languages item=language}
								<li>
									<a href="javascript:hideOtherLanguage({$language.id_lang});">{$language.name}</a>
								</li>
								{/foreach}
							</ul>
						</div>
					</div>
					{/if}
				{/foreach}
			{else}
				<div class="translatable">
					{foreach $languages as $language}
						<div class="lang_{$language.id_lang}" style="display:{if $language.id_lang == $defaultFormLanguage}block{else}none{/if}; float: left;">
							<input size="55" type="text"
							name="{$input.name}_{$language.id_lang}"
							id="{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}"
								value="{$simpleblogpost->getTags($language.id_lang, true)|htmlentitiesUTF8}" />
							<span class="hint" name="help_box">{l s='Forbidden characters:'} !&lt;;&gt;;?=+#&quot;&deg;{}_$%<span class="hint-pointer">&nbsp;</span></span>
						</div>
					{/foreach}
				</div>
			{/if}
		{/if}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name="script"}
</script>
{if $is_16}
<script>
hideOtherLanguage({$defaultFormLanguage});
</script>
{/if}
<script>
$(document).ready(function(){
	$(".copyNiceUrl").live('keyup change',function(e){
		if(!isArrowKey(e))
			return copyNiceUrl();
	});
});
function copyNiceUrl()
{
	$('#link_rewrite_' + id_language).val(str2url($('#name_' + id_language).val().replace(/^[0-9]+\./, ''), 'UTF-8'));
}	
</script>
{/block}




