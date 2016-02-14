<?php
if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_2_0_0($object)
{
	// Cover
	Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_category` ADD cover VARCHAR(5) NOT NULL AFTER id_simpleblog_category');

	// Position
	Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_category` ADD position int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER cover');

	// Likes
	Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_post` ADD likes INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER author');

	// Views
	Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'simpleblog_post` ADD views INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER likes');

	Configuration::updateValue('PH_BLOG_DISPLAY_CATEGORY_IMAGE', '1');
    Configuration::updateValue('PH_BLOG_DISPLAY_LIKES', '1');
    Configuration::updateValue('PH_BLOG_DISPLAY_VIEWS', '1');

	return true;
}