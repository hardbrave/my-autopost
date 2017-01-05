<?php

include MAP_PLUGIN_DIR . '/functions/my-autopost-common-function.php';
include MAP_PLUGIN_DIR . '/functions/my-autopost-db-function.php';
include MAP_PLUGIN_DIR . '/functions/my-autopost-article-function.php';
include MAP_PLUGIN_DIR . '/functions/my-autopost-simple-html-dom.php';
include MAP_PLUGIN_DIR . '/functions/my-autopost-crawl-function.php';


/* 引入自定义的CSS样式 */
add_action('admin_head', 'my_autopost_head');
function my_autopost_head()
{
    $css_url = plugins_url('/css/my-autopost.css', __FILE__);
    echo '<link rel="stylesheet" type="text/css" href="' . $css_url . '"/>';
}

/* 更新任务 */
if (get_option('my_autopost_update_method') == 1) {
    add_action('init', 'my_autopost_update_cron_url');
} else {
    add_action('shutdown', 'my_autopost_update_after_page_load');
}

/**
 * 使用Cron计划任务更新
 */
function my_autopost_update_cron_url()
{
    if ($_GET['update_autopost'] == 1) {
        my_autopost_check_update(true);
        die;
    }
}

/**
 * 当页面加载后,自动检测更新
 */
function my_autopost_update_after_page_load()
{
    my_autopost_check_update(false);
}

add_filter('content_save_pre', 'my_autopost_link_content_filter');
function my_autopost_link_content_filter($content)
{
    global $wpdb, $t_map_autolink;
    $autolinks = $wpdb->get_results('SELECT * FROM ' . $t_map_autolink);
    return my_autopost_link_replace($content, $autolinks);
}
