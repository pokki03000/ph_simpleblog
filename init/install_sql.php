<?php
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'simpleblog_post` (
            `id_simpleblog_post` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_simpleblog_category` INT( 11 ) UNSIGNED NOT NULL,
            `active` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
            `author` VARCHAR(60) NOT NULL,
            `likes` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0,
            `views` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0,
            `is_featured` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
            `logged` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
            `cover` TEXT NOT NULL,
            `featured` TEXT NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_simpleblog_post`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'simpleblog_post_lang` (
            `id_simpleblog_post` int(10) UNSIGNED NOT NULL,
            `id_lang` int(10) UNSIGNED NOT NULL,
            `meta_title` varchar(255) NOT NULL,
            `meta_description` varchar(255) NOT NULL,
            `meta_keywords` varchar(255) NOT NULL,
            `short_content` longtext,
            `content` longtext,
            `link_rewrite` varchar(128) NOT NULL,
            PRIMARY KEY (`id_simpleblog_post`,`id_lang`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'simpleblog_post_shop` (
            `id_simpleblog_post` int(11) UNSIGNED NOT NULL,
            `id_shop` int(11) UNSIGNED NOT NULL,
            PRIMARY KEY (`id_simpleblog_post`,`id_shop`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

# categories

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'simpleblog_category` (
            `id_simpleblog_category` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
            `cover` VARCHAR(5) NOT NULL,
            `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
            `id_parent` int(10) UNSIGNED NOT NULL DEFAULT 0,
            `active` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_simpleblog_category`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'simpleblog_category_lang` (
            `id_simpleblog_category` int(10) UNSIGNED NOT NULL,
            `id_lang` int(10) UNSIGNED NOT NULL,
            `name` varchar(128) NOT NULL,
            `description` text,
            `link_rewrite` varchar(128) NOT NULL,
            `meta_title` varchar(128) NOT NULL,
            `meta_keywords` varchar(255) NOT NULL,
            `meta_description` varchar(255) NOT NULL,
            PRIMARY KEY (`id_simpleblog_category`,`id_lang`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'simpleblog_category_shop` (
            `id_simpleblog_category` int(11) UNSIGNED NOT NULL,
            `id_shop` int(11) UNSIGNED NOT NULL,
            PRIMARY KEY (`id_simpleblog_category`,`id_shop`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

# tags

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'simpleblog_tag` (
            `id_simpleblog_tag` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_lang` INT( 11 ) UNSIGNED NOT NULL,
            `name` VARCHAR(60) NOT NULL,
            PRIMARY KEY (`id_simpleblog_tag`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'simpleblog_post_tag` (
        `id_simpleblog_post` INT( 11 ) UNSIGNED NOT NULL,
        `id_simpleblog_tag` INT( 11 ) UNSIGNED NOT NULL,
        PRIMARY KEY (`id_simpleblog_post`, `id_simpleblog_tag`),
        KEY (`id_simpleblog_tag`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';



