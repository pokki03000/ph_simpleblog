<?php
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/ph_simpleblog.php';

class AdminSimpleBlogPostsController extends ModuleAdminController
{

    public $is_16;

    public function __construct()
    {

        $this->table = 'simpleblog_post';
        $this->className = 'SimpleBlogPost';
        $this->lang = true;

        $this->bootstrap = true;

        $this->addRowAction('edit');
        $this->addRowAction('view');
        $this->addRowAction('delete');

        $this->is_16 = (bool)(version_compare(_PS_VERSION_, '1.6.0', '>=') === true);
        
        $this->bulk_actions = array(
                'delete' => array(
                    'text' => $this->l('Delete selected'), 
                    'confirm' => $this->l('Delete selected items?'
                    )
                ),
                'enableSelection' => array('text' => $this->l('Enable selection')),
                'disableSelection' => array('text' => $this->l('Disable selection'))
            );

        $this->_select = 'sbcl.name AS `category`';

        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'simpleblog_category_lang` sbcl ON (sbcl.`id_simpleblog_category` = a.`id_simpleblog_category` AND sbcl.`id_lang` = '.(int)Context::getContext()->language->id.')';

        $this->fields_list = array(
            'id_simpleblog_post' => array(
                'title' => $this->l('ID'), 
                'align' => 'center', 
                'width' => 30),
            'cover' => array(
                'title' => $this->l('Post thumbnail'), 
                'width' => 150,
                'orderby' => false, 
                'search' => false,
                'callback' => 'getPostThumbnail'
            ),
            'category' => array(
                'title' => $this->l('Category'), 
                'width' => 'auto',
                'filter_key' => 'sbcl!name',
            ),
            'meta_title' => array(
                'title' => $this->l('Name'), 
                'width' => 'auto',
                'filter_key' => 'b!meta_title',
            ),
            'short_content' => array(
                'title' => $this->l('Description'), 
                'width' => 500, 
                'orderby' => false, 
                'callback' => 'getDescriptionClean'
            ),
            'template' => array(
                'recette' => $this->l('valRecette'), 
                'news' => $this->l('valNews')
            ),
            'views' => array(
                'title' => $this->l('Views'), 
                'width' => 30,
                'align' => 'center',
                'search' => false,
            ),
            'likes' => array(
                'title' => $this->l('Likes'), 
                'width' => 30,
                'align' => 'center',
                'search' => false,
            ),
            'is_featured' => array(
                'title' => $this->l('Featured?'), 
                'orderby' => false, 
                'align' => 'center', 
                'type' => 'bool', 
                'active' => 'is_featured'
            ),
            'active' => array(
                'title' => $this->l('Displayed'), 'width' => 25, 'active' => 'status',
                'align' => 'center','type' => 'bool', 'orderby' => false
        ));


        parent::__construct();

    }

    public function init()
    {
        parent::init();

        Shop::addTableAssociation($this->table, array('type' => 'shop'));

        if (Shop::getContext() == Shop::CONTEXT_SHOP)
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'simpleblog_post_shop` sa ON (a.`id_simpleblog_post` = sa.`id_simpleblog_post` AND sa.id_shop = '.(int)$this->context->shop->id.') ';
        // else
        //     $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'simpleblog_post_shop` sa ON (a.`simpleblog_post` = sa.`simpleblog_post` AND sa.id_shop = a.id_shop_default) ';

        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive())
            $this->_where = ' AND sa.`id_shop` = '.(int)Context::getContext()->shop->id;

        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP)
            unset($this->fields_list['position']);
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addJqueryUi('ui.widget');
        $this->addJqueryPlugin('tagify');
    }

    public static function getDescriptionClean($description)
    {
        return substr(strip_tags(stripslashes($description)), 0, 80).'...';
    }

    public static function getPostThumbnail($cover, $row)
    {
        return ImageManager::thumbnail(_PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$row['id_simpleblog_post'].'.'.$cover, 'ph_simpleblog_'.$row['id_simpleblog_post'].'-list.'.$cover, 75, $cover, true);
    }

    public function renderList()
    {
        $this->initToolbar();
        return parent::renderList();
    }

    public function initFormToolBar()
    {
        unset($this->toolbar_btn['back']);    
        $this->toolbar_btn['save-and-stay'] = array(
                        'short' => 'SaveAndStay',
                        'href' => '#',
                        'desc' => $this->l('Save and stay'),
                    );
        $this->toolbar_btn['back'] = array(
                        'href' => self::$currentIndex.'&token='.Tools::getValue('token'),
                        'desc' => $this->l('Back to list'),
                    );
    }

    public function renderForm()
    {

        $this->initFormToolbar();
        if (!$this->loadObject(true))
            return;

        $obj = $this->loadObject(true);

        $cover = false;
        $featured = false;

        if(isset($obj->id))
        {
            $this->display = 'edit';

            $cover = ImageManager::thumbnail(_PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$obj->id.'.'.$obj->cover, 'ph_simpleblog_'.$obj->id.'.'.$obj->cover, 350, $obj->cover, false);
            $featured = ImageManager::thumbnail(_PS_MODULE_DIR_ . 'ph_simpleblog/featured/'.$obj->id.'.'.$obj->featured, 'ph_simpleblog_featured_'.$obj->id.'.'.$obj->featured, 350, $obj->featured, false);
        }
        else
        {
            $this->display = 'add';
        }
        
        $obj->tags = SimpleBlogTag::getPostTags($obj->id);

        $this->tpl_form_vars['PS_ALLOW_ACCENTED_CHARS_URL'] = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
        $this->tpl_form_vars['languages'] = $this->_languages;
        $this->tpl_form_vars['simpleblogpost'] = $obj;
        $this->tpl_form_vars['is_16'] = $this->is_16;

        $this->fields_value = array(
            'cover' => $cover ? $cover : false,
            'cover_size' => $cover ? filesize(_PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$obj->id.'.'.$obj->cover) / 1000 : false,
            'featured' => $featured ? $featured : false,
            'featured_size' => $featured ? filesize(_PS_MODULE_DIR_ . 'ph_simpleblog/featured/'.$obj->id.'.'.$obj->featured) / 1000 : false
        );

        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('SimpleBlog Post'),
                'image' => '../img/admin/tab-categories.gif'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Title:'),
                    'name' => 'meta_title',
                    'required' => true,
                    'lang' => true,
                    'id' => 'name',
                    'class' => 'copyNiceUrl',
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description:'),
                    'name' => 'short_content',
                    'lang' => true,
                    'rows' => 5,
                    'cols' => 40,
                    'autoload_rte' => true,
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Content:'),
                    'name' => 'content',
                    'lang' => true,
                    'rows' => 15,
                    'cols' => 40,
                    'autoload_rte' => true,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Category:'),
                    'name' => 'id_simpleblog_category',
                    'required' => true,
                    'options' => array(
                        'id' => 'id',
                        'query' => SimpleBlogCategory::getCategories($this->context->language->id),
                        'name' => 'name'
                        )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Tags:'),
                    'desc' => $this->l('separate by comma for eg. ipod, apple, something'),
                    'name' => 'tags',
                    'display_tags' => true,
                    'required' => false,
                    'lang' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Author:'),
                    'name' => 'author',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Featured?'),
                    'name' => 'is_featured',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'is_featured_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'is_featured_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Displayed:'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),

                array(
                    'type' => 'radio',
                    'label' => $this->l('For logged customers only?'),
                    'name' => 'logged',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'logged_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'logged_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'class' => 'button',
                'stay' => true,
            ),
        );

        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Post Images'),
                'image' => $this->is_16 ? null : '../img/t/AdminImages.gif',
                'icon'  => 'icon-picture',
            ),
            'input' => array(
               array(
                    'type' => 'file',
                    'label' => $this->l('Post cover:'),
                    'display_image' => true,
                    'name' => 'cover',
                    'desc' => $this->l('Upload a image from your computer.')
                ),
               array(
                    'type' => 'file',
                    'label' => $this->l('Post featured image:'),
                    'display_image' => true,
                    'name' => 'featured',
                    'desc' => $this->l('Upload a image from your computer. Featured image will be displayed only if you want on the single post page.')
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'class' => 'button',
                'stay' => true,
            ),
        );

        $this->fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('SimpleBlog SEO'),
                'image' => '../img/admin/tab-categories.gif'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta description:'),
                    'name' => 'meta_description',
                    'lang' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta keywords:'),
                    'name' => 'meta_keywords',
                    'lang' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Friendly URL:'),
                    'name' => 'link_rewrite',
                    'required' => true,
                    'lang' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save and stay'),
                'class' => 'button',
                'stay' => true,
            ),
        );

        if (Shop::isFeatureActive())
            $this->fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Shop association:')
            ),      
            'input' => array(   
                array(
                    'type' => 'shop',
                    'label' => $this->l('Shop association:'),
                    'name' => 'checkBoxShopAsso',
                ),
            
            )
        );

        $this->multiple_fieldsets = true;

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('viewsimpleblog_post') && ($id_simpleblog_post = (int)Tools::getValue('id_simpleblog_post')) && ($SimpleBlogPost = new SimpleBlogPost($id_simpleblog_post, $this->context->language->id)) && Validate::isLoadedObject($SimpleBlogPost))
        {
            $redir = $SimpleBlogPost->getObjectLink($id_lang);
            Tools::redirectAdmin($redir);
        }

        if(Tools::isSubmit('deleteCover'))
        {
            $this->deleteCover((int)Tools::getValue('id_simpleblog_post'));
        }

        if(Tools::isSubmit('deleteFeatured'))
        {
            $this->deleteFeatured((int)Tools::getValue('id_simpleblog_post'));
        }

        return parent::postProcess();
    }

    public function processAdd()
    {
        $object = parent::processAdd();

        // Cover

        if (isset($_FILES['cover']) && $_FILES['cover']['size'] > 0)
        {
            $object->cover = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $object->update();
        }

        if(!empty($object->cover))
        {
            $this->createCover($_FILES['cover']['tmp_name'], $object);
        }

        // Featured

        if (isset($_FILES['featured']) && $_FILES['featured']['size'] > 0)
        {
            $object->featured = pathinfo($_FILES['featured']['name'], PATHINFO_EXTENSION);
            $object->update();
        }

        if(!empty($object->featured))
        {
            $this->createFeatured($_FILES['featured']['tmp_name'], $object);
        }

        $languages = Language::getLanguages(false);

        $this->updateTags($languages, $object);
        
        $this->updateAssoShop($object->id);
        
        return $object;
    }

    public function processUpdate()
    {

        $object = parent::processUpdate();

        // Cover

        if (isset($_FILES['cover']) && $_FILES['cover']['size'] > 0)
        {
            $object->cover = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $object->update();
        }

        if(!empty($object->cover) && isset($_FILES['cover']) && $_FILES['cover']['size'] > 0)
        {
            $this->createCover($_FILES['cover']['tmp_name'], $object);
        }

        // Featured

        if (isset($_FILES['featured']) && $_FILES['featured']['size'] > 0)
        {
            $object->featured = pathinfo($_FILES['featured']['name'], PATHINFO_EXTENSION);
            $object->update();
        }

        if(!empty($object->featured) && isset($_FILES['featured']) && $_FILES['featured']['size'] > 0)
        {
            $this->createFeatured($_FILES['featured']['tmp_name'], $object);
        }

        $languages = Language::getLanguages(false);

        $this->updateTags($languages, $object);

        $this->updateAssoShop($object->id);

        return $object;
    }

    public function createCover($img = null, $object = null)
    {
        if(!isset($img))
            die('AdminSimpleBlogPostsController@createCover: No image to process');

        $thumbX = Configuration::get('PH_BLOG_THUMB_X');
        $thumbY = Configuration::get('PH_BLOG_THUMB_Y');

        $thumb_wide_X = Configuration::get('PH_BLOG_THUMB_X_WIDE');
        $thumb_wide_Y = Configuration::get('PH_BLOG_THUMB_Y_WIDE');

        $thumbMethod = Configuration::get('PH_BLOG_THUMB_METHOD');

        if(isset($object) && Validate::isLoadedObject($object))
        {
            $fileTmpLoc = $img;
            $origPath = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$object->id.'.'.$object->cover;
            $pathAndName = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$object->id.'-thumb.'.$object->cover;
            $pathAndNameWide = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$object->id.'-wide.'.$object->cover;

            $tmp_location = _PS_TMP_IMG_DIR_.'ph_simpleblog_'.$object->id.'.'.$object->cover;
            if(file_exists($tmp_location))
                @unlink($tmp_location);

            $tmp_location_list = _PS_TMP_IMG_DIR_.'ph_simpleblog_'.$object->id.'-list.'.$object->cover;
            if(file_exists($tmp_location_list))
                @unlink($tmp_location_list);

            try
            {
                $orig = PhpThumbFactory::create($fileTmpLoc);
                $thumb = PhpThumbFactory::create($fileTmpLoc);
                $thumbWide = PhpThumbFactory::create($fileTmpLoc);
            }
            catch (Exception $e)
            {
                echo $e;
            }

            if($thumbMethod == '1')
            {
                $thumb->adaptiveResize($thumbX,$thumbY);
                $thumbWide->adaptiveResize($thumb_wide_X,$thumb_wide_Y);
            }
            elseif($thumbMethod == '2')
            {
                $thumb->cropFromCenter($thumbX,$thumbY);
                $thumbWide->cropFromCenter($thumb_wide_X,$thumb_wide_Y);
            }

            return $orig->save($origPath) && $thumb->save($pathAndName) && $thumbWide->save($pathAndNameWide) && ImageManager::thumbnail(_PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$object->id.'.'.$object->cover, 'ph_simpleblog_'.$object->id.'.'.$object->cover, 350, $object->cover);
        }

    }

    public function createFeatured($img = null, $object = null)
    {
        if(!isset($img))
            die('AdminSimpleBlogPostsController@createFeatured: No image to process');

        if(isset($object) && Validate::isLoadedObject($object))
        {
            $fileTmpLoc = $img;
            $origPath = _PS_MODULE_DIR_ . 'ph_simpleblog/featured/'.$object->id.'.'.$object->featured;

            $this->deleteFeatured($object->id, true);

            try
            {
                $orig = PhpThumbFactory::create($fileTmpLoc);
            }
            catch (Exception $e)
            {
                echo $e;
            }
           
            return $orig->save($origPath) && $tmp_featured_location && ImageManager::thumbnail(_PS_MODULE_DIR_ . 'ph_simpleblog/featured/'.$object->id.'.'.$object->featured, 'ph_simpleblog_featured_'.$object->id.'.'.$object->featured, 350, $object->featured);
        }

    }

    public function deleteFeatured($id, $only_delete = false)
    {
        $object = new SimpleBlogPost($id, Context::getContext()->language->id);

        $tmp_location = _PS_TMP_IMG_DIR_.'ph_simpleblog_featured_'.$object->id.'.'.$object->featured;
        if(file_exists($tmp_location))
            @unlink($tmp_location);

        $orig_location = _PS_MODULE_DIR_ . 'ph_simpleblog/featured/'.$object->id.'.'.$object->featured;

        if(file_exists($orig_location))
            @unlink($orig_location);

        if($only_delete)
            return;

        $object->featured = NULL;
        $object->update();

        Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminSimpleBlogPosts').'&conf=7');
    }

    public function deleteCover($id)
    {
        $object = new SimpleBlogPost($id, Context::getContext()->language->id);

        $tmp_location = _PS_TMP_IMG_DIR_.'ph_simpleblog_'.$object->id.'.'.$object->cover;
        if(file_exists($tmp_location))
            @unlink($tmp_location);

        $orig_location = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$object->id.'.'.$object->cover;
        $thumb = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$object->id.'-thumb.'.$object->cover;
        $thumbWide = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$object->id.'-wide.'.$object->cover;

        if(file_exists($orig_location))
            @unlink($orig_location);

        if(file_exists($thumb))
            @unlink($thumb);

        if(file_exists($thumbWide))
            @unlink($thumbWide);

        $object->cover = NULL;
        $object->update();

        Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminSimpleBlogPosts').'&conf=7');
    }

    public function updateTags($languages, $post)
    {
        $tag_success = true;
        foreach ($languages as $language)
        if ($value = Tools::getValue('tags_'.$language['id_lang']))
            if (!Validate::isTagsList($value))
                $this->errors[] = sprintf(
                    Tools::displayError('The tags list (%s) is invalid.'),
                    $language['name']
                );

        if (!SimpleBlogTag::deleteTagsForPost((int)$post->id))
            $this->errors[] = Tools::displayError('An error occurred while attempting to delete previous tags.');

        foreach ($languages as $language)
            if ($value = Tools::getValue('tags_'.$language['id_lang']))
                $tag_success &= SimpleBlogTag::addTags($language['id_lang'], (int)$post->id, $value);

        if (!$tag_success)
            $this->errors[] = Tools::displayError('An error occurred while adding tags.');
    }

}
