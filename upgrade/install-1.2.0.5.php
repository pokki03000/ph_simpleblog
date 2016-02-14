<?php
if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_2_0_5($object)
{
	return $object->registerHook('displayBackOfficeHeader') && $object->registerHook('displayAdminHomeQuickLinks');
}