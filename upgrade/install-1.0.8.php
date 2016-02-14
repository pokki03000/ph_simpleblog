<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_8($object)
{
	return Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_post` ADD cover TEXT AFTER author');
}