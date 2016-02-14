<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_2($object)
{
	Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_post` ADD author VARCHAR(60) NOT NULL AFTER active');

	$object->registerHook('moduleRoutes');

	return true;
}