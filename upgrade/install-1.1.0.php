<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_0($object)
{
	return (Configuration::updateValue('PH_BLOG_LOAD_FA', '1')
			&& Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_post` ADD is_featured tinyint(1) NOT NULL DEFAULT \'0\''));
}