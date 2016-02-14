<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_1($object)
{
	return Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_post` ADD date_add DATETIME NOT NULL') && Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_post` ADD date_upd DATETIME NOT NULL');
}