<?php
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/ph_simpleblog.php';

class PH_SimpleBlogSingleModuleFrontController extends ModuleFrontController
{
	public $simpleblog_post_rewrite;

	public function init()
	{
		parent::init();

		$simpleblog_post_rewrite = Tools::getValue('rewrite');

		if($simpleblog_post_rewrite)
			$this->simpleblog_post_rewrite = $simpleblog_post_rewrite;
	}

	public function initContent()
	{

        $this->context->controller->addJqueryPlugin('cooki-plugin');

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

  		
    	parent::initContent();

    	$id_lang = Context::getContext()->language->id;

      	$SimpleBlogPost = SimpleBlogPost::getByRewrite($this->simpleblog_post_rewrite, $id_lang);

        $logged = (isset(Context::getContext()->customer) && Context::getContext()->customer->isLogged() ? true : false);

        if(Validate::isLoadedObject($SimpleBlogPost) && $SimpleBlogPost->logged && !$logged || Validate::isLoadedObject($SimpleBlogPost) && !$SimpleBlogPost->active)
        {
            Tools::redirect('index.php?controller=404');
        }

      	$this->context->smarty->assign('meta_title', $SimpleBlogPost->meta_title);

        if(!empty($SimpleBlogPost->meta_description))
        {
            $this->context->smarty->assign('meta_description', $SimpleBlogPost->meta_description);
        }

        if(!empty($SimpleBlogPost->meta_keywords))
        {
            $this->context->smarty->assign('meta_keywords', $SimpleBlogPost->meta_keywords);
        }
          	
      	if(!Validate::isLoadedObject($SimpleBlogPost))
        {
            $SimpleBlogPost = SimpleBlogPost::getByRewrite($this->simpleblog_post_rewrite, false);

            if(Validate::isLoadedObject($SimpleBlogPost))
            {
                $SimpleBlogPost = new SimpleBlogPost($SimpleBlogPost->id, $id_lang);
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.SimpleBlogPost::getLink($SimpleBlogPost->link_rewrite, $SimpleBlogPost->category_rewrite));
            }
            else
            {
                Tools::redirect('index.php?controller=404');
            }
        }

        $SimpleBlogPost->increaseViewsNb();
    		
        $this->context->smarty->assign('post', $SimpleBlogPost);
    		$this->context->smarty->assign('is_16', (bool)(version_compare(_PS_VERSION_, '1.6.0', '>=') === true));
    		$this->setTemplate('single.tpl');
    	}
}