<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_1($object)
{
	Configuration::updateValue('PH_RECENTPOSTS_NB', '4');
    Configuration::updateValue('PH_RECENTPOSTS_CAT', '0');
    Configuration::updateValue('PH_RECENTPOSTS_POSITION', 'home');

    Configuration::updateValue('PH_BLOG_DISPLAY_AUTHOR', '1');
    Configuration::updateValue('PH_BLOG_DISPLAY_DATE', '1');
    Configuration::updateValue('PH_BLOG_DISPLAY_THUMBNAIL', '1');
    Configuration::updateValue('PH_BLOG_DISPLAY_CATEGORY', '1');
    Configuration::updateValue('PH_BLOG_DISPLAY_SHARER', '1');
    Configuration::updateValue('PH_BLOG_DISPLAY_TAGS', '1');
    Configuration::updateValue('PH_BLOG_DISPLAY_DESCRIPTION', '1');

	return true;
}