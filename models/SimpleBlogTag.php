<?php
class SimpleBlogTag extends ObjectModel
{
 	/** @var integer Language id */
	public $id_lang;

 	/** @var string Name */
	public $name;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'simpleblog_tag',
		'primary' => 'id_simpleblog_tag',
		'fields' => array(
			'id_lang' => 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'name' => 		array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
		),
	);

	public function __construct($id = null, $name = null, $id_lang = null)
	{
		$this->def = Tag::getDefinition($this);
		$this->setDefinitionRetrocompatibility();

		if ($id)
			parent::__construct($id);
		else if ($name && Validate::isGenericName($name) && $id_lang && Validate::isUnsignedId($id_lang))
		{
			$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'simpleblog_tag` t
			WHERE `name` LIKE \''.pSQL($name).'\' AND `id_lang` = '.(int)$id_lang);

			if ($row)
			{
			 	$this->id = (int)$row['id_simpleblog_tag'];
			 	$this->id_lang = (int)$row['id_lang'];
				$this->name = $row['name'];
			}
		}
	}

	public function add($autodate = true, $null_values = false)
	{
		if (!parent::add($autodate, $null_values))
			return false;
		else if (isset($_POST['posts']))
			return $this->setPosts(Tools::getValue('posts'));
		return true;
	}

	/**
	* Add several tags in database and link it to a product
	*
	* @param integer $id_lang Language id
	* @param integer $id_simpleblog_post Post id to link tags with
	* @param string|array $tag_list List of tags, as array or as a string with comas
	* @return boolean Operation success
	*/
	public static function addTags($id_lang, $id_simpleblog_post, $tag_list, $separator = ',')
	{

	 	if (!Validate::isUnsignedId($id_lang))
			return false;

		if (!is_array($tag_list))
	 		$tag_list = array_filter(array_unique(array_map('trim', preg_split('#\\'.$separator.'#', $tag_list, null, PREG_SPLIT_NO_EMPTY))));
		
	 	$list = array();
		if (is_array($tag_list))
			foreach ($tag_list as $tag)
			{
		 	 	if (!Validate::isGenericName($tag))
		 	 		return false;

				$tag_obj = new SimpleBlogTag(null, $tag, (int)$id_lang);
	
				/* Tag does not exist in database */
				if (!Validate::isLoadedObject($tag_obj))
				{
					$tag_obj->name = $tag;
					$tag_obj->id_lang = (int)$id_lang;
					$tag_obj->add();
				}
				if (!in_array($tag_obj->id, $list))
					$list[] = $tag_obj->id;
			}
		$data = '';
		foreach ($list as $tag)
			$data .= '('.(int)$tag.','.(int)$id_simpleblog_post.'),';
		$data = rtrim($data, ',');

		$sql = 'INSERT INTO `'._DB_PREFIX_.'simpleblog_post_tag` (`id_simpleblog_tag`, `id_simpleblog_post`) VALUES '.$data;
		
		return Db::getInstance()->execute($sql);
	}

	public static function getMainTags($id_lang, $nb = 10)
	{
		$groups = FrontController::getCurrentCustomerGroups();
		$sql_groups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT t.name, COUNT(pt.id_tag) AS times
		FROM `'._DB_PREFIX_.'simpleblog_post_tag` pt
		LEFT JOIN `'._DB_PREFIX_.'tag` t ON (t.id_tag = pt.id_tag)
		LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.id_simpleblog_post = pt.id_simpleblog_post)
		'.Shop::addSqlAssociation('product', 'p').'
		WHERE t.`id_lang` = '.(int)$id_lang.'
		AND product_shop.`active` = 1
		AND product_shop.`id_simpleblog_post` IN (
			SELECT cp.`id_simpleblog_post`
			FROM `'._DB_PREFIX_.'category_group` cg
			LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
			WHERE cg.`id_group` '.$sql_groups.'
		)
		GROUP BY t.id_tag
		ORDER BY times DESC
		LIMIT 0, '.(int)$nb);
	}

	public static function getPostTags($id_simpleblog_post)
	{
	 	if (!$tmp = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT t.`id_lang`, t.`name`
		FROM '._DB_PREFIX_.'simpleblog_tag t
		LEFT JOIN '._DB_PREFIX_.'simpleblog_post_tag pt ON (pt.id_simpleblog_tag = t.id_simpleblog_tag)
		WHERE pt.`id_simpleblog_post`='.(int)$id_simpleblog_post))
	 		return false;
	 	$result = array();
	 	foreach ($tmp as $tag)
	 		$result[$tag['id_lang']][] = $tag['name'];
	 	return $result;
	}

	public function getPosts($associated = true, Context $context = null)
	{
		if (!$context)
			$context = Context::getContext();
		$id_lang = $this->id_lang ? $this->id_lang : $context->language->id;

		if (!$this->id && $associated)
			return array();

		$in = $associated ? 'IN' : 'NOT IN';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT pl.meta_title, pl.id_simpleblog_post
		FROM `'._DB_PREFIX_.'simpleblog_post` p
		LEFT JOIN `'._DB_PREFIX_.'simpleblog_post_lang` pl ON p.id_simpleblog_post = pl.id_simpleblog_post
		WHERE pl.id_lang = '.(int)$id_lang.'
		'.($this->id ? ('AND p.id_simpleblog_post '.$in.' (SELECT pt.id_simpleblog_post FROM `'._DB_PREFIX_.'simpleblog_post_tag` pt WHERE pt.id_simpleblog_tag = '.(int)$this->id.')') : '').'
		ORDER BY pl.meta_title');
	}

	public function setPosts($array)
	{
		$result = Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'simpleblog_post_tag WHERE id_simpleblog_tag = '.(int)$this->id);
		if (is_array($array))
		{
			$array = array_map('intval', $array);
			$ids = array();
			foreach ($array as $id_simpleblog_post)
				$ids[] = '('.(int)$id_simpleblog_post.','.(int)$this->id.')';

			if ($result)
			{
				$result &= Db::getInstance()->execute('INSERT INTO '._DB_PREFIX_.'simpleblog_post_tag (id_simpleblog_post, id_simpleblog_tag) VALUES '.implode(',', $ids));
			}
		}
		return $result;
	}

	public static function deleteTagsForPost($id_simpleblog_post)
	{
		return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'simpleblog_post_tag` WHERE `id_simpleblog_post` = '.(int)$id_simpleblog_post);
	}

	public static function getLink($tag, $id_lang = null, $id_shop = null)
    {
    	$url = ph_simpleblog::myRealUrl();
    	
        $dispatcher = Dispatcher::getInstance();
        $params = array();
        $params['tag'] = $tag;
        return $url.$dispatcher->createUrl('ph_simpleblog_tag', $id_lang, $params);
    }
}


