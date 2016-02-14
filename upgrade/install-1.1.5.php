<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_5($object)
{
	Configuration::updateValue('PH_BLOG_POST_BY_AUTHOR', '1');

	return true;
}