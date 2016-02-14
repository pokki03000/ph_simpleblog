<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_3($object)
{
	$sql = array();
    
	$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'simpleblog_tag` (
            `id_simpleblog_tag` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_lang` INT( 11 ) unsigned NOT NULL,
            `name` VARCHAR(60) NOT NULL,
            PRIMARY KEY (`id_simpleblog_tag`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'simpleblog_post_tag` (
            `id_simpleblog_post` INT( 11 ) unsigned NOT NULL,
            `id_simpleblog_tag` INT( 11 ) unsigned NOT NULL,
            PRIMARY KEY (`id_simpleblog_tag`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

    foreach ($sql as $s) {
        if (!Db::getInstance()->Execute($s)) {
            return false;
        }
    }

    $context = Context::getContext();

    $tab = new Tab();       
    $tab->name[$context->language->id] = $object->l('Tags');
    $tab->class_name = 'AdminSimpleBlogTags';
    $tab->id_parent = Tab::getIdFromClassName('AdminSimpleBlog');
    $tab->module = $object->name;
    $tab->add();

	return true;
}