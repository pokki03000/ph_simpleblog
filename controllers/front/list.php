<?php
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/ph_simpleblog.php';

class PH_SimpleBlogListModuleFrontController extends ModuleFrontController
{
	public $sb_category;
	public $simpleblog_search;
	public $simpleblog_keyword;
	public $is_search = false;

	public $posts_per_page;
	public $n;
	public $p;

	public function init()
	{
		parent::init();

		$sb_category = Tools::getValue('sb_category');
		$simpleblog_search = Tools::getValue('simpleblog_search');
		$simpleblog_keyword = Tools::getValue('simpleblog_keyword');

		if($sb_category)
			$this->sb_category = $sb_category;

		if($simpleblog_search && $simpleblog_keyword)
		{
			$this->simpleblog_search = $simpleblog_search;
			$this->simpleblog_keyword = $simpleblog_keyword;
			$this->is_search = true;
		}
	}

	public function initContent()
  	{

  		$sidebar = Configuration::get('PH_BLOG_LAYOUT');

  		if($sidebar == 'left_sidebar')
  		{
  			$this->display_column_left = true;
  			$this->display_column_right = false;
  		}
  		elseif($sidebar == 'right_sidebar') {
  			$this->display_column_left = false;
  			$this->display_column_right = true;
  		}
  		elseif($sidebar == 'full_width')
  		{
  			$this->display_column_left = false;
  			$this->display_column_right = false;
  		}
  		else
  		{
  			$this->display_column_left = true;
  			$this->display_column_right = true;
  		}

  		$id_lang = Context::getContext()->language->id;

	    parent::initContent();

	    $this->context->smarty->assign('is_16', (bool)(version_compare(_PS_VERSION_, '1.6.0', '>=') === true));

	    $gridType = Configuration::get('PH_BLOG_COLUMNS');
	    $gridColumns = Configuration::get('PH_BLOG_GRID_COLUMNS');
	    $blogLayout = Configuration::get('PH_BLOG_LIST_LAYOUT');

	    $gridHtmlCols = '';

	    $mainTemplate = '';

	    // First attempts to support 1.6
	    if($gridType == 'prestashop')
	    {
	    	$gridHtmlCols = 'ph_col ph_col_'.$gridColumns;
	    } elseif($gridType == 'bootstrap')
	    {
	    	$mainTemplate = '-bootstrap';
	    }

	    if($blogLayout == 'full')
	    {
	    	$gridHtmlCols = 'ph_col';
	    }

	    $this->context->smarty->assign(array(
	    	'categories' => SimpleBlogCategory::getCategories($id_lang),
	    	'latest_posts' => SimpleBlogPost::getPosts($id_lang, 5),
	    	'blogMainTitle' => Configuration::get('PH_BLOG_MAIN_TITLE', $id_lang),
	    	'columns' => $gridColumns,
	    	'grid' => Configuration::get('PH_BLOG_COLUMNS'),
	    	'gridHtmlCols' => $gridHtmlCols,
	    	'module_dir' => _MODULE_DIR_.'ph_simpleblog/',
	    	'blogLayout' => $blogLayout
    	));

	    $page = Tools::getValue('p', 0);

	    // How many posts?
	    $this->posts_per_page = Configuration::get('PH_BLOG_POSTS_PER_PAGE');

	    // Is blog category, author or something else?
	    $this->context->smarty->assign('is_category', false);
	    $this->context->smarty->assign('is_search', false);

	    // Category things
	    if($this->sb_category != '')
	    {
	    	$this->context->smarty->assign('is_category', true);

	    	$SimpleBlogCategory = SimpleBlogCategory::getByRewrite($this->sb_category, $id_lang);

	    	// Category not found so now we looking for categories in same rewrite but other languages and if we found then we redirect 301
	    	if(!Validate::isLoadedObject($SimpleBlogCategory))
	    	{
	    		$SimpleBlogCategory = SimpleBlogCategory::getByRewrite($this->sb_category, false);

	    		if(Validate::isLoadedObject($SimpleBlogCategory))
	    		{
	    			$SimpleBlogCategory = new SimpleBlogCategory($SimpleBlogCategory->id, $id_lang);
	    			header('HTTP/1.1 301 Moved Permanently');
					header('Location: '.SimpleBlogCategory::getLink($SimpleBlogCategory->link_rewrite));
	    		}
	    	}

	    	// @todo: More flexible
	    	if($SimpleBlogCategory->meta_title != '')
	    	{
	    		$meta_title = $SimpleBlogCategory->meta_title;
	    	}
	    	else
	    	{
	    		$meta_title = $SimpleBlogCategory->name. ' - Blog';
	    	}

	    	if(!empty($SimpleBlogCategory->meta_description))
	    	{
	    		$this->context->smarty->assign('meta_description', $SimpleBlogCategory->meta_description);
	    	}

	    	if(!empty($SimpleBlogCategory->meta_keywords))
	    	{
	    		$this->context->smarty->assign('meta_keywords', $SimpleBlogCategory->meta_keywords);
	    	}

	    	if($page > 1)
	    		$meta_title .= ' ('.$page.')';
	    		
	    	$this->context->smarty->assign('meta_title', $meta_title);

	    	$posts = SimpleBlogPost::getPosts($id_lang, $this->posts_per_page, $SimpleBlogCategory->id, $page);
	    	$this->assignPagination($this->posts_per_page, sizeof(SimpleBlogPost::getPosts($id_lang, null, $SimpleBlogCategory->id)));

		    $this->context->smarty->assign('blogCategory',$SimpleBlogCategory);
		    $this->context->smarty->assign('posts', $posts);
		    $this->context->smarty->assign('category_rewrite', $SimpleBlogCategory->link_rewrite);

		    //$this->setTemplate('list-category.tpl');
	    }
	    // @todo: complete refactoring "authors" to 2.0.0
	    // Posts by author
	    elseif($this->is_search)
	    {
	    	$this->context->smarty->assign('is_search', true);

		    // echo SimpleBlogPost::getSearchLink('author', 'kpodemski', $id_lang);
	    	// @todo: meta titles, blog title, specific layout
		    switch($this->simpleblog_search)
		    {
		    	case 'author':


		    	break;

		    	case 'tag':

		    	break;
		    }

	    	$this->context->smarty->assign('meta_title', $this->l('Posts by').' '.$this->simpleblog_author.' - Blog');

	    	$posts = SimpleBlogPost::findPosts($this->simpleblog_search, $this->simpleblog_keyword, $id_lang, $this->posts_per_page, $page);

	    	$this->assignPagination($this->posts_per_page, sizeof(SimpleBlogPost::findPosts($this->simpleblog_search, $this->simpleblog_keyword, $id_lang)));

	    	$this->context->smarty->assign('posts', $posts);
	    }
	    // Home things
	    else
	    {
	    	$posts = SimpleBlogPost::getPosts($id_lang, $this->posts_per_page, null, $page);

	    	$this->assignPagination($this->posts_per_page, sizeof(SimpleBlogPost::getPosts($id_lang, null)));

	    	// @todo: More flexible
	    	$meta_title = Configuration::get('PS_SHOP_NAME'). ' - Blog';

	    	if($page > 1)
	    		$meta_title .= ' ('.$page.')';
	    		
	    	$this->context->smarty->assign('meta_title', $meta_title);

	    	$this->context->smarty->assign('posts', $posts);
	    }

	    $this->setTemplate('list'.$mainTemplate.'.tpl');
	    	
  	}

  	public function assignPagination($limit, $nbPosts)
  	{
  		$this->n = $limit;
		$this->p = abs((int)Tools::getValue('p', 1));

		$current_url = tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']);
		//delete parameter page
		$current_url = preg_replace('/(\?)?(&amp;)?p=\d+/', '$1', $current_url);

		$range = 2; /* how many pages around page selected */

		if ($this->p < 1)
			$this->p = 1;

		$pages_nb = ceil($nbPosts / (int)$this->n);

		$start = (int)($this->p - $range);

		if ($start < 1)
			$start = 1;
		$stop = (int)($this->p + $range);

		if ($stop > $pages_nb)
			$stop = (int)$pages_nb;
		$this->context->smarty->assign('nb_posts', $nbPosts);
		$pagination_infos = array(
			'products_per_page' => $limit,
			'pages_nb' => $pages_nb,
			'p' => $this->p,
			'n' => $this->n,
			'range' => $range,
			'start' => $start,
			'stop' => $stop,
			'current_url' => $current_url
		);
		$this->context->smarty->assign($pagination_infos);
  	}
}