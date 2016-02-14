<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_9($object)
{
	return (Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_post` ADD logged tinyint(1) NOT NULL DEFAULT \'0\' AFTER is_featured'));
}