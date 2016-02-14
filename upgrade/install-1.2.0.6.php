<?php
if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_2_0_6($object)
{
	return Configuration::updateGlobalValue('PH_BLOG_DATEFORMAT', 'd M Y, H:i');
}