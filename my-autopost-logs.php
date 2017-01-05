<?php

require_once(MAP_PLUGIN_DIR . '/classes/class-my-autopost-log-list-table.php');
$log_list_table = new My_Autopost_Log_List_Table(array(
    'task_id' => $_POST['task_id']));

$saction = $_POST['saction'];
switch ($saction) {
    case 'clear_logs':
        clear_logs();
        break;
    default:
        show_log_list();
        break;
}

/**
 * 清除日志
 */
function clear_logs()
{
    global $wpdb, $t_map_log;
    $num = $wpdb->query("DELETE FROM $t_map_log");
    $wpdb->get_row("OPTIMIZE TABLE $t_map_log");
    $wpdb->query("UPDATE $t_map_log SET last_error = 0");
    $msg = $num . ' ' . __('items permanently deleted.','my-autopost');

    show_log_list($msg);
}

/**
 * 显示日志列表
 *
 * @param string $msg   信息
 */
function show_log_list($msg = '')
{
    include_once(MAP_PLUGIN_DIR . '/views/view-log-list.php');
}



