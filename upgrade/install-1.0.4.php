<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_4($object)
{
	$object->registerHook('displaySimpleBlogPosts');

	return true;
}