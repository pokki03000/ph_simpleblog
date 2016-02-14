<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_9_3($object)
{
	if(file_exists(_PS_MODULE_DIR_ . 'ph_simpleblog/controllers/admin/AdminSimpleBlogSettings.php'))
	{
		@unlink(_PS_MODULE_DIR_ . 'ph_simpleblog/controllers/admin/AdminSimpleBlogSettings.php');
	}

	return true;
}