{capture name=path}
	<a href="{ph_simpleblog::getLink()}">{l s='Blog' mod='ph_simpleblog'}</a>
	<span class="navigation-pipe">{$navigationPipe}</span> <a href="{$post->category_url}">{$post->category}</a>
	<span class="navigation-pipe">{$navigationPipe}</span> {$post->meta_title}
{/capture}
{if !$is_16}{include file="$tpl_dir./breadcrumb.tpl"}{/if}

<div class="ph_simpleblog simpleblog-single {if !empty($post->featured_image)}with-cover{else}without-cover{/if}">
	<div class="cover">
		{if $post->featured_image}
		<img src="{$post->featured_image}" alt="{$post->meta_title}" class="img-responsive" />
		{/if}
		<div class="info {if $post->featured_image}absolute{/if}">
			<h1>
				{$post->meta_title}

				{if Configuration::get('PH_BLOG_DISPLAY_LIKES')}
					<div class="blog-post-likes likes_{$post->id_simpleblog_post}" onclick="addRating({$post->id_simpleblog_post});">
						<span class="likes-nb">
							{$post->likes}
						</span>
						<span class="txt">
							{l s='likes'  mod='ph_simpleblog'}
						</span>
					</div>
				{/if}
			</h1>
			
		</div>
	</div>	
	<div class="row">
		{if Configuration::get('PH_BLOG_DISPLAY_SHARER')}
			<div class="col-md-2 post-share-buttons">
				<div class="fb-like" data-href="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" data-width="80" data-height="20" data-colorscheme="light" data-layout="box_count" data-action="like" data-show-faces="false" data-send="false"></div>

				<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}">Tweet</a>

				<div class="g-plusone" data-size="tall" data-href="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}"></div>
			</div>
		{/if}
		<div class="col-md-10">
			<div class="post-content">
				{$post->content}
			</div><!-- .post-content -->
			
			<div class="post-additional-info">
				{if Configuration::get('PH_BLOG_DISPLAY_DATE')}
					<span class="post-date">
						{l s='Posté le:' mod='ph_simpleblog'} {$post->date_add|date_format:Configuration::get('PH_BLOG_DATEFORMAT')}
					</span>
				{/if}
				
				{if Configuration::get('PH_BLOG_DISPLAY_CATEGORY')}
					<span class="post-category">
						{l s='Posté dans:' mod='ph_simpleblog'} <a href="{$post->category_url}" title="">{$post->category}</a>
					</span>
				{/if}

				{if isset($post->author) && !empty($post->author) && Configuration::get('PH_BLOG_DISPLAY_AUTHOR')}
					<span class="post-author">
						{l s='Auteur:' mod='ph_simpleblog'} {$post->author} 
					</span>
				{/if}

				{if $post->tags && Configuration::get('PH_BLOG_DISPLAY_TAGS') && isset($post->tags_list)}
					<span class="post-tags clear">
						{l s='Tags:' mod='ph_simpleblog'} 
						{foreach from=$post->tags_list item=tag name='tagsLoop'}
							{$tag}{if !$smarty.foreach.tagsLoop.last}, {/if}
						{/foreach}
					</span>
				{/if}
			</div><!-- .additional-info -->
			
			{if Configuration::get('PH_BLOG_FB_COMMENTS')}
				<div class="fb-comments" data-href="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" data-colorscheme="light" data-numposts="5" data-width="440"></div>
			{/if}
		
		</div><!--.col-md-10 -->
	</div><!-- .row -->
</div><!-- .ph_simpleblog -->

{if Configuration::get('PH_BLOG_FB_INIT')}
<script>
var lang_iso = '{$lang_iso}_{$lang_iso|@strtoupper}';
{literal}(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/"+lang_iso+"/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
{/literal}
</script>
{/if}

<script>
$(function() {
	$('body').addClass('simpleblog simpleblog-single');
});
{literal}
!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');

(function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
{/literal}
</script>
{*
		{if Configuration::get('PH_BLOG_DISPLAY_LIKES')}
		<div class="blog-post-likes likes_{$post->id_simpleblog_post}" onclick="addRating({$post->id_simpleblog_post});">
			<span class="likes-nb">
				{$post->likes}
			</span>
			<span class="txt">
				{l s='likes'  mod='ph_simpleblog'}
			</span>
		</div>
		{/if}
	*}
{if Configuration::get('PH_BLOG_DISPLAY_LIKES')}
<script>
$(function() {
	var simpleblog_post_id = $(".ph_simpleblog").data("post");
	if ($.cookie('guest_{$cookie->id_guest}_'+simpleblog_post_id) == "voted") 
	{
		$(".blog-post-likes span.likes-nb").addClass("voted");
	}
});

function addRating(simpleblog_post_id){	
	if ($.cookie('guest_{$cookie->id_guest}_'+simpleblog_post_id) != "voted") 
	{
		$.cookie('guest_{$cookie->id_guest}_'+simpleblog_post_id, 'voted');
		var request = $.ajax({
		  	type: "POST",
		  	url: baseDir + 'modules/ph_simpleblog/ajax.php',
		  	data: { 
			  	action:'addRating',
				simpleblog_post_id : simpleblog_post_id 
			},
			success: function(result){             
		    	var data = $.parseJSON(result);
				if (data.status == 'success') 
				{		
					$(".blog-post-likes span.likes-nb").text(data.message).addClass("voted");
				} 
				else 
				{
					alert(data.message);
				}
			}
		}); 		
	} 
	else 
	{
		$.cookie('guest_{$cookie->id_guest}_'+simpleblog_post_id, '');
		var request = $.ajax({
			type: "POST",
			url: baseDir + 'modules/ph_simpleblog/ajax.php',
			data: { 
			  	action:'removeRating',
				simpleblog_post_id : simpleblog_post_id 
			},
			success: function(result){             
		    	var data = $.parseJSON(result);
				if (data.status == 'success') 
				{		
					$(".blog-post-likes span.likes-nb").text(data.message).removeClass("voted");
				} 
				else 
				{
					alert(data.message);
				}
		    }
		});
	}
	return false;
}
</script>
{/if}