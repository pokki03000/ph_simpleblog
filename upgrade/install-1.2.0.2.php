<?php
if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_2_0_2($object)
{
	// Position
	Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_category` ADD id_parent int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER position');

	return true;
}