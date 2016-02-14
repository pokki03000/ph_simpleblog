<?php
class SimpleBlogCategory extends ObjectModel
{
    public $id;
    public $id_shop;
    public $id_simpleblog_category;
    public $name;
    public $active = 1;
    public $description;
    public $link_rewrite;
    public $meta_title;
    public $meta_keywords;
    public $meta_description;
    public $date_add;
    public $date_upd;

    /**
    @since 1.2.0.0
    **/
    public $cover;
    public $position;
    public $image;
    public $id_parent;

    protected static $_links = array();

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'simpleblog_category',
        'primary' => 'id_simpleblog_category',
        'multilang' => true,
        'fields' => array(
            'cover'  =>             array('type' => self::TYPE_STRING),
            'position' =>           array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_parent' =>           array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'active' =>             array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'date_add' =>           array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>           array('type' => self::TYPE_DATE, 'validate' => 'isDate'),

            // Lang fields
            'name' =>               array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 64),
            'link_rewrite' =>       array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 64),
            'description' =>        array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'meta_title' =>         array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
            'meta_description' =>   array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'meta_keywords' =>      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
        ),
    );

    public function __construct($id_simpleblog_category = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id_simpleblog_category, $id_lang, $id_shop);

        if(file_exists(_PS_MODULE_DIR_ . 'ph_simpleblog/covers_cat/' .$this->id_simpleblog_category. '.'.$this->cover))
        {
            $this->image = _MODULE_DIR_ . 'ph_simpleblog/covers_cat/' .$this->id_simpleblog_category. '.'.$this->cover;
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

        $this->position = SimpleBlogCategory::getNewLastPosition($this->id_parent);

        foreach ($this->name as $k => $value)
            if (preg_match('/^[1-9]\./', $value))
                $this->name[$k] = '0'.$value;
        $ret = parent::add($autodate, $null_values);
        return $ret;
    }

    public function delete()
    {
        if ((int)$this->id === 0)
            return false;

        return SimpleBlogCategory::deleteCover($this) && parent::delete() && $this->cleanPositions($this->id_parent);
    }

    public  function update($null_values = false)
    {
        foreach ($this->name as $k => $value)
            if (preg_match('/^[1-9]\./', $value))
                $this->name[$k] = '0'.$value;

        return parent::update($null_values);
    }

    public static function getNbCats($id_parent = null)
    {
        return (int)Db::getInstance()->getValue('
            SELECT COUNT(*)
            FROM `'._DB_PREFIX_.'simpleblog_category` sbc
            '.(!is_null($id_parent) ? 'WHERE sbc.`id_parent` = '.(int)$id_parent : '')
        );
    }

    public static function getNewLastPosition($id_parent)
    {
        return (Db::getInstance()->getValue('
            SELECT IFNULL(MAX(position),0)+1
            FROM `'._DB_PREFIX_.'simpleblog_category`
            WHERE `id_parent` = '.(int)$id_parent
        ));
    }

    public function move($direction)
    {
        $nb_cats = SimpleBlogCategory::getNbCats($this->id_parent);
        if ($direction != 'l' && $direction != 'r')
            return false;
        if ($nb_cats <= 1)
            return false;
        if ($direction == 'l' && $this->position <= 1)
            return false;
        if ($direction == 'r' && $this->position >= $nb_cats)
            return false;

        $new_position = ($direction == 'l') ? $this->position - 1 : $this->position + 1;
        Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'simpleblog_category` sbc
            SET position = '.(int)$this->position.'
            WHERE id_parent = '.(int)$this->id_parent.'
                AND position = '.(int)$new_position
        );
        $this->position = $new_position;
        return $this->update();
    }

    public function cleanPositions($id_parent)
    {
        $result = Db::getInstance()->executeS('
            SELECT `id_simpleblog_category`
            FROM `'._DB_PREFIX_.'simpleblog_category`
            WHERE `id_parent` = '.(int)$id_parent.'
            ORDER BY `position`
        ');
        $sizeof = count($result);
        for ($i = 0; $i < $sizeof; ++$i)
            Db::getInstance()->execute('
                UPDATE `'._DB_PREFIX_.'simpleblog_category`
                SET `position` = '.($i + 1).'
                WHERE `id_simpleblog_category` = '.(int)$result[$i]['id_simpleblog_category']
            );
        return true;
    }

    public function updatePosition($way, $position)
    {
        if (!$res = Db::getInstance()->executeS('
            SELECT sbc.`id_simpleblog_category`, sbc.`position`, sbc.`id_parent`
            FROM `'._DB_PREFIX_.'simpleblog_category` sbc
            WHERE sbc.`id_parent` = '.(int)$this->id_parent.'
            ORDER BY sbc.`position` ASC'
        ))
            return false;

        foreach ($res as $cat)
            if ((int)$cat['id_simpleblog_category'] == (int)$this->id)
                $moved_cat = $cat;

        if (!isset($moved_cat) || !isset($position))
            return false;
        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        $result = (Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'simpleblog_category`
            SET `position`= `position` '.($way ? '- 1' : '+ 1').'
            WHERE `position`
            '.($way
                ? '> '.(int)$moved_cat['position'].' AND `position` <= '.(int)$position
                : '< '.(int)$moved_cat['position'].' AND `position` >= '.(int)$position).'
            AND `id_parent`='.(int)$moved_cat['id_parent'])
        && Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'simpleblog_category`
            SET `position` = '.(int)$position.'
            WHERE `id_parent` = '.(int)$moved_cat['id_parent'].'
            AND `id_simpleblog_category`='.(int)$moved_cat['id_simpleblog_category']));
        return $result;
    }

    /**
      * Return available categories
      *
      * @param integer $id_lang Language ID
      * @param boolean $active return only active categories
      * @return array Categories
      */
    public static function getCategories($id_lang, $active = true)
    {
        if (!Validate::isBool($active))
            die(Tools::displayError());
        
        $context = Context::getContext();

        switch(Configuration::get('PH_CATEGORY_SORTBY'))
        {
            case 'c.position':
            $orderby = 'position';
            break;

            case 'name':
            $orderby = 'cl.name';
            break;

            case 'id':
            $orderby = 'c.id_simpleblog_category';
            break;

            default:
            $orderby = 'c.position';
            break;
        }

        $sql = '
        SELECT *
        FROM `'._DB_PREFIX_.'simpleblog_category` c
         '.Shop::addSqlAssociation('simpleblog_category', 'c').'
        LEFT JOIN `'._DB_PREFIX_.'simpleblog_category_lang` cl ON c.`id_simpleblog_category` = cl.`id_simpleblog_category`
        WHERE `id_lang` = '.(int)$id_lang.'
        '.($active ? 'AND `active` = 1' : '').'
        ORDER BY '.$orderby.' ASC';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $categories = array();

        foreach ($result as $row)
        {
            $categories[$row['id_simpleblog_category']]['name'] = $row['name'];
            $categories[$row['id_simpleblog_category']]['url'] = self::getLink($row['link_rewrite'], $id_lang);
            $categories[$row['id_simpleblog_category']]['id'] = $row['id_simpleblog_category'];
        }
            
        return $categories;
    }

    public static function getSimpleCategories($id_lang)
    {
        switch(Configuration::get('PH_CATEGORY_SORTBY'))
        {
            case 'c.position':
            $orderby = 'position';
            break;

            case 'name':
            $orderby = 'cl.name';
            break;

            case 'id':
            $orderby = 'c.id_simpleblog_category';
            break;

            default:
            $orderby = 'c.position';
            break;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT c.`id_simpleblog_category`, cl.`name`
        FROM `'._DB_PREFIX_.'simpleblog_category` c
        LEFT JOIN `'._DB_PREFIX_.'simpleblog_category_lang` cl ON (c.`id_simpleblog_category` = cl.`id_simpleblog_category`)
        WHERE cl.`id_lang` = '.(int)$id_lang.'
        ORDER BY '.$orderby.' ASC');
    }

    public static function getByRewrite($rewrite = null, $id_lang = false)
    {
        if(!$rewrite) return;

        $sql = new DbQuery();
        $sql->select('l.id_simpleblog_category');
        $sql->from('simpleblog_category_lang', 'l');

        if($id_lang)
            $sql->where('l.link_rewrite = \''.$rewrite.'\' AND l.id_lang = '.(int)$id_lang);
        else
            $sql->where('l.link_rewrite = \''.$rewrite.'\'');

        $category = new SimpleBlogCategory(Db::getInstance()->getValue($sql), $id_lang);
        return $category;
    }

    public static function getRewriteByCategory($id_simpleblog_category, $id_lang)
    {
        $sql = new DbQuery();
        $sql->select('l.link_rewrite');
        $sql->from('simpleblog_category_lang', 'l');
        $sql->where('l.id_simpleblog_category = '.$id_simpleblog_category.' AND l.id_lang = '.(int)$id_lang);

        return Db::getInstance()->getValue($sql);
    }

    public static function getLink($rewrite, $id_lang = null, $id_shop = null)
    {
        $url = ph_simpleblog::myRealUrl();

        $dispatcher = Dispatcher::getInstance();
        $params = array();
        $params['sb_category'] = $rewrite;
        return $url.$dispatcher->createUrl('module-ph_simpleblog-category', $id_lang, $params);
    }

    public function getObjectLink($id_lang)
    {
        $url = ph_simpleblog::myRealUrl();

        $dispatcher = Dispatcher::getInstance();
        $params = array();
        $params['sb_category'] = $this->link_rewrite;
        return $url.$dispatcher->createUrl('module-ph_simpleblog-category', $id_lang, $params);
    }

    public static function deleteCover($object)
    {
        $tmp_location = _PS_TMP_IMG_DIR_.'ph_simpleblog_cat_'.$object->id.'.'.$object->cover;
        if(file_exists($tmp_location))
            @unlink($tmp_location);

        $orig_location = _PS_MODULE_DIR_ . 'ph_simpleblog/covers_cat/'.$object->id.'.'.$object->cover;

        if(file_exists($orig_location))
            @unlink($orig_location);

        $object->cover = NULL;
        $object->update();

        return true;
    }

}


