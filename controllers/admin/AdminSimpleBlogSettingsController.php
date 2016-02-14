<?php
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/ph_simpleblog.php';

class AdminSimpleBlogSettingsController extends ModuleAdminController
{

    public $is_16;

    // @todo - 2.0.0
    public static $grid_types = array(
        array(
            'id' => 'prestashop',
            'name' => 'PrestaShop Grid'   
        ),
        array(
            'id' => 'bootstrap',
            'name' => 'Bootstrap 3 Grid'   
        ),     
    );

    public function __construct()
    {
        

        parent::__construct();

        $this->bootstrap = true;

        $this->is_16 = (bool)(version_compare(_PS_VERSION_, '1.6.0', '>=') === true);

        $this->initOptions();
    }

    public function initOptions()
    {
        $this->optionTitle = $this->l('Settings');

        $blogCategories = SimpleBlogCategory::getCategories($this->context->language->id);

        $simpleBlogCategories = array();

        $simpleBlogCategories[0] = $this->l('All categories');
        $simpleBlogCategories[9999] = $this->l('Featured only');

        foreach($blogCategories as $key => $category)
        {
            $simpleBlogCategories[$category['id']] = $category['name'];
        }

        $recentPosts = array();

        if(Module::isInstalled('ph_recentposts'))
        {
            $recentPosts = array(
                'recent_posts' => array(
                    'submit' => array('title' => $this->l('Update'), 'class' => 'button'),
                    'title' =>  $this->l('Recent Posts widget settings'),
                    'image' =>   '../img/t/AdminOrderPreferences.gif',
                    'fields' => array(

                        'PH_RECENTPOSTS_LAYOUT' => array(
                            'title' => $this->l('Recent Posts layout:'),
                            'show' => true,
                            'required' => true,
                            'type' => 'radio',
                            'choices' => array(
                                'full' => $this->l('Full width with large images'),
                                'grid' => $this->l('Grid'),
                            )
                        ), // PH_BLOG_LIST_LAYOUT

                        'PH_RECENTPOSTS_GRID_COLUMNS' => array(
                            'title' => $this->l('Grid columns:'),
                            'cast' => 'intval',
                            'desc' => $this->l('Working only with "Recent Posts layout:" setup to "Grid"'),
                            'show' => true,
                            'required' => true,
                            'type' => 'radio',
                            'choices' => array(
                                '2' => $this->l('2 columns'),
                                '3' => $this->l('3 columns'),
                                '4' => $this->l('4 columns'),
                            )
                        ), // PH_RECENTPOSTS_GRID_COLUMNS

                        'PH_RECENTPOSTS_NB' => array(
                            'title' => $this->l('Number of displayed Recent Posts'),
                            'cast' => 'intval',
                            'desc' => $this->l('Default: 4'),
                            'type' => 'text',
                            'required' => true,
                            'validation' => 'isUnsignedId',
                        ), // PH_RECENTPOSTS_NB

                        'PH_RECENTPOSTS_CAT' => array(
                            'title' => $this->l('Category:'),
                            'cast' => 'intval',
                            'desc' => $this->l('If you not select any category then Recent Posts will displayed posts from all categories'),
                            'show' => true,
                            'required' => true,
                            'type' => 'radio',
                            'choices' => $simpleBlogCategories
                        ), // PH_BLOG_THUMB_METHOD

                    ),
                ),
            );
        }

        $relatedPosts = array();

        if(Module::isInstalled('ph_relatedposts'))
        {
            $relatedPosts = array(
                'related_posts' => array(
                    'submit' => array('title' => $this->l('Update'), 'class' => 'button'),
                    'title' =>  $this->l('Related Posts widget settings'),
                    'image' =>   '../img/t/AdminOrderPreferences.gif',
                    'fields' => array(

                        'PH_RELATEDPOSTS_GRID_COLUMNS' => array(
                            'title' => $this->l('Grid columns:'),
                            'cast' => 'intval',
                            'desc' => $this->l('Working only with "Recent Posts layout:" setup to "Grid"'),
                            'show' => true,
                            'required' => true,
                            'type' => 'radio',
                            'choices' => array(
                                '2' => $this->l('2 columns'),
                                '3' => $this->l('3 columns'),
                                '4' => $this->l('4 columns'),
                            )
                        ), // PH_RELATEDPOSTS_GRID_COLUMNS

                    ),
                ),
            );
        }

        $alert_class = ($this->is_16 === true) ? 'alert alert-info' : 'info';

        $standard_options = array(
            'general' => array(
                'title' =>  $this->l('PrestaHome SimpleBlog Settings'),
                'image' =>   '../img/t/AdminOrderPreferences.gif',
                'info' => '<a class="button btn btn-default" href="index.php?controller=AdminSimpleBlogSettings&token='.Tools::getValue('token').'&regenerateThumbnails=1" class="bold"><i class="process-icon-cogs"></i>'.$this->l('Regenerate thumbnails').'</a><br /><br />',
                'fields' => array(

                    'PH_BLOG_POSTS_PER_PAGE' => array(
                        'title' => $this->l('Posts per page:'),
                        'cast' => 'intval',
                        'desc' => $this->l('Number of blog posts displayed per page. Default is 10.'),
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'isUnsignedId',
                    ), // PH_BLOG_POSTS_PER_PAGE
                    
                    'PH_BLOG_SLUG' => array(
                        'title' => $this->l('Blog main URL (by default: blog)'),
                        'validation' => 'isGenericName',
                        'required' => true,
                        'type' => 'text',
                        'size' => 40
                    ), // PH_BLOG_SLUG

                    'PH_BLOG_MAIN_TITLE' => array(
                        'title' => $this->l('Blog title:'),
                        'validation' => 'isGenericName',
                        'type' => 'textLang',
                        'size' => 40
                    ), // PH_BLOG_MAIN_TITLE

                    'PH_BLOG_DATEFORMAT' => array(
                        'title' => $this->l('Blog default date format:'),
                        'desc' => $this->l('More details: http://php.net/manual/pl/function.date.php'),
                        'validation' => 'isGenericName',
                        'type' => 'text',
                        'size' => 40
                    ), // PH_BLOG_DATEFORMAT

                    'PH_CATEGORY_SORTBY' => array(
                        'title' => $this->l('Sort categories by:'),
                        'desc' => $this->l('Select which method use to sort categories in SimpleBlog Categories Block'),
                        'show' => true,
                        'required' => true,
                        'type' => 'radio',
                        'choices' => array(
                            'position' => $this->l('Position (1-9)'),
                            'name' => $this->l('Name (A-Z)'),
                            'id' => $this->l('ID (1-9)'),
                        )
                    ), // PH_CATEGORY_SORTBY

                    'PH_BLOG_FB_INIT' => array(
                        'title' => $this->l('Init Facebook?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'desc' => $this->l('If you already use some Facebook widgets in your theme please select option to "No". If you select "Yes" then SimpleBlog will add facebook connect script on single post page.'),
                        'required' => true,
                        'type' => 'bool'
                    ), // PH_BLOG_FB_INIT


                    // @todo - 2.0.0
                    // 'PH_BLOG_LOAD_FA' => array(
                    //     'title' => $this->l('Load FontAwesome?'),
                    //     'validation' => 'isBool',
                    //     'cast' => 'intval',
                    //     'desc' => $this->l('If you already use FontAwesome in your theme please select option to "No".'),
                    //     'required' => true,
                    //     'type' => 'bool'
                    // ), // PH_BLOG_LOAD_FA



                ),
                'submit' => array('title' => $this->l('Update'), 'class' => 'button'),
            ),

            'layout' => array(
                'submit' => array('title' => $this->l('Update'), 'class' => 'button'),
                'title' =>  $this->l('Appearance Settings - General'),
                'image' =>   '../img/t/AdminOrderPreferences.gif',
                'fields' => array(

                    // @todo - 2.0.0
                    // 'PH_BLOG_COLUMNS' => array(
                    //     'title' => $this->l('Grid mechanism:'),
                    //     'type' => 'select',
                    //     'list' => self::$grid_types,
                    //     'identifier' => 'id',
                    //     'required' => true,
                    //     'validation' => 'isGenericName',
                    // ), // PH_BLOG_COLUMNS

                    'PH_BLOG_LAYOUT' => array(
                        'title' => $this->l('Main layout:'),
                        'show' => true,
                        'required' => true,
                        'type' => 'radio',
                        'choices' => array(
                            'default' => $this->l('3 columns, enabled left column and right column'),
                            'left_sidebar' => $this->l('2 columns, enabled left column'),
                            'right_sidebar' => $this->l('2 columns, enabled right column'),
                            'full_width' => $this->l('Full width - Left and right column will be removed'),
                        )
                    ), // PH_BLOG_LAYOUT

                    'PH_BLOG_DISPLAY_BREADCRUMBS' => array(
                        'title' => $this->l('Display breadcrumbs in center-column?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'desc' => $this->l('Sometimes you want to remove breadcrumbs from center-column'),
                        'required' => true,
                        'type' => 'bool'
                    ), // PH_BLOG_DISPLAY_BREADCRUMBS

                    'PH_BLOG_LIST_LAYOUT' => array(
                        'title' => $this->l('Posts list layout:'),
                        'show' => true,
                        'required' => true,
                        'type' => 'radio',
                        'choices' => array(
                            'full' => $this->l('Full width with large images'),
                            'grid' => $this->l('Grid'),
                        )
                    ), // PH_BLOG_LIST_LAYOUT

                    'PH_BLOG_GRID_COLUMNS' => array(
                        'title' => $this->l('Grid columns:'),
                        'cast' => 'intval',
                        'desc' => $this->l('Working only with "Posts list layout" setup to "Grid"'),
                        'show' => true,
                        'required' => true,
                        'type' => 'radio',
                        'choices' => array(
                            '2' => $this->l('2 columns'),
                            '3' => $this->l('3 columns'),
                            '4' => $this->l('4 columns'),
                        )
                    ), // PH_BLOG_GRID_COLUMNS

                    'PH_BLOG_CSS' => array(
                        'title' => $this->l('Custom CSS'),
                        'show' => true,
                        'required' => false,
                        'type' => 'textarea',
                        'cols' => '70',
                        'rows' => '10'
                    ), // PH_BLOG_CSS

                ),
            ),

            'single_post' => array(
                'submit' => array('title' => $this->l('Update'), 'class' => 'button'),
                'title' =>  $this->l('Appearance Settings - Single post'),
                'image' =>   '../img/t/AdminOrderPreferences.gif',
                'fields' => array(

                    'PH_BLOG_DISPLAY_LIKES' => array(
                        'title' => $this->l('Display "likes"?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool'
                    ), // PH_BLOG_DISPLAY_LIKES

                    'PH_BLOG_FB_COMMENTS' => array(
                        'title' => $this->l('Use FB comments on single post page?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool'
                    ), // PH_BLOG_FB_COMMENTS

                    'PH_BLOG_DISPLAY_SHARER' => array(
                        'title' => $this->l('Use share icons on single post page?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool'
                    ), // PH_BLOG_DISPLAY_SHARER

                    'PH_BLOG_DISPLAY_AUTHOR' => array(
                        'title' => $this->l('Display post author?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool',
                        'desc' => $this->l('This option also applies to the list of posts from the category'),
                    ), // PH_BLOG_DISPLAY_AUTHOR

                    'PH_BLOG_DISPLAY_DATE' => array(
                        'title' => $this->l('Display post creation date?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool',
                        'desc' => $this->l('This option also applies to the list of posts from the category'),
                    ), // PH_BLOG_DISPLAY_DATE

                    'PH_BLOG_DISPLAY_FEATURED' => array(
                        'title' => $this->l('Display post featured image?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool'
                    ), // PH_BLOG_DISPLAY_FEATURED

                    'PH_BLOG_DISPLAY_CATEGORY' => array(
                        'title' => $this->l('Display post category?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool',
                        'desc' => $this->l('This option also applies to the list of posts from the category'),
                    ), // PH_BLOG_DISPLAY_CATEGORY

                    'PH_BLOG_DISPLAY_TAGS' => array(
                        'title' => $this->l('Display post tags?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool',
                        'desc' => $this->l('This option also applies to the list of posts from the category'),
                    ), // PH_BLOG_DISPLAY_TAGS

                ),
            ),

            'category_page' => array(
                'submit' => array('title' => $this->l('Update'), 'class' => 'button'),
                'title' =>  $this->l('Appearance Settings - Post lists'),
                'image' =>   '../img/t/AdminOrderPreferences.gif',
                'fields' => array(

                    'PH_BLOG_DISPLAY_THUMBNAIL' => array(
                        'title' => $this->l('Display post thumbnails?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool'
                    ), // PH_BLOG_DISPLAY_THUMBNAILS

                    'PH_BLOG_DISPLAY_DESCRIPTION' => array(
                        'title' => $this->l('Display post short description?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool'
                    ), // PH_BLOG_DISPLAY_DESCRIPTION

                    'PH_BLOG_DISPLAY_CAT_DESC' => array(
                        'title' => $this->l('Display category description on category page?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool'
                    ), // PH_BLOG_DISPLAY_CAT_DESC

                    'PH_BLOG_DISPLAY_CATEGORY_IMAGE' => array(
                        'title' => $this->l('Display category image?'),
                        'validation' => 'isBool',
                        'cast' => 'intval',
                        'required' => true,
                        'type' => 'bool'
                    ), // PH_BLOG_DISPLAY_CATEGORY_IMAGE

                    'PH_CATEGORY_IMAGE_X' => array(
                        'title' => $this->l('Default category image width (px)'),
                        'cast' => 'intval',
                        'desc' => $this->l('Default: 535 (For PrestaShop 1.5), 870 (For PrestaShop 1.6)'),
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'isUnsignedId',
                    ), // PH_CATEGORY_IMAGE_X

                    'PH_CATEGORY_IMAGE_Y' => array(
                        'title' => $this->l('Default category image height (px)'),
                        'cast' => 'intval',
                        'desc' => $this->l('Default: 150'),
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'isUnsignedId',
                    ), // PH_CATEGORY_IMAGE_Y

                ),
            ),

            'thumbnails' => array(
                'submit' => array('title' => $this->l('Update'), 'class' => 'button'),
                'title' =>  $this->l('Thumbnails Settings'),
                'image' =>   '../img/t/AdminOrderPreferences.gif',
                'info' => '<div class="'.$alert_class.'">'.$this->l('Remember to regenerate thumbnails after doing changes here').'</div>',
                'fields' => array(

                    'PH_BLOG_THUMB_METHOD' => array(
                        'title' => $this->l('Resize method:'),
                        'cast' => 'intval',
                        'desc' => $this->l('Select wich method use to resize thumbnail. Adaptive resize: What it does is resize the image to get as close as possible to the desired dimensions, then crops the image down to the proper size from the center.'),
                        'show' => true,
                        'required' => true,
                        'type' => 'radio',
                        'choices' => array(
                            '1' => $this->l('Adaptive resize (recommended)'),
                            '2' => $this->l('Crop from center'),
                        )
                    ), // PH_BLOG_THUMB_METHOD

                    'PH_BLOG_THUMB_X' => array(
                        'title' => $this->l('Default thumbnail width (px)'),
                        'cast' => 'intval',
                        'desc' => $this->l('Default: 255 (For PrestaShop 1.5), 420 (For PrestaShop 1.6)'),
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'isUnsignedId',
                    ), // PH_BLOG_THUMB_X

                    'PH_BLOG_THUMB_Y' => array(
                        'title' => $this->l('Default thumbnail height (px)'),
                        'cast' => 'intval',
                        'desc' => $this->l('Default: 200 (For PrestaShop 1.5 and 1.6)'),
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'isUnsignedId',
                    ), // PH_BLOG_THUMB_Y

                    'PH_BLOG_THUMB_X_WIDE' => array(
                        'title' => $this->l('Default thumbnail width (wide version) (px)'),
                        'cast' => 'intval',
                        'desc' => $this->l('Default: 535 (For PrestaShop 1.5), 870 (For PrestaShop 1.6)'),
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'isUnsignedId',
                    ), // PH_BLOG_THUMB_X_WIDE

                    'PH_BLOG_THUMB_Y_WIDE' => array(
                        'title' => $this->l('Default thumbnail height (wide version) (px)'),
                        'cast' => 'intval',
                        'desc' => $this->l('Default: 350 (For PrestaShop 1.5 and 1.6)'),
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'isUnsignedId',
                    ), // PH_BLOG_THUMB_Y_WIDE

                ),
            ),
        );

        $widgets_options = array();
        $widgets_options = array_merge($recentPosts, $relatedPosts);

        //$this->hide_multishop_checkbox = true;
        $this->fields_options = array_merge($standard_options, $widgets_options);

        return parent::renderOptions();
    }

    public function beforeUpdateOptions()
    {
        $customCSS = '/** custom css for SimpleBlog **/'.PHP_EOL;
        $customCSS .= Tools::getValue('PH_BLOG_CSS', false);

        if($customCSS)
        {
            $handle = _PS_MODULE_DIR_ . 'ph_simpleblog/css/custom.css';

            if(!file_put_contents($handle, $customCSS))
            {
                die(Tools::displayError('Problem with saving custom CSS, contact with module author'));
            }
        }
    }

    public function initContent()
    {
        $this->multiple_fieldsets = true;

        if(Tools::isSubmit('regenerateThumbnails'))
        {
            SimpleBlogPost::regenerateThumbnails();
            Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getValue('token').'&conf=9');
        }

        $this->context->smarty->assign(array(
            'content' => $this->content,
            'url_post' => self::$currentIndex.'&token='.$this->token,
        ));

        parent::initContent();
    }
}
