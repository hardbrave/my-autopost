<?php

/*
Plugin Name: My-Autopost
Plugin URI: http://www.dianying8.tv
Description: wp-autopost的简化免费版
Version: 1.0
Author: 钱力
Author URI:  http://www.dianying8.tv
*/

define('MAP_PLUGIN_DIR', WP_PLUGIN_DIR . '/my-autopost/');
define('MAP_PLUGIN_URI', plugins_url('', __FILE__));

/* 自定义表名 */
global $table_prefix;
global $t_map_config, $t_map_updated_record, $t_map_config_url_list, $t_map_log, $t_map_autolink;
$t_map_config = $table_prefix . 'autopost_task';
$t_map_updated_record = $table_prefix . 'autopost_record';
$t_map_config_url_list = $table_prefix . 'autopost_task_urllist';
$t_map_log = $table_prefix . 'autopost_log';
$t_map_autolink = $table_prefix . 'autolink';

/* 加载插件语言包 */
load_plugin_textdomain('my-autopost', false, 'my-autopost/languages/');

/* 添加菜单 */
add_action('admin_menu', 'my_autopost_create_menu');
function my_autopost_create_menu()
{
	add_menu_page('MY Auto Post', 'My Auto Post', 'administrator', 'my-autopost/my-autopost-tasklist.php', '', WP_PLUGIN_URL.'/my-autopost/images/menu_icon.png');
	add_submenu_page('my-autopost/my-autopost-tasklist.php', __('Posts'), __('Posts'),  'administrator', 'my-autopost/my-autopost-posts.php');
	add_submenu_page('my-autopost/my-autopost-tasklist.php', __('Auto Link', 'my-autopost'), __('Auto Link', 'my-autopost'),  'administrator', 'my-autopost/my-autopost-autolinks.php');
	add_submenu_page('my-autopost/my-autopost-tasklist.php', __('Options', 'my-autopost'), __('Options', 'my-autopost'),  'administrator', 'my-autopost/my-autopost-options.php');
	add_submenu_page('my-autopost/my-autopost-tasklist.php', __('Proxy', 'my-autopost'), __('Proxy', 'my-autopost'),  'administrator', 'my-autopost/my-autopost-proxy.php');
	add_submenu_page('my-autopost/my-autopost-tasklist.php', __('Logs', 'my-autopost'), __('Logs', 'my-autopost'),  'administrator', 'my-autopost/my-autopost-logs.php');

}

/* 激活插件时,进行初始化工作 */
register_activation_hook( __FILE__, 'my_autopost_initialize');
function my_autopost_initialize() {

	global $wpdb;
	global $t_map_config, $t_map_config_url_list, $t_map_updated_record, $t_map_log, $t_map_autolink;

	// 设置默认属性
	add_option('my_autopost_update_method', '0');
	add_option('my_autopost_run_only_one_task', '1');
	add_option('my_autopost_time_limit', '0');
	add_option('my_autopost_del_comment', '1');
	add_option('my_autopost_del_attr_id', '1');
	add_option('my_autopost_del_attr_class', '1');
	add_option('my_autopost_del_attr_style', '1');
	$types = array('.zip','.rar','.pdf','.doc','.docx','.xls','.ppt');
	$download_types = json_encode($types);
	add_option('my_autopost_download_types', $download_types);
	add_option('my_autopost_down_img_min_width', 100);

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	if(($wpdb->get_var("SHOW TABLES LIKE '$t_map_config'") != $t_map_config)){
		$sql = "CREATE TABLE " . $t_map_config . " (
					id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
					m_extract TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
					activation TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
					name CHAR(200) NOT NULL COLLATE 'utf8_unicode_ci',
					page_charset CHAR(30) NOT NULL DEFAULT 'UTF-8' COLLATE 'utf8_unicode_ci',
					a_match_type TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
					title_match_type TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
					content_match_type VARCHAR(300) NOT NULL DEFAULT '0',
					a_selector VARCHAR(400) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
					title_selector VARCHAR(400) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
					content_selector VARCHAR(400) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
					source_type TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
					start_num SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
					end_num SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
					updated_num MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
					cat VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
					author SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
					update_interval SMALLINT(5) UNSIGNED NOT NULL DEFAULT '60',
					published_interval SMALLINT(5) UNSIGNED NOT NULL DEFAULT '60',
					post_scheduled VARCHAR(20)  NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
					last_update_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
					post_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
					last_error INT(10) UNSIGNED NOT NULL DEFAULT '0',
					is_running TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
					reverse_sort TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
					auto_tags CHAR(10) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
					proxy  CHAR(10) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
					post_type VARCHAR(50) NULL DEFAULT 'post' COLLATE 'utf8_unicode_ci',
					post_format VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
					check_duplicate TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
					err_status TINYINT(3) NOT NULL DEFAULT '1',
					 PRIMARY KEY (id)
					 ) COLLATE='utf8_unicode_ci' ENGINE=InnoDB";

		dbDelta($sql);
	}

	if(($wpdb->get_var("SHOW TABLES LIKE '$t_map_config_url_list'") != $t_map_config_url_list)){
		$sql = "CREATE TABLE " . $t_map_config_url_list . " (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					config_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
					url CHAR(255) NOT NULL,
					PRIMARY KEY (id)
					 ) COLLATE='utf8_unicode_ci' ENGINE=InnoDB";

		dbDelta($sql);
	}

	if(($wpdb->get_var("SHOW TABLES LIKE '$t_map_updated_record'") != $t_map_updated_record)){
		$sql = "CREATE TABLE " . $t_map_updated_record . " (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					config_id SMALLINT(5) UNSIGNED NOT NULL,
					url VARCHAR(1000) NOT NULL COLLATE 'utf8_unicode_ci',
					title VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
					post_id INT(10) UNSIGNED NOT NULL,
					date_time INT(10) UNSIGNED NOT NULL,
					url_status TINYINT(3) NOT NULL DEFAULT '1',
					PRIMARY KEY (id),
					INDEX url (url),
					INDEX title (title)
					 ) COLLATE='utf8_unicode_ci' ENGINE=InnoDB";

		dbDelta($sql);
	}

	if(($wpdb->get_var("SHOW TABLES LIKE '$t_map_log'") != $t_map_log)){
		$sql = "CREATE TABLE " . $t_map_log . " (
					id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					config_id INT(10) UNSIGNED NULL DEFAULT NULL,
					date_time INT(10) UNSIGNED NULL DEFAULT NULL,
					info VARCHAR(255) NULL DEFAULT NULL,
					url VARCHAR(255) NULL DEFAULT NULL,
					PRIMARY KEY (id)
					 ) COLLATE='utf8_unicode_ci' ENGINE=InnoDB";

		dbDelta($sql);
	}

	if(($wpdb->get_var("SHOW TABLES LIKE '$t_map_autolink'") != $t_map_autolink)){
		$sql = "CREATE TABLE " . $t_map_autolink . " (
			id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			keyword VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
			details VARCHAR(200) NOT NULL COLLATE 'utf8_unicode_ci',
			PRIMARY KEY (id)
			 ) COLLATE='utf8_unicode_ci' ENGINE=InnoDB";

		dbDelta($sql);
	}
}

include_once(MAP_PLUGIN_DIR . '/functions/my-autopost-function.php');