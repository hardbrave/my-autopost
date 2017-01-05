<?php

function my_autopost_db_update_task_updated_num($id, $num, $post_id)
{
    global $wpdb, $t_map_config;
    $current_time = current_time('timestamp');
    $wpdb->query("UPDATE $t_map_config SET updated_num = updated_num + $num, post_id = $post_id, last_update_time = $current_time WHERE  id = $id");
}

function my_autopost_db_update_task_running_status($id, $status)
{
    global $wpdb, $t_map_config;
    $current_time = current_time('timestamp');
    $wpdb->query("UPDATE $t_map_config SET is_running = $status, last_update_time = $current_time WHERE id = $id");
}

function my_autopost_db_task_is_running($id)
{
    global $wpdb, $t_map_config;
    return $wpdb->get_var("SELECT is_running $t_map_config WHERE id = $id");
}

function my_autopost_db_get_task($id)
{
    global $wpdb, $t_map_config;
    return $wpdb->get_row("SELECT * FROM $t_map_config WHERE id = $id");
}

function my_autopost_db_get_list_urls($id)
{
    global $wpdb, $t_map_config_url_list;
    return $wpdb->get_results("SELECT url FROM $t_map_config_url_list WHERE config_id = $id ORDER BY id");
}

function my_autopost_db_check_post_id($post_id)
{
    global $wpdb, $t_map_updated_record;
    return $wpdb->get_var("SELECT count(*) FROM $t_map_updated_record WHERE post_id = $post_id");
}

function my_autopost_db_check_url($id, $url)
{
    global $wpdb, $t_map_updated_record;
    return $wpdb->get_var("SELECT count(*) FROM $t_map_updated_record WHERE url = $url");
}

function my_autopost_db_check_posted_url($id, $url)
{
    global $wpdb, $t_map_updated_record;
    return $wpdb->get_var("SELECT count(*) FROM $t_map_updated_record WHERE url = $url AND url_status = 1");
}

function my_autopost_db_check_title($id, $title, $status = 1)
{
    global $wpdb, $t_map_updated_record;
    return $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$t_map_updated_record} WHERE config_id = %d AND title = %s AND url_status = %d", $id, $title, $status));
}

function my_autopost_db_get_taxonomy_by_term_id($id)
{
    global $wpdb;
    return $wpdb->get_var("SELECT {$wpdb->term_taxonomy}.taxonomy FROM {$wpdb->term_taxonomy} WHERE {$wpdb->term_taxonomy}.term_id = {$id}");
}

function my_autopst_db_transaction_work($do_work, $parameters)
{
    global $wpdb;
    $wpdb->query("START TRANSACTION");
    if($do_work($parameters)){
        $wpdb->query("COMMIT");
    }else{
        $wpdb->query("ROLLBACK");
    }
}

function my_autopost_db_log_err($parameters)
{
    global $wpdb, $t_map_config, $t_map_log;
    extract($parameters);
    $current_time = current_time('timestamp');
    $wpdb->query("INSERT INTO $t_map_log(config_id, date_time, info, url) VALUES ($task_id, $current_time, '$info', '$url')");
    if ($wpdb->last_error) {
        return false;
    }
    $log_id = $wpdb->get_var('SELECT LAST_INSERT_ID()');
    $wpdb->query("UPDATE $t_map_config SET last_error = $log_id WHERE id = $task_id");
    if ($wpdb->last_error) {
        return false;
    }
}

function my_autopost_db_insert_or_update_record($parameters)
{
    global $wpdb, $t_map_updated_record, $t_map_config;

    extract($parameters);
    if ($post_id) {
        $current_time = current_time('timestamp');
        if (!$record_id) {
            $wpdb->query($wpdb->prepare("INSERT INTO {$t_map_updated_record} (config_id, url, title, post_id, date_time, url_status) VALUES (%d, %s, %s, %d, %d)",
                $task_config->id, $url, $title, $post_id, $current_time, $status));
            if ($wpdb->last_error) {
                return false;
            }
        } else {
            $wpdb->query("UPDATE $t_map_updated_record SET post_id = $post_id, date_time = $current_time, url_status = $status WHERE id = $record_id");
            if ($wpdb->last_error) {
                return false;
            }
        }
        $wpdb->query("UPDATE $t_map_config SET updated_num = updated_num + 1, post_id = $post_id, last_update_time = $current_time WHERE  id = $task_config->id");
        if ($wpdb->last_error) {
            return false;
        }
    }
}