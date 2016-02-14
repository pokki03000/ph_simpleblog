<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_8($object)
{
	return Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_post` ADD featured TEXT NOT NULL AFTER cover');
}