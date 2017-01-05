<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class My_Autopost_Task_List_Table extends WP_List_Table
{
    /**
     * 获取任务列表
     *
     * @param int $per_page         每页显示多少条任务,默认显示15条
     * @param int $page_number      显示第几页的任务,默认显示第一页
     * @return array                二维数组,返回任务列表
     */
    public function get_tasks($per_page = 15, $page_number = 1)
    {
        global $wpdb, $t_map_config;

        $sql = "SELECT * FROM $t_map_config";
        $sql .= ' ORDER BY id DESC';
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        return $wpdb->get_results($sql);
    }

    /**
     * 获取日志条数
     *
     * @return int      返回满足条件的日志条数
     */
    public function get_tasks_count()
    {
        global $wpdb, $t_map_config;

        $sql = "SELECT count(*) FROM $t_map_config";
        return $wpdb->get_var($sql);
    }

    /**
     * 显示表格上方切换条
     */
    public function views()
    {
        global $wpdb, $t_map_updated_record;
        $all_num = $wpdb->get_var('SELECT count(*) FROM ' . $t_map_updated_record);
        $published_num = $wpdb->get_var('SELECT count(*) FROM ' . $t_map_updated_record . ' WHERE url_status = 1');
        $pending_num = $wpdb->get_var('SELECT count(*) FROM ' . $t_map_updated_record . ' WHERE url_status = 2');
        $ignored_num = $wpdb->get_var('SELECT count(*) FROM ' . $t_map_updated_record . ' WHERE url_status = 3');
        ?>
        <ul class='subsubsub'>
            <li><a class="current"><?php _e('Posts'); ?></a> :</li>
            <li><a href="admin.php?page=my-autopost/my-autopost-posts.php" ><?php _e('All'); ?> <span class="count">(<?php echo number_format($all_num);?>)</span></a> |</li>
            <li><a href="admin.php?page=my-autopost/my-autopost-posts.php&url_status=1" ><?php _e('Published'); ?> <span class="count">(<?php echo number_format($published_num);?>)</span></a> |</li>
            <li><a href="admin.php?page=my-autopost/my-autopost-posts.php&url_status=2" ><?php _e('Pending Extraction', 'my-autopost'); ?> <span class="count">(<?php echo number_format($pending_num);?>)</span></a> |</li>
            <li><a href="admin.php?page=my-autopost/my-autopost-posts.php&url_status=3" ><?php _e('Ignored', 'my-autopost'); ?> <span class="count">(<?php echo number_format($ignored_num);?>)</span></a></li>
        </ul>
        <?php
    }

    /**
     * 显示表头
     */
    public function print_column_headers()
    {
        ?>
        <th scope="col" style="text-align:center" width="1%"></th>
        <th scope="col" style="text-align:center"><?php _e('Task Name', 'my-autopost'); ?></th>
        <th scope="col" style="text-align:center"><?php _e('Log', 'my-autopost'); ?></th>
        <th scope="col" style="text-align:center"><?php _e('Updated Articles', 'my-autopost'); ?></th>
        <th scope="col" style="text-align:center"></th>
        <?php
    }

    /**
     * 显示表格数据或占位数据(无内容时)
     */
    public function display_rows_or_placeholder()
    {
        if ($this->has_items()) {
            $this->display_rows();
        } else {
            echo '<tr class="no-items"><td class="colspanchange" colspan=4">';
            _e('No tasks found', 'my-autopost');
            echo '</td></tr>';
        }
    }

    /**
     * 显示表格数据
     */
    public function display_rows()
    {
        foreach ($this->items as $item) {
            echo "\n\t" . $this->single_row($item);
        }
    }

    /**
     * 返回表格单行格式数据
     *
     * @param object $item  表格数据
     * @return string       表格单行格式数据
     */
    public function single_row($item)
    {
        global $wpdb, $t_map_updated_record, $t_map_log;

        if ($item->activation == 0) {
            $row_class = 'class="inactive"';
        } else {
            $row_class = 'class="active"';
        }

        $r = "<tr style=\"text-align:center\" $row_class>";

        /* 任务状态条 */
        $r .= '<th scope="row" class="check-column"></th>';

        /* 任务名称 */
        $current_page = $this->get_pagenum();
        $r .= "<td><strong>$item->name</strong><div class=\"row-actions-visible\">";
        if ($item->activation == 0) {
            $activate = __('Activate');
            $activate_url = 'admin.php?page=my-autopost/my-autopost-tasklist.php&activate=' . $item->id . '&paged=' . $current_page;
            $r .= "<a href=\"$activate_url\">$activate</a> | ";
        } else {
            $deactivate = __('Deactivate');
            $deactivate_url = 'admin.php?page=my-autopost/my-autopost-tasklist.php&deactivate=' . $item->id . '&paged=' . $current_page;
            $r .= "<a href=\"$deactivate_url\">$deactivate</a > | ";
        }
        $setting = __('Setting', 'my-autopost');
        $setting_url = 'admin.php?page=my-autopost/my-autopost-tasklist.php&saction=edit&task_id=' . $item->id . '&paged=' . $current_page;
        $r .= "<a href=\"$setting_url\">$setting</a> | ";
        $delete = __('Delete');
        $delete_url = 'admin.php?page=my-autopost/my-autopost-tasklist.php&saction=delete&task_id=' . $item->id . '&paged=' . $current_page;
        $r .= "<a href='$delete_url' class='delete'>$delete</a>";
        $r .= '</div></td>';

        /* 日志 */
        $r .= '<td>';
        if ($item->last_update_time > 0) {
            $last_detected = __('Last detected', 'my-autopost');
            $last_update_time = my_autopost_maktimes($item->last_update_time);
            $expected_next_detected = __('Expected next detect', 'my-autopost');
            $next_update_time = my_autopost_maktimes($item->last_update_time + $item->update_interval * 60);
            $r .= "${last_detected}<b>${last_update_time}</b>,${expected_next_detected}<b>${next_update_time}</b>";

            if ($item->m_extract == 1) {
                $manually_extraction = __('Manually Selective Extraction', 'my-autopost');
                $post_pending_extraction = __('Posts Pending Extraction', 'my-autopost');
                $manually_extraction_url = 'admin.php?page=my-autopost/my-autopost-post.php&task_id=' . $item->id . '&url_status=0';
                $pending_num = $wpdb->get_var('SELECT count(*) FROM ' . $t_map_updated_record .'  WHERE url_status = 0 AND config_id=' . $item->id);
                $r .= "<br/>${$manually_extraction}: <a href=\"$manually_extraction_url\"><b>$pending_num</b>$post_pending_extraction</a>";
            } else {
                if ($item->post_id > 0) {
                    $post_link = get_permalink($item->post_id);
                    $post_title = get_the_title($item->post_id);
                    $recently_updated = __('Recently updated articles', 'my-autopost');
                    $r .= "<br/>$recently_updated: <b><a href=\"$post_link\" target=\"_blank\">$post_title</a></b>";
                }
            }
        } else {
            $r .= __('Has not updated any post', 'my-autopost');
        }
        if ($item->last_error > 0) {
            $last_error = $wpdb->get_var('SELECT info FROM ' . $t_map_log . ' WHERE id=' . $item->last_error);
            $error_occured = __('An error occurred', 'my-autopost');
            $ignore = __('Ignore', 'my-autopost');
            $error_log_url = 'admin.php?page=my-autopost/my-autopost-logs.php&task_id=' . $item->id . '&log_id=' . $item->last_error;
            $ignore_url = 'admin.php?page=my-autopost/my-autopost-tasklist.php&saction=ignore&id=' . $item->id;
            $r .= "<br/><b>$error_occured</b> : <span class=\"trash\"><a href=\"$error_log_url\"><b>$last_error</b></a></span>[<a href=\"$ignore_url\">$ignore</a>]";
        }
        $r .= '</td>';

        /* 已更新文章 */
        $post_url = 'admin.php?page=my-autopost/my-autopost-updatedpost.php&task_id=' . $item->id . '&url_status=1';
        $r .= "<td><a href=\"$post_url\">$item->updated_num</a></td>";

        /* 任务状态及操作 */
        $r .= '<td>';
        if ($item->is_running == 1) {
            $is_running = __('Is running', 'my-autopost');
            $abort = __('Abort', 'my-autopost');
            $abort_url = 'admin.php?page=my-autopost/my-autopost-tasklist.php&saction=abort&task_id=' . $item->id;
            $running_gif_path = MAP_PLUGIN_URI . '/images/running.gif';
            $r .= "$is_running <img src=\"${running_gif_path}\" width=\"15\" height=\"15\" style=\"vertical-align:text-bottom;\" />";
            $r .= "<div class=\"row-actions-visible\"><a href=\"$abort_url\">$abort</a></div>";

        } elseif ($item->activation == 1) {
            $update_now = __('Update Now', 'my-autopost');
            $update_url = 'admin.php?page=my-autopost/my-autopost-tasklist.php&saction=fetch&task_id=' . $item->id;
            $r .= "<a href=\"$update_url\">$update_now</a>";
        } else {
            $r .= __('Task deactivated.', 'my-autopost');
        }
        $r .= '</td>';

        $r .= '</tr>';

        return $r;
    }

    /**
     * 只在表格下侧显示分页列表
     *
     * @param string $which
     */
    protected function pagination( $which )
    {
        if ($which == 'bottom') {
            parent::pagination($which);
        }
    }

    /**
     * 为表格显示准备数据
     */
    public function prepare_items()
    {
        $per_page     = $this->get_items_per_page('logs_per_page', 15);
        $current_page = $this->get_pagenum();
        $this->items  = $this->get_tasks($per_page, $current_page);
        $total_items  = $this->get_tasks_count();

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ));
    }

    /**
     * 显示表格
     */
    public function display()
    {
        ?>
        <table class="widefat plugins"  style="margin-top:4px">
            <thead>
                <tr>
                    <?php $this->print_column_headers(); ?>
                </tr>
            </thead>

            <tbody id="the-list">
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

            <tfoot>
                <tr>
                    <?php $this->print_column_headers(); ?>
                </tr>
            </tfoot>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }

}
