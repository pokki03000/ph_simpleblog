<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_7($object)
{
    Configuration::updateValue('PH_BLOG_LOAD_FA', '0');
    Configuration::updateValue('PH_BLOG_FB_INIT', '1');

	return true;
}