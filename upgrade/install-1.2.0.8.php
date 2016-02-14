<?php
if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_2_0_8($object)
{
	return Configuration::updateGlobalValue('PH_RELATEDPOSTS_GRID_COLUMNS', Configuration::get('PH_BLOG_GRID_COLUMNS'));
}