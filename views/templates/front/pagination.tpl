<!-- Pagination -->
<div id="pagination" class="pagination simpleblog-pagination">
{if $start!=$stop}
	<ul class="pagination">
	{if $p != 1}
		{assign var='p_previous' value=$p-1}
		<li id="pagination_previous" class="pagination_previous"><a href="{SimpleBlogPost::getPageLink($p_previous, $type, $rewrite)}">&laquo;&nbsp;{l s='Previous' mod='imp_simpleblog'}</a></li>
	{else}
		<li id="pagination_previous" class="disabled pagination_previous"><span>&laquo;&nbsp;{l s='Previous' mod='imp_simpleblog'}</span></li>
	{/if}
	{if $start>3}
		<li><a href="{SimpleBlogPost::getPageLink(1, $type, $rewrite)}">1</a></li>
		<li class="truncate">...</li>
	{/if}
	{section name=pagination start=$start loop=$stop+1 step=1}
		{if $p == $smarty.section.pagination.index}
			<li class="current"><span>{$p|escape:'htmlall':'UTF-8'}</span></li>
		{else}
			<li><a href="{SimpleBlogPost::getPageLink($smarty.section.pagination.index, $type, $rewrite)}">{$smarty.section.pagination.index|escape:'htmlall':'UTF-8'}</a></li>
		{/if}
	{/section}
	{if $pages_nb>$stop+2}
		<li class="truncate">...</li>
		<li><a href="{SimpleBlogPost::getPageLink($pages_nb, $type, $rewrite)}">{$pages_nb|intval}</a></li>
	{/if}
	{if $pages_nb > 1 AND $p != $pages_nb}
		{assign var='p_next' value=$p+1}
		<li id="pagination_next" class="pagination_next"><a href="{SimpleBlogPost::getPageLink($p_next, $type, $rewrite)}">{l s='Next' mod='imp_simpleblog'}&nbsp;&raquo;</a></li>
	{else}
		<li id="pagination_next" class="disabled pagination_next"><span>{l s='Next' mod='imp_simpleblog'}&nbsp;&raquo;</span></li>
	{/if}
	</ul>
{/if}
</div>
<!-- /Pagination -->		