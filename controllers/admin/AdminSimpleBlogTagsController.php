<?php
require_once _PS_MODULE_DIR_ . 'ph_simpleblog/ph_simpleblog.php';
class AdminSimpleBlogTagsController extends ModuleAdminController
{
	public function __construct()
	{
		$this->table = 'simpleblog_tag';
		$this->className = 'SimpleBlogTag';

		$this->bootstrap = true;

		$this->fields_list = array(
			'id_simpleblog_tag' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25,
			),
			'lang' => array(
				'title' => $this->l('Language'),
				'filter_key' => 'l!name',
				'width' => 100,
			),
			'name' => array(
				'title' => $this->l('Name'),
				'width' => 'auto',
				'filter_key' => 'a!name'
			),
			'posts' => array(
				'title' => $this->l('Posts:'),
				'align' => 'center',
				'width' => 50,
				'havingFilter' => true
			)
		);

	 	$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));

		parent::__construct();
	}

	public function renderList()
	{
		$this->addRowAction('edit');
	 	$this->addRowAction('delete');

		$this->_select = 'l.name as lang, COUNT(pt.id_simpleblog_post) as posts';
		$this->_join = '
			LEFT JOIN `'._DB_PREFIX_.'simpleblog_post_tag` pt
				ON (a.`id_simpleblog_tag` = pt.`id_simpleblog_tag`)
			LEFT JOIN `'._DB_PREFIX_.'lang` l
				ON (l.`id_lang` = a.`id_lang`)';
		$this->_group = 'GROUP BY a.name, a.id_lang';

		return parent::renderList();
	}

	public function postProcess()
	{
		if ($this->tabAccess['edit'] === '1' && Tools::getValue('submitAdd'.$this->table))
		{
			if (($id = (int)Tools::getValue($this->identifier)) && ($obj = new $this->className($id)) && Validate::isLoadedObject($obj))
			{
				$previousPosts = $obj->getPosts();
				$removedPosts = array();

				foreach ($previousPosts as $post)
					if (!in_array($post['id_simpleblog_post'], $_POST['posts']))
						$removedPosts[] = $post['id_simpleblog_post'];

				$obj->setPosts($_POST['posts']);
			}
		}

		return parent::postProcess();
	}

	public function renderForm()
	{
		if (!($obj = $this->loadObject(true)))
			return;

		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Tag')
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Name:'),
					'name' => 'name',
					'required' => true
				),
				array(
					'type' => 'select',
					'label' => $this->l('Language:'),
					'name' => 'id_lang',
					'required' => true,
					'options' => array(
						'query' => Language::getLanguages(false),
						'id' => 'id_lang',
						'name' => 'name'
					)
				),
			),
			'selects' => array(
				'posts' => $obj->getPosts(true),
				'posts_unselected' => $obj->getPosts(false)
			),
			'submit' => array(
				'title' => $this->l('Save   '),
				'class' => 'button'
			)
		);

		return parent::renderForm();
	}
}


