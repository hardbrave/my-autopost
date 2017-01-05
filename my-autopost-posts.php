<?php

require_once(MAP_PLUGIN_DIR . '/classes/class-my-autopost-post-list-table.php');
$post_list_table = new My_Autopost_Post_List_Table(array(
    'url_status'    =>  $_GET['url_status'],
    'search_text'   =>  $_POST['s']));

$saction = $_POST['saction'];
switch ($saction) {
    case 'empty_data':
        empty_data( $_POST['task_id'], $_GET['url_status'], $_POST['rdays'], $_POST['trash_post']);
        break;
    case 'filter_data':
        filter_data($_POST['task_id'], $_POST['url_status']);
        break;
    case 'bulk_delete':
        bulk_delete($_POST['ids'], $_POST['trash_post_bulk_action']);
        break;
    case 'bulk_extraction':
        bulk_extraction($_POST['ids']);
        break;
    case 'bulk_ignore':
        bulk_ignore($_POST['ids']);
        break;
    default:
        show_post_list();
}

/**
 * 清空数据
 *
 * @param int        $task_id       任务id
 * @param int        $url_status    文章状态
 * @param int        $days          保留天数
 * @param bool|false $delete_post   是否同时删除文章
 */
function empty_data($task_id, $url_status, $days, $delete_post = false)
{
    global $wpdb, $t_map_updated_record, $t_map_config;

    $condition = '';
    if (!$url_status)
        $condition .= ' AND url_status =' . $url_status;

    $date_time = current_time('timestamp') - $days * 24 * 60 * 60;

    if (!$delete_post) {
        if (!$task_id) {
            $num = $wpdb->query("DELETE FROM $t_map_updated_record WHERE date_time < $date_time $condition");
        } else {
            $num = $wpdb->query("DELETE FROM $t_map_updated_record WHERE config_id = $task_id AND date_time < $date_time $condition");
        }
    } else {
        if (!$task_id) {
            $posts = $wpdb->get_results("SELECT post_id, config_id FROM $t_map_updated_record WHERE date_time < $date_time $condition");
            foreach ($posts as $post) {
                if ($post->post_id > 0) {
                    wp_trash_post($post->post_id);
                    $wpdb->query("UPDATE $t_map_config SET updated_num = updated_num - 1 WHERE id = $post->config_id");
                }
            }
            $num = $wpdb->query("DELETE FROM $t_map_updated_record WHERE date_time < $date_time $condition");

        } else {
            $posts = $wpdb->get_results("SELECT post_id, config_id FROM $t_map_updated_record WHERE config_id = $task_id AND date_time < $date_time $condition");
            foreach ($posts as $post) {
                if ($post->post_id > 0) {
                    wp_trash_post($post->post_id);
                    $wpdb->query("UPDATE $t_map_config SET updated_num = updated_num - 1 WHERE id = $post->config_id");
                }
            }
            $num = $wpdb->query("DELETE FROM $t_map_updated_record WHERE config_id = $task_id AND date_time <$date_time $condition");
        }
    }
    $wpdb->get_row("OPTIMIZE TABLE $t_map_updated_record");
    $msg = $num . ' ' . __('items permanently deleted.', 'my-autopost');

    show_post_list($msg);
}

/**
 * 根据任务id和文章状态来筛选文章
 *
 * @param int   $task_id        任务id
 * @param int   $url_status     文章状态
 */
function filter_data($task_id, $url_status)
{
    global $post_list_table;
    if ($task_id) {
        $post_list_table->task_id = $task_id;
    }
    if ($url_status) {
        $post_list_table->url_status = $url_status;
    }

    show_post_list();
}

/**
 * 批量删除
 *
 * @param array $ids                id列表
 * @param bool|false $delete_post   是否同时删除文章
 */
function bulk_delete($ids, $delete_post = false)
{
    global $wpdb, $t_map_updated_record, $t_map_config;
    if ($ids) {
        if (!$delete_post) {
            foreach ($ids as $id) {
                $wpdb->query("DELETE FROM $t_map_updated_record WHERE id = $id");
            }
        } else {
            foreach ($ids as $id) {
                $row = $wpdb->get_row("SELECT post_id, config_id, url_status FROM $t_map_updated_record WHERE id = $id");
                if ($row->url_status == 1) {
                    wp_trash_post($row->post_id);
                }
                $wpdb->query("DELETE FROM $t_map_updated_record WHERE id = $id");
                if ($row->url_status == 1) {
                    $wpdb->query("UPDATE $t_map_config SET updated_num = updated_num - 1 WHERE id = $row->config_id");
                }
            }
        }
        $msg = count($ids) . ' ' .__('items permanently deleted.', 'my-autopost');
    }
    show_post_list($msg);
}

/**
 * 批量抓取
 *
 * @param array $ids    id列表
 */
function bulk_extraction($ids)
{
    global $wpdb, $t_map_updated_record;
    if ($ids) {
        $query_ids = implode(',', $ids);
        $posts = $wpdb->get_results("SELECT config_id, id, url FROM $t_map_updated_record WHERE id in ($query_ids) AND url_status = 2 ORDER BY config_id, id");
        if (count($posts) == 0) {
            return;
        }
        ignore_user_abort(true);
        set_time_limit((int)get_option('my_autopost_time_limit'));
        my_autopost_print_info('<div class="updated fade"><p><b>' . __('Being processed, the processing may take some time, you can close the page', 'my-autopost') . '</b></p></div>');
        echo '<div class="updated fade">';
        foreach ($posts as $post) {
            my_autopost_db_update_task_running_status($post->config_id, 1);
            $task_config = my_autopost_db_get_task($post->config_id);
            $post_num = my_autopost_fetch_and_post($post->config_id, array($post->url), $task_config, 1, $post->id);
            if ($post_num > 0) {
                echo '<p>' . __('Task', 'my-autopost') . ': <b>' . $task_config->name . '</b> , ' . __('updated', 'my-autopost') . ' <b>' . $post_num . '</b> ' . __('articles', 'my-autopost') . '</p>';
            }
            my_autopost_db_update_task_running_status($post->config_id, 0);
        }
        echo '</div>';
    }
    show_post_list();
}

/**
 * 批量忽略
 *
 * @param array $ids    id列表
 */
function bulk_ignore($ids)
{
    global $wpdb, $t_map_updated_record;
    $count = 0;
    if ($ids) {
        foreach ($ids as $id) {
            if ($wpdb->query("UPDATE $t_map_updated_record SET url_status = 3 WHERE id = $id AND url_status = 1")) {
                $count++;
            }
        }
    }
    $msg = $count.' '.__('items updated.', 'my-autopost');
    show_post_list($msg);
}

/**
 * 显示文章列表
 *
 * @param string $msg   信息
 */
function show_post_list($msg = '')
{
    include_once(MAP_PLUGIN_DIR . '/views/view-post-list.php');
}

function my_autopost_query_duplicate($similar_percent, $posts)
{
    ignore_user_abort(true);
    set_time_limit(0);
    update_option('my-autopost-run-query-duplicate', 1);
    update_option('my-autopost-duplicate-ids', null);
    $num = count($posts);
    for ($i = 0; $i < $num; $i++) {
        if ($posts[$i]->id == 0) {
            continue;
        }
        $my_autopost_check_title = $posts[$i]->title;
        echo ('<p>Begin check <b>' . $my_autopost_check_title) . '</b> whether has duplication</p>';
        ob_flush();
        flush();
        $duplicateIds = get_option('my-autopost-duplicate-ids');
        for ($j = $i + 1; $j < $num; $j++) {
            if ($posts[$j]->id == 0) {
                continue;
            }
            similar_text($my_autopost_check_title, $posts[$j]->title, $percent);
            if ($percent >= $similar_percent) {
                $duplicateIds[] = $posts[$i]->id;
                $duplicateIds[] = $posts[$j]->id;
                $posts[$i]->id = 0;
                $posts[$j]->id = 0;
                update_option('my-autopost-duplicate-ids', $duplicateIds);
            }
        }
        $posts[$i]->id = 0;
    }
    update_option('my-autopost-run-query-duplicate', 0);
}