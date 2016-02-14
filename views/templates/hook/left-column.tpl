<div id="ph_simpleblog_categories" class="block informations_block_left">
	<p class="title_block"><a href="{ph_simpleblog::getLink()}" title="{l s='Blog' mod='ph_simpleblog'}">{l s='Blog' mod='ph_simpleblog'}</a></p>
	<div class="block_content list-block">
		<ul>
			{foreach $categories AS $category}
				<li><a href="{$category['url']}" title="{l s='Link to' mod='ph_simpleblog'} {$category['name']}">{$category['name']}</a></li>
			{/foreach}
		</ul>
	</div>
</div>