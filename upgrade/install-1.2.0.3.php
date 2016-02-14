<?php
if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_2_0_3($object)
{
	Configuration::updateGlobalValue('PH_CATEGORY_SORTBY', 'position');

	return true;
}