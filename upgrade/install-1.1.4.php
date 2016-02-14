<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_4($object)
{
	Configuration::updateValue('PH_BLOG_DISPLAY_CAT_DESC', '1');

	return true;
}