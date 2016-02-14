<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_9_5($object)
{
	Configuration::updateValue('PH_BLOG_DISPLAY_BREADCRUMBS', '1');
	return true;
}