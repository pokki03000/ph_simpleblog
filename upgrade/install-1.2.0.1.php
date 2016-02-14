<?php
if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_2_0_1($object)
{
	Configuration::updateGlobalValue('PH_CATEGORY_IMAGE_X', '535');
	Configuration::updateGlobalValue('PH_CATEGORY_IMAGE_Y', '150');

	return true;
}