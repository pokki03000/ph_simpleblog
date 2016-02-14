<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_6($object)
{
    Configuration::updateValue('PH_BLOG_SLUG', 'blog');

	return true;
}