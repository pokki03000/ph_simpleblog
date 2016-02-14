<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_2($object)
{
	Configuration::updateValue('PH_RECENTPOSTS_LAYOUT', 'grid');

	return true;
}