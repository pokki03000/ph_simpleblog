<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_9($object)
{
	return (Configuration::updateValue('PH_BLOG_THUMB_METHOD', '1')
			&& Configuration::updateValue('PH_BLOG_THUMB_X', '400') 
			&& Configuration::updateValue('PH_BLOG_THUMB_Y', '200') 
			&& Configuration::updateValue('PH_BLOG_THUMB_X_WIDE', '800')
			&& Configuration::updateValue('PH_BLOG_THUMB_Y_WIDE', '250'));
}