<?php
$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'simpleblog_post`';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'simpleblog_post_lang`';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'simpleblog_post_shop`';

# categories

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'simpleblog_category`';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'simpleblog_category_lang`';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'simpleblog_category_shop`';

# tags

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'simpleblog_tag`';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'simpleblog_post_tag`';
