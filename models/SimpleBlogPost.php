<?php
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/ph_simpleblog.php';

class SimpleBlogPost extends ObjectModel
{

    public $id;
    public $id_simpleblog_post;
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $short_content;
    public $content;
    public $link_rewrite;
    public $id_simpleblog_category;
    public $active;
    public $date_add;
    public $date_upd;
    public $category;
    public $is_featured;
    public $cover;
    public $featured;
    public $featured_image;
    public $banner;
    public $author;
    public $tags;

    /**
    @since 1.1.9
    **/
    public $logged;

    /**
    @since 1.2.0.0
    **/
    public $likes;
    public $views;

    public static $definition = array(
        'table' => 'simpleblog_post',
        'primary' => 'id_simpleblog_post',
        'multilang' => true,
        'fields' => array(
            'id_simpleblog_category' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'active' =>                 array('type' => self::TYPE_BOOL),
            'is_featured' =>            array('type' => self::TYPE_BOOL),
            'logged' =>                 array('type' => self::TYPE_BOOL),
            'author' =>                 array('type' => self::TYPE_STRING),
            'likes' =>                  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'views' =>                  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'cover'  =>                 array('type' => self::TYPE_STRING),
            'featured'  =>              array('type' => self::TYPE_STRING),
            'date_add' =>               array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>               array('type' => self::TYPE_DATE, 'validate' => 'isDate'),

            // Lang fields
            'meta_title' =>             array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
            'meta_description' =>       array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'meta_keywords' =>          array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'link_rewrite' =>           array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 128),
            'short_content' =>          array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 3999999999999),
            'content' =>                array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 3999999999999),
        ),
    );

    public function __construct($id_simpleblog_post = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id_simpleblog_post, $id_lang, $id_shop);

        $category = new SimpleBlogCategory($this->id_simpleblog_category, $id_lang);
        $this->category = $category->name;
        $this->category_rewrite = $category->link_rewrite;
        $this->url = self::getLink($this->link_rewrite, $this->category_rewrite, $id_lang);
        $this->category_url = SimpleBlogCategory::getLink($category->link_rewrite, $id_lang);

        if(file_exists(_PS_MODULE_DIR_ . 'ph_simpleblog/covers/' .$this->id_simpleblog_post. '.'.$this->cover))
        {
            $this->banner = _MODULE_DIR_ . 'ph_simpleblog/covers/' .$this->id_simpleblog_post. '.'.$this->cover;
            $this->banner_thumb = _MODULE_DIR_ . 'ph_simpleblog/covers/' .$this->id_simpleblog_post. '-thumb.'.$this->cover;
            $this->banner_wide = _MODULE_DIR_ . 'ph_simpleblog/covers/' .$this->id_simpleblog_post. '-wide.'.$this->cover;
        }

        if(file_exists(_PS_MODULE_DIR_ . 'ph_simpleblog/featured/' .$this->id_simpleblog_post. '.'.$this->featured))
        {
            $this->featured_image = _MODULE_DIR_ . 'ph_simpleblog/featured/' .$this->id_simpleblog_post. '.'.$this->featured;
        }

        if ($this->id)
        {
            $tags = SimpleBlogTag::getPostTags((int)$this->id);
            $this->tags = $tags;

            if(isset($tags) && isset($tags[$id_lang]))
                $this->tags_list = $tags[$id_lang];
        }

    }

    public  function add($autodate = true, $null_values = false)
    {
        $ret = parent::add($autodate, $null_values);
        return $ret;
    }

    public function delete()
    {
        if ((int)$this->id === 0)
            return false;

        return SimpleBlogPost::deleteCover($this)
                && SimpleBlogPost::deleteFeatured($this)
                && parent::delete();
    }

    public static function getUrlRewriteInformations($id_simpleblog)
    {
        $sql = 'SELECT l.`id_lang`, c.`link_rewrite`
                FROM `'._DB_PREFIX_.'simpleblog_lang` AS c
                LEFT JOIN  `'._DB_PREFIX_.'lang` AS l ON c.`id_lang` = l.`id_lang`
                WHERE c.`id_simpleblog` = '.(int)$id_simpleblog.'
                AND l.`active` = 1';

        return Db::getInstance()->executeS($sql);
    }

    public static function getSimplePosts($id_lang, $id_shop = null, Context $context = null, $filter = null, $selected = array())
    {
        if (!$context)
            $context = Context::getContext();

        if(!$id_shop)
            $id_shop = $context->shop->id;

        $sql = new DbQuery();
        $sql->select('sbp.id_simpleblog_post, l.meta_title');
        $sql->from('simpleblog_post', 'sbp');
        $sql->innerJoin('simpleblog_post_lang', 'l', 'sbp.id_simpleblog_post = l.id_simpleblog_post AND l.id_lang = '.(int)$id_lang);
        $sql->innerJoin('simpleblog_post_shop', 'sbps', 'sbp.id_simpleblog_post = sbps.id_simpleblog_post AND sbps.id_shop = '.(int)$id_shop); 

        if($filter)
        {
            $sql->where('sbp.id_simpleblog_post '.$filter.' ('.join(',',$selected).')');
        }

        $sql->where('sbp.active = 1');

        if(isset($context->customer) && !$context->customer->isLogged())
            $sql->where('sbp.logged = 0');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }


    public static function findPosts($type = 'author', $keyword = false, $id_lang, $limit = 10, $page = null)
    {
        if($type == 'author')
        {
            return self::getPosts($id_lang, $limit, null, $page, $page, null, null, null, null, false, $keyword);
        }
        elseif($type == 'tag')
        {
            return self::getPosts($id_lang, $limit, null, $page, $page, null, null, null, null, false, false, $keyword);
        }
        else
        {
            return;
        }
    }

    public static function getPosts($id_lang, $limit = 10, $id_simpleblog_category = null, $page = null, $active = true, $orderby = false, $orderway = false, $exclude = null, $featured = false, $author = false, $id_shop = null, $filter = false, $selected = array())
    {
        $context = Context::getContext();

        $start = $limit * ($page == 0 ? 0 : $page-1);

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('simpleblog_post', 'sbp');
        if ($id_lang)
            $sql->innerJoin('simpleblog_post_lang', 'l', 'sbp.id_simpleblog_post = l.id_simpleblog_post AND l.id_lang = '.(int)$id_lang);

        if(!$id_shop)
            $id_shop = $context->shop->id;

        $sql->innerJoin('simpleblog_post_shop', 'sbps', 'sbp.id_simpleblog_post = sbps.id_simpleblog_post AND sbps.id_shop = '.(int)$id_shop); 

        if ($active)
            $sql->where('sbp.active = 1');

        if (isset($id_simpleblog_category) && (int)$id_simpleblog_category > 0)
            $sql->where('sbp.id_simpleblog_category = '.(int)$id_simpleblog_category);

        if($exclude)
        {
            $sql->where('sbp.id_simpleblog_post != '.(int)$exclude);
        }

        if($featured)
        {
            $sql->where('sbp.is_featured = 1');
        }

        if($author && Configuration::get('PH_BLOG_POST_BY_AUTHOR'))
        {
            $sql->where('sbp.author = \''.pSQL($author).'\'');
        }

        if($filter)
        {
            $sql->where('sbp.id_simpleblog_post '.$filter.' ('.join(',',$selected).')');
        }

        if(isset($context->customer) && !$context->customer->isLogged())
        {
            $sql->where('sbp.logged = 0');
        }
            

        if(!$orderby)
            $orderby = 'sbp.id_simpleblog_post';

        if(!$orderway)
            $orderway = 'DESC';

        $sql->orderBy($orderby.' '.$orderway);

        $sql->limit($limit, $start);

        $result = Db::getInstance()->executeS($sql);

        if(sizeof($result))
        {
            foreach($result as &$row)
            {
                $category_rewrite = SimpleBlogCategory::getRewriteByCategory($row['id_simpleblog_category'], $id_lang);

                $category_obj = new SimpleBlogCategory($row['id_simpleblog_category'], $id_lang);

                $category_url = SimpleBlogCategory::getLink($category_obj->link_rewrite, $id_lang);

                if(file_exists(_PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$row['id_simpleblog_post'].'.'.$row['cover']))
                {
                    $row['banner'] = _MODULE_DIR_ . 'ph_simpleblog/covers/'.$row['id_simpleblog_post'].'.'.$row['cover'];
                    $row['banner_thumb'] = _MODULE_DIR_ . 'ph_simpleblog/covers/'.$row['id_simpleblog_post'].'-thumb.'.$row['cover'];
                    $row['banner_wide'] = _MODULE_DIR_ . 'ph_simpleblog/covers/'.$row['id_simpleblog_post'].'-wide.'.$row['cover'];
                }

                if(file_exists(_PS_MODULE_DIR_ . 'ph_simpleblog/featured/'.$row['id_simpleblog_post'].'.'.$row['featured']))
                {
                    $row['featured'] = _MODULE_DIR_ . 'ph_simpleblog/featured/'.$row['id_simpleblog_post'].'.'.$row['featured'];
                }

                $row['url'] = self::getLink($row['link_rewrite'], $category_obj->link_rewrite, $id_lang);
                $row['category'] = $category_obj->name;
                $row['category_url'] = $category_url;

                $tags = SimpleBlogTag::getPostTags($row['id_simpleblog_post']);

                $row['tags'] = isset($tags[$id_lang]) && sizeof($tags[$id_lang] > 0) ? $tags[$id_lang] : false;
            }
        }
        else
        {
            return;
        }

        return $result;
    }


    public static function getLink($rewrite, $category, $id_lang = null, $id_shop = null)
    {
        $url = ph_simpleblog::myRealUrl();

        $dispatcher = Dispatcher::getInstance();
        $params = array();
        $params['rewrite'] = $rewrite;
        $params['sb_category'] = $category;

        return $url.$dispatcher->createUrl('module-ph_simpleblog-single', $id_lang, $params);
    }

    public function getObjectLink($id_lang)
    {
        if(!Validate::isLoadedObject($this))
            return;

        $url = ph_simpleblog::myRealUrl();

        $dispatcher = Dispatcher::getInstance();
        $params = array();
        $params['rewrite'] = $this->link_rewrite;
        $params['sb_category'] = $this->category_rewrite;

        return $url.$dispatcher->createUrl('module-ph_simpleblog-single', $id_lang, $params);
    }

    public static function getSearchLink($type = 'author', $keyword = false, $id_lang = null, $id_shop = null)
    {
        $url = ph_simpleblog::myRealUrl();

        $dispatcher = Dispatcher::getInstance();
        $params = array();
        $params['type'] = $type;
        $params['keyword'] = $keyword;

        return $url.$dispatcher->createUrl('ph_simpleblog_search', $id_lang, $params);
    }

    public static function getByRewrite($rewrite = null, $id_lang)
    {
        if(!$rewrite) return;

        $sql = new DbQuery();
        $sql->select('l.id_simpleblog_post');
        $sql->from('simpleblog_post_lang', 'l');
        
        if($id_lang)
            $sql->where('l.link_rewrite = \''.$rewrite.'\' AND l.id_lang = '.(int)$id_lang);
        else
            $sql->where('l.link_rewrite = \''.$rewrite.'\'');

        $post = new SimpleBlogPost(Db::getInstance()->getValue($sql), $id_lang);
        return $post;
    }

    public function getTags($id_lang)
    {
        if (is_null($this->tags))
            $this->tags = SimpleBlogTag::getPostTags($this->id);

        if (!($this->tags && key_exists($id_lang, $this->tags)))
            return '';

        $result = '';
        foreach ($this->tags[$id_lang] as $tag_name)
            $result .= $tag_name.', ';

        return rtrim($result, ', ');
    }

    public static function getPageLink($page_nb, $type = false, $rewrite = false)
    {
        $url = ph_simpleblog::myRealUrl();
        $id_lang = Context::getContext()->language->id;

        $dispatcher = Dispatcher::getInstance();
        $params = array();
        $params['p'] = $page_nb;

        if($type == 'category')
        {
            if($page_nb == 1 && isset($rewrite))
            {
                return SimpleBlogCategory::getLink($rewrite, $id_lang);
            }

            if(isset($rewrite))
            {
                $params['sb_category'] = $rewrite;
                return $url.$dispatcher->createUrl('module-ph_simpleblog-categorypage', $id_lang, $params);   
            }
        }

        if($page_nb > 1)
            return $url.$dispatcher->createUrl('module-ph_simpleblog-page', $id_lang, $params);
        else
            return ph_simpleblog::getLink();
    }


    public static function getPaginationLink($nb = false, $sort = false, $pagination = true, $array = false)
    {

        $vars = array();
        $vars_nb = array('n', 'search_query');
        $vars_sort = array('orderby', 'orderway');
        $vars_pagination = array('p');
        $url = ph_simpleblog::myRealUrl();

        foreach ($_GET as $k => $value)
        {
            if (Configuration::get('PS_REWRITING_SETTINGS') && ($k == 'isolang' || $k == 'id_lang'))
                continue;
            $if_nb = (!$nb || ($nb && !in_array($k, $vars_nb)));
            $if_sort = (!$sort || ($sort && !in_array($k, $vars_sort)));
            $if_pagination = (!$pagination || ($pagination && !in_array($k, $vars_pagination)));
            if ($if_nb && $if_sort && $if_pagination)
            {
                if (!is_array($value))
                    $vars[urlencode($k)] = $value;
                else
                {
                    foreach (explode('&', http_build_query(array($k => $value), '', '&')) as $key => $val)
                    {
                        $data = explode('=', $val);
                        $vars[urldecode($data[0])] = $data[1];
                    }
                }
            }
        }

        if (!$array)
            if (count($vars))
                return $url.((Configuration::get('PS_REWRITING_SETTINGS') == 1) ? '?' : '&').http_build_query($vars, '', '&');
            else
                return $url;
        
        $vars['requestUrl'] = $url;

        return $vars;
    }

    public static function deleteCover($object)
    {
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

        return true;
    }

    public static function deleteFeatured($object)
    {
        $tmp_location = _PS_TMP_IMG_DIR_.'ph_simpleblog_'.$object->id.'.'.$object->featured;
        if(file_exists($tmp_location))
            @unlink($tmp_location);

        $orig_location = _PS_MODULE_DIR_ . 'ph_simpleblog/featured/'.$object->id.'.'.$object->featured;

        if(file_exists($orig_location))
            @unlink($orig_location);

        $object->featured = NULL;
        $object->update();

        return true;
    }

    public static function regenerateThumbnails()
    {
        $posts = self::getPosts(Context::getContext()->language->id, 9999, null, null, false);

        foreach($posts as $post)
        {
            if(isset($post['cover']) && !empty($post['cover']))
            {
                $tmp_location = _PS_TMP_IMG_DIR_.'ph_simpleblog_'.$post['id_simpleblog_post'].'.'.$post['cover'];
                if(file_exists($tmp_location))
                    @unlink($tmp_location);

                $orig_location = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$post['id_simpleblog_post'].'.'.$post['cover'];
                $thumbLoc = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$post['id_simpleblog_post'].'-thumb.'.$post['cover'];
                $thumbWideLoc = _PS_MODULE_DIR_ . 'ph_simpleblog/covers/'.$post['id_simpleblog_post'].'-wide.'.$post['cover'];

                if(file_exists($thumbLoc))
                    @unlink($thumbLoc);

                if(file_exists($thumbWideLoc))
                    @unlink($thumbWideLoc);

                $thumbX = Configuration::get('PH_BLOG_THUMB_X');
                $thumbY = Configuration::get('PH_BLOG_THUMB_Y');

                $thumb_wide_X = Configuration::get('PH_BLOG_THUMB_X_WIDE');
                $thumb_wide_Y = Configuration::get('PH_BLOG_THUMB_Y_WIDE');

                $thumbMethod = Configuration::get('PH_BLOG_THUMB_METHOD');

                try
                {
                    $thumb = PhpThumbFactory::create($orig_location);
                    $thumbWide = PhpThumbFactory::create($orig_location);
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

                $thumb->save($thumbLoc);
                $thumbWide->save($thumbWideLoc);

                unset($thumb);
                unset($thumbWide);

            }
            
        }

    }

    public static function changeRating($way = 'up', $id_simpleblog_post) 
    {
        if($way == 'up')
        {
            $sql = 'UPDATE `'._DB_PREFIX_.'simpleblog_post` SET `likes` = `likes` + 1 WHERE id_simpleblog_post = '.$id_simpleblog_post;
        }
        elseif($way == 'down')
        {
            $sql = 'UPDATE `'._DB_PREFIX_.'simpleblog_post` SET `likes` = `likes` - 1 WHERE id_simpleblog_post = '.$id_simpleblog_post;
        }
        else
            return;
        
        $result = Db::getInstance()->Execute($sql);

        $sql = 'SELECT `likes` FROM `'._DB_PREFIX_.'simpleblog_post` WHERE id_simpleblog_post = '.$id_simpleblog_post;

        $res = Db::getInstance()->ExecuteS($sql);

        return $res;
    }

    public function increaseViewsNb() 
    {
        
        $sql = 'UPDATE `'._DB_PREFIX_.'simpleblog_post` SET `views` = `views` + 1 WHERE id_simpleblog_post = '.$this->id_simpleblog_post;
       
        $result = Db::getInstance()->Execute($sql);

        return $result;
    }
}