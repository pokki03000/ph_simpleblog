<script>
var currentBlog = '{if $is_category}category{else}home{/if}';
</script>
{if Configuration::get('PH_BLOG_DISPLAY_BREADCRUMBS')}
	{capture name=path}
		<a href="{ph_simpleblog::getLink()}">{l s='Blog' mod='ph_simpleblog'}</a>
		{if $is_category eq true}
			<span class="navigation-pipe">{$navigationPipe}</span>{$blogCategory->name}
		{/if}
	{/capture}
	{if !$is_16}{include file="$tpl_dir./breadcrumb.tpl"}{/if}
{/if}




{if isset($posts) && count($posts) > 0}
<div class="ph_simpleblog simpleblog-{if $is_category}category{else}home{/if}">
	{if $is_category eq true}
		<h1>{$blogCategory->name}</h1>

		{if Configuration::get('PH_BLOG_DISPLAY_CATEGORY_IMAGE') && isset($blogCategory->image)}
		<div class="simpleblog-category-image">
			<img src="{$blogCategory->image}" alt="{$blogCategory->name}" class="img-responsive" />
		</div>
		{/if}
		{if !empty($blogCategory->description) && Configuration::get('PH_BLOG_DISPLAY_CAT_DESC')}
		<div class="ph_cat_description">
			{$blogCategory->description}
		</div>
		{/if}
	{else}
		<h1><center>Le Blog Made in Champeyroux</center></h1>
	{/if}

	<div class="row simpleblog-posts">
        {if $is_category eq false}
        <div style="width:100%;height:107px">
            {foreach $categories AS $category}
                <a href="{$category['url']}">
                        <div class="categ">
                            <p>{$category['name']}</p>
                            <img src="/modules/ph_simpleblog/covers_cat/{$category['id']}.jpg" class="img-responsive" style="border-radius=5px" />
                        </div>
                </a>
            {/foreach}
        </div>
        {/if}

		{foreach from=$posts item=post}

			{assign var='cols' value='col-md-6 col-xs-6 col-ms-12'}

			{if $columns eq '3'}
				{assign var='cols' value='col-md-4 col-xs-4 col-ms-12'}
			{/if}

			{if $columns eq '4'}
				{assign var='cols' value='col-md-3 col-xs-3 col-ms-12'}
			{/if}

			<div class="simpleblog-post-item {if $blogLayout eq 'grid'}{$cols}{else}col-md-12{/if}">

				<div class="post-item">

					{if isset($post.banner) && Configuration::get('PH_BLOG_DISPLAY_THUMBNAIL')}
						<div class="post-thumbnail">
							<a href="{$post.url}" title="{l s='Liens vers' mod='ph_simpleblog'} {$post.meta_title}">
								{if $blogLayout eq 'full'}
									<img src="{$post.banner_wide}" alt="{$post.meta_title}" class="img-responsive" />
								{else}
									<img src="{$post.banner_thumb}" alt="{$post.meta_title}" class="img-responsive" />
								{/if}
							</a>
						</div>
					{/if}

					<div class="post-content">
						<h2>
							<a href="{$post.url}" title="{$post.meta_title}">{$post.meta_title}</a>
						</h2>
						{if Configuration::get('PH_BLOG_DISPLAY_DESCRIPTION')}
							{$post.short_content}
						{/if}
					</div>	

					<div class="post-additional-info">
						{if Configuration::get('PH_BLOG_DISPLAY_DATE')}
							<span class="post-date">
								{l s='Posté le:' mod='ph_simpleblog'} {$post.date_add|date_format:Configuration::get('PH_BLOG_DATEFORMAT')}
							</span>
						{/if}

						{if $is_category eq false && Configuration::get('PH_BLOG_DISPLAY_CATEGORY')}
							<span class="post-category">
								{l s='Posté dans:' mod='ph_simpleblog'} <a href="{$post.category_url}" title="">{$post.category}</a>
							</span>

						{/if}

						{if isset($post.author) && !empty($post.author) && Configuration::get('PH_BLOG_DISPLAY_AUTHOR')}
							<span class="post-author">
								{l s='Auteur:' mod='ph_simpleblog'} {$post.author}
							</span>
						{/if}

						{if isset($post.tags) && $post.tags && Configuration::get('PH_BLOG_DISPLAY_TAGS')}
							<span class="post-tags clear">
								{l s='Tags:' mod='ph_simpleblog'} 
								{foreach from=$post.tags item=tag name='tagsLoop'}
									{$tag}{if !$smarty.foreach.tagsLoop.last}, {/if}
								{/foreach}
							</span>
						{/if}
					</div><!-- .additional-info -->
				</div>
			</div><!-- .simpleblog-post-item -->

		{/foreach}
	</div><!-- .row -->
		
	{if $is_category}
		{include file="./pagination.tpl" rewrite=$blogCategory->link_rewrite type='category'}
	{else}
		{include file="./pagination.tpl" rewrite=false type=false}
	{/if}
</div><!-- .ph_simpleblog -->
<script>
$(window).load(function() {
	$('body').addClass('simpleblog simpleblog-'+currentBlog);
});
{if $blogLayout eq 'grid'}
$(window).load(  SimpleBlogEqualHeight  );
$(window).resize(SimpleBlogEqualHeight);

function SimpleBlogEqualHeight()
{
  	var mini = 0;
  	$('.simpleblog-post-item .post-item').each(function(){
      	if(parseInt($(this).css('height')) > mini )
      	{
        	mini = parseInt($(this).css('height'));
      	}  
  	});

  	$('.simpleblog-post-item .post-item').css('height',mini+40);  
}
{/if}
</script>
{else}
	<p class="warning alert alert-warning">{l s='There are no posts' mod='ph_simpleblog'}</p>
{/if}