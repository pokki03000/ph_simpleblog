<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_6($object)
{
    $object->registerHook('displaySimpleBlogCategories');

	return true;
}