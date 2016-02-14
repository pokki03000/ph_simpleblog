<?php

/*
* @author    Krystian Podemski <podemski.krystian@gmail.com>
* @site
* @copyright  Copyright (c) 2014 Krystian Podemski - www.PrestaHome.com
* @license    You only can use module, nothing more!
*/

if(!defined('THUMBLIB_BASE_PATH'))
    require_once _PS_MODULE_DIR_ . 'ph_simpleblog/assets/phpthumb/ThumbLib.inc.php';

require_once _PS_MODULE_DIR_ . 'ph_simpleblog/models/SimpleBlogHelper.php';
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/models/SimpleBlogCategory.php';
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/models/SimpleBlogPost.php';
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/models/SimpleBlogTag.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class ph_simpleblog extends Module
{
    
    public function __construct()
    {
        $this->name = 'ph_simpleblog';
        $this->tab = 'front_office_features';
        $this->version = '1.2.0.9';
        $this->author = 'www.PrestaHome.com';
        $this->need_instance = 0;
        $this->is_configurable = 1;
        $this->ps_versions_compliancy['min'] = '1.5.3.1';
        $this->ps_versions_compliancy['max'] = '1.6.1.0';
        $this->secure_key = Tools::encrypt($this->name);

        if (Shop::isFeatureActive())
        {
            Shop::addTableAssociation('simpleblog_category', array('type' => 'shop'));
            Shop::addTableAssociation('simpleblog_post', array('type' => 'shop'));
        }
        
        parent::__construct();

        $this->displayName = $this->l('Simple Blog');
        $this->description = $this->l('Adds a blog to your prestashop store');

        $this->confirmUninstall = $this->l('Are you sure you want to delete this module ?');
    }

    public function install()
    {

        // Hooks & Install
        return (parent::install() 
                && $this->prepareModuleSettings() 
                && $this->registerHook('moduleRoutes') 
                && $this->registerHook('displaySimpleBlogPosts') 
                && $this->registerHook('displaySimpleBlogCategories')
                && $this->registerHook('displayHeader') 
                && $this->registerHook('displayBackOfficeHeader')
                && $this->registerHook('displayAdminHomeQuickLinks')
                && $this->registerHook('displayLeftColumn'));
    }

    public function prepareModuleSettings()
    {
        // Database
        $sql = array();
        include (dirname(__file__) . '/init/install_sql.php');
        foreach ($sql as $s) {
            if (!Db::getInstance()->Execute($s)) {
                return false;
            }
        }

        // Tabs
        $parent_tab = new Tab();

        $parent_tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $parent_tab->name[$lang['id_lang']] = 'Simple Blog';

        $parent_tab->class_name = 'AdminSimpleBlog';
        $parent_tab->id_parent = 0;
        $parent_tab->module = $this->name;
        $parent_tab->add();

        // Categories
        $tab = new Tab();       

        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = 'Categories';

        $tab->class_name = 'AdminSimpleBlogCategories';
        $tab->id_parent = $parent_tab->id;
        $tab->module = $this->name;
        $tab->add();

        // Posts
        $tab = new Tab();       
        
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = 'Posts';

        $tab->class_name = 'AdminSimpleBlogPosts';
        $tab->id_parent = $parent_tab->id;
        $tab->module = $this->name;
        $tab->add();

        // Tags
        $tab = new Tab();       
        
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = 'Tags';

        $tab->class_name = 'AdminSimpleBlogTags';
        $tab->id_parent = $parent_tab->id;
        $tab->module = $this->name;
        $tab->add();

        // Settings
        $tab = new Tab();       
        
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = 'Settings';

        $tab->class_name = 'AdminSimpleBlogSettings';
        $tab->id_parent = $parent_tab->id;
        $tab->module = $this->name;
        $tab->add();

        $id_lang = $this->context->language->id;

        $simple_blog_category = new SimpleBlogCategory();

        foreach (Language::getLanguages(true) as $lang)
            $simple_blog_category->name[$lang['id_lang']] = 'News';

        foreach (Language::getLanguages(true) as $lang)
            $simple_blog_category->link_rewrite[$lang['id_lang']] = 'news';
        $simple_blog_category->add();
        

        // Settings
        Configuration::updateValue('PH_BLOG_POSTS_PER_PAGE', '10');
        Configuration::updateValue('PH_BLOG_FB_COMMENTS', '1');
        Configuration::updateValue('PH_BLOG_SLUG', 'blog');
        Configuration::updateValue('PH_BLOG_COLUMNS', 'prestashop');
        Configuration::updateValue('PH_BLOG_LAYOUT', 'default');
        Configuration::updateValue('PH_BLOG_LIST_LAYOUT', 'grid');
        Configuration::updateValue('PH_BLOG_GRID_COLUMNS', '2');
        Configuration::updateValue('PH_BLOG_MAIN_TITLE', array($this->context->language->id => 'Blog - whats new?'));
        Configuration::updateValue('PH_BLOG_LOAD_FA', '0');

        Configuration::updateValue('PH_BLOG_DISPLAY_AUTHOR', '1');
        Configuration::updateValue('PH_BLOG_DISPLAY_DATE', '1');
        Configuration::updateValue('PH_BLOG_DISPLAY_THUMBNAIL', '1');
        Configuration::updateValue('PH_BLOG_DISPLAY_CATEGORY', '1');
        Configuration::updateValue('PH_BLOG_DISPLAY_SHARER', '1');

        Configuration::updateValue('PH_BLOG_DISPLAY_TAGS', '1');
        Configuration::updateValue('PH_BLOG_DISPLAY_DESCRIPTION', '1');

        // Thumbnails
        Configuration::updateValue('PH_BLOG_THUMB_METHOD', '1');
        Configuration::updateValue('PH_BLOG_THUMB_X', '255');
        Configuration::updateValue('PH_BLOG_THUMB_Y', '200');
        Configuration::updateValue('PH_BLOG_THUMB_X_WIDE', '535');
        Configuration::updateValue('PH_BLOG_THUMB_Y_WIDE', '350');

        // Recent Posts
        Configuration::updateValue('PH_RECENTPOSTS_NB', '4');
        Configuration::updateValue('PH_RECENTPOSTS_CAT', '0');
        Configuration::updateValue('PH_RECENTPOSTS_POSITION', 'home');
        Configuration::updateValue('PH_RECENTPOSTS_LAYOUT', 'grid');

        // @Since 1.1.4
        // Category description
        Configuration::updateValue('PH_BLOG_DISPLAY_CAT_DESC', '1');

        // @since 1.1.5
        Configuration::updateValue('PH_BLOG_POST_BY_AUTHOR', '1');

        // @since 1.1.7
        Configuration::updateValue('PH_BLOG_FB_INIT', '1');

        // @since 1.1.8
        Configuration::updateValue('PH_BLOG_DISPLAY_FEATURED', '1');

        // @since 1.1.9
        //Configuration::updateValue('PH_BLOG_INSTALL', '1');

        // @since 1.1.9.5
        Configuration::updateValue('PH_BLOG_DISPLAY_BREADCRUMBS', '1');

        // @since 1.2.0.0
        Configuration::updateValue('PH_BLOG_DISPLAY_CATEGORY_IMAGE', '1');
        Configuration::updateValue('PH_BLOG_DISPLAY_LIKES', '1');
        Configuration::updateValue('PH_BLOG_DISPLAY_VIEWS', '1');
        Configuration::updateValue('PH_CATEGORY_IMAGE_X', '535');
        Configuration::updateValue('PH_CATEGORY_IMAGE_Y', '300');
        Configuration::updateValue('PH_CATEGORY_SORTBY', 'position');

        // @since 1.2.0.6
        Configuration::updateValue('PH_BLOG_DATEFORMAT', 'd M Y, H:i');
        Configuration::updateValue('PH_RECENTPOSTS_GRID_COLUMNS', '2');
        Configuration::updateValue('PH_RELATEDPOSTS_GRID_COLUMNS', '2');

        // For theme developers - you're welcome!
        if(file_exists(_PS_MODULE_DIR_.'ph_simpleblog/init/my-install.php'))
            include_once _PS_MODULE_DIR_.'ph_simpleblog/init/my-install.php';

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        // Database
        $sql = array();
        include (dirname(__file__) . '/init/uninstall_sql.php');
        foreach ($sql as $s) {
            if (!Db::getInstance()->Execute($s)) {
                return false;
            }
        }

        // Settings
        Configuration::deleteByName('PH_BLOG_POSTS_PER_PAGE');
        Configuration::deleteByName('PH_BLOG_FB_COMMENTS');
        Configuration::deleteByName('PH_BLOG_SLUG');
        Configuration::deleteByName('PH_BLOG_COLUMNS');
        Configuration::deleteByName('PH_BLOG_LAYOUT');
        Configuration::deleteByName('PH_BLOG_GRID_COLUMNS');
        Configuration::deleteByName('PH_BLOG_MAIN_TITLE');
        Configuration::deleteByName('PH_BLOG_MAIN_TITLE');
        Configuration::deleteByName('PH_BLOG_LOAD_FA');

        Configuration::deleteByName('PH_BLOG_DISPLAY_AUTHOR');
        Configuration::deleteByName('PH_BLOG_DISPLAY_DATE');
        Configuration::deleteByName('PH_BLOG_DISPLAY_THUMBNAIL');
        Configuration::deleteByName('PH_BLOG_DISPLAY_CATEGORY');
        Configuration::deleteByName('PH_BLOG_DISPLAY_SHARER');

        Configuration::deleteByName('PH_BLOG_DISPLAY_TAGS');
        Configuration::deleteByName('PH_BLOG_DISPLAY_DESCRIPTION');

        // Thumbnails
        Configuration::deleteByName('PH_BLOG_THUMB_METHOD');
        Configuration::deleteByName('PH_BLOG_THUMB_X');
        Configuration::deleteByName('PH_BLOG_THUMB_Y');
        Configuration::deleteByName('PH_BLOG_THUMB_X_WIDE');
        Configuration::deleteByName('PH_BLOG_THUMB_Y_WIDE');

        // Recent Posts
        Configuration::deleteByName('PH_RECENTPOSTS_NB');
        Configuration::deleteByName('PH_RECENTPOSTS_CAT');
        Configuration::deleteByName('PH_RECENTPOSTS_POSITION');
        Configuration::deleteByName('PH_RECENTPOSTS_LAYOUT');

        // @Since 1.1.4
        // Category description
        Configuration::deleteByName('PH_BLOG_DISPLAY_CAT_DESC');

        // @since 1.1.5
        Configuration::deleteByName('PH_BLOG_POST_BY_AUTHOR');

        // @since 1.1.7
        Configuration::deleteByName('PH_BLOG_FB_INIT');

        // @since 1.1.8
        Configuration::deleteByName('PH_BLOG_DISPLAY_FEATURED');

        // @since 1.1.9
        Configuration::deleteByName('PH_BLOG_CSS');
        //Configuration::deleteByName('PH_BLOG_INSTALL');

        // @since 1.1.9.5
        Configuration::deleteByName('PH_BLOG_DISPLAY_BREADCRUMBS');

        // @since 1.2.0.0
        Configuration::deleteByName('PH_BLOG_DISPLAY_CATEGORY_IMAGE');
        Configuration::deleteByName('PH_BLOG_DISPLAY_LIKES');
        Configuration::deleteByName('PH_BLOG_DISPLAY_VIEWS');
        Configuration::deleteByName('PH_CATEGORY_IMAGE_X');
        Configuration::deleteByName('PH_CATEGORY_IMAGE_Y');
        Configuration::deleteByName('PH_CATEGORY_SORTBY');

        // @since 1.2.0.6
        Configuration::deleteByName('PH_BLOG_DATEFORMAT');
        Configuration::deleteByName('PH_RECENTPOSTS_GRID_COLUMNS');
        Configuration::deleteByName('PH_RELATEDPOSTS_GRID_COLUMNS');

        // Tabs
        $idTabs = array();
        $idTabs[] = Tab::getIdFromClassName('AdminSimpleBlog');
        $idTabs[] = Tab::getIdFromClassName('AdminSimpleBlogCategories');
        $idTabs[] = Tab::getIdFromClassName('AdminSimpleBlogPosts');
        $idTabs[] = Tab::getIdFromClassName('AdminSimpleBlogTags');
        $idTabs[] = Tab::getIdFromClassName('AdminSimpleBlogSettings');

        foreach ($idTabs as $idTab) {
            if ($idTab) {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }

        // For theme developers - you're welcome!
        if(file_exists(_PS_MODULE_DIR_.'ph_simpleblog/init/my-uninstall.php'))
            include_once _PS_MODULE_DIR_.'ph_simpleblog/init/my-uninstall.php';

        return true;
    }

    // public function getContent() 
    // {
    //     if(Configuration::get('PH_BLOG_INSTALL'))
    //     {

    //     $this->html = '<h2>'.$this->displayName.'</h2>';


        
    //     return $this->html;

    //     }
    // }

    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS($this->_path.'css/ph_simpleblog.css');
        $this->context->controller->addCSS($this->_path.'css/custom.css');

        if(Configuration::get('PH_BLOG_LOAD_FA')) 
        {
            $this->context->controller->addCSS($this->_path.'css/font-awesome.css');
        }

        $this->context->controller->addJS($this->_path.'js/jquery.fitvids.js');
        $this->context->controller->addJS($this->_path.'js/ph_simpleblog.js');
    }

    public function hookModuleRoutes($params)
    {

        $context = Context::getContext();
        $controller = Tools::getValue('controller', 0);

        if($controller == 'AdminSimpleBlogPosts' && isset($_GET['updatesimpleblog_post']))
            return array();

        $blog_slug = Configuration::get('PH_BLOG_SLUG');

        $my_routes = array(
            /**
                Home
            **/
            // Home list
            'module-ph_simpleblog-list' => array(
                'controller' => 'list',
                'rule' => $blog_slug,
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ph_simpleblog',
                ),
            ),
            // Home pagination
            'module-ph_simpleblog-page' => array(
                'controller' => 'page',
                'rule' => $blog_slug.'/page/{p}',
                'keywords' => array(
                    'p' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'p'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ph_simpleblog',
                ),
            ),

            /**
                Category
            **/
            
            // Category list
            'module-ph_simpleblog-category' => array(
                'controller' => 'category',
                'rule' =>       $blog_slug.'/{sb_category}',
                'keywords' => array(
                    'sb_category' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'sb_category'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ph_simpleblog',
                ),
            ),
            // Category pagination
            'module-ph_simpleblog-categorypage' => array(
                'controller' => 'categorypage',
                'rule' => $blog_slug.'/{sb_category}/page/{p}',
                'keywords' => array(
                    'p' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'p'),
                    'sb_category' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'sb_category'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ph_simpleblog',
                ),
            ),

            'module-ph_simpleblog-single' => array(
                'controller' => 'single',
                'rule' =>       $blog_slug.'/{sb_category}/{rewrite}',
                'keywords' => array(
                    'sb_category' =>       array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'sb_category'),
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'rewrite'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ph_simpleblog',
                ),
            )
            // 'ph_simpleblog_search' => array(
            //     'controller' => 'list',
            //     'rule' =>       ''.$blog_slug.'/search/{type}/{keyword}',
            //     'keywords' => array(
            //         'type' =>       array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'simpleblog_search'),
            //         'keyword' =>       array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'simpleblog_keyword'),
            //     ),
            //     'params' => array(
            //         'fc' => 'module',
            //         'module' => 'ph_simpleblog',
            //     ),
            // )
        );

        return $my_routes;
    }

    public static function myRealURL()
    {
        $force_ssl = null;
        $allow = (int)Configuration::get('PS_REWRITING_SETTINGS');
        $ssl_enable = Configuration::get('PS_SSL_ENABLED');
        $context = Context::getContext();
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;

        if (!defined('_PS_BASE_URL_'))
            define('_PS_BASE_URL_', Tools::getShopDomain(true));
        if (!defined('_PS_BASE_URL_SSL_'))
            define('_PS_BASE_URL_SSL_', Tools::getShopDomainSsl(true));

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null)
            $shop = new Shop($id_shop);
        else
            $shop = Context::getContext()->shop;

        if (isset($ssl) && $ssl === null)
        {
            if ($force_ssl === null)
                $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
            $ssl = $force_ssl;
        }

        $base = ((isset($ssl) && $ssl && $this->ssl_enable) ? 'https://'.$shop->domain_ssl : 'http://'.$shop->domain);

        $langUrl = Language::getIsoById($id_lang).'/';

        if ((!$allow && in_array($id_shop, array($context->shop->id,  null))) || !Language::isMultiLanguageActivated($id_shop) || !(int)Configuration::get('PS_REWRITING_SETTINGS', null, null, $id_shop))
            $langUrl = '';

        return $base.$shop->getBaseURI().$langUrl;
    }

    public static function getLink()
    {
        $url = self::myRealUrl();
        $dispatcher = Dispatcher::getInstance();

        return $url.$dispatcher->createUrl('module-ph_simpleblog-list');
    }

    public function hookDisplaySimpleBlogPosts($params)
    {
        return;
        
        $id_lang = $this->context->language->id;

        $posts = SimpleBlogPost::getPosts($id_lang, 5);
        $this->smarty->assign('posts', $posts);

        return $this->display(__FILE__, 'home.tpl');
    }

    public function prepareSimpleBlogCategories()
    {
        $this->context->smarty->assign(array(
            'categories' => SimpleBlogCategory::getCategories($this->context->language->id),
        ));
    }

    public function hookDisplaySimpleBlogCategories($params)
    {
        $this->prepareSimpleBlogCategories();

        if(isset($params['template']))
            return $this->display(__FILE__, $params['template'].'.tpl');
        else
            return $this->hookDisplayLeftColumn($params);
    }

    public function hookDisplayLeftColumn($params)
    {
        $this->prepareSimpleBlogCategories();

        return $this->display(__FILE__, 'left-column.tpl');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookDisplayHome($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookDisplayFooter($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookDisplayAdminHomeQuickLinks()
    {
        return $this->display(__FILE__, 'quick-links.tpl');
    }

    public function hookDisplayBackOfficeHeader()
    {
        $tab = Tools::getValue('tab', 0);
        $controller = Tools::getValue('controller', 0);

        if($controller)
            if($controller == 'AdminImportFast')
                return;

        if($tab)
            if($tab == 'AdminSelfUpgrade')
                return;

        $this->context->controller->addCSS(($this->_path).'css/simpleblog-admin.css', 'all');
    }
}
