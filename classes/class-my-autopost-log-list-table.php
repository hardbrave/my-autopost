<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class My_Autopost_Log_List_Table
 */
class My_Autopost_Log_List_Table extends WP_List_Table
{
    /**
     * @var 任务id
     */
    private $task_id;


    /**
     * My_Autopost_Log_List_Table constructor.
     *
     * @param array|string $args
     */
    public function __construct($args)
    {
        parent::__construct($args);

        $args = wp_parse_args($args, array(
            'task_id' => 0,
        ));
        $this->task_id = $args['task_id'];
    }

    /**
     * 设置$task_id
     *
     * @param string $name
     * @param mixed  $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (in_array($name, array('task_id'))) {
            $this->$name = $value;
        } else {
            return parent::__set($name, $value);
        }
    }

    /**
     * 获取日志列表
     *
     * @param int $per_page         每页显示多少条日志,默认显示15条
     * @param int $page_number      显示第几页的日志,默认显示第一页
     * @return array                二维数组,返回日志列表
     */
    public function get_logs($per_page = 15, $page_number = 1)
    {
        global $wpdb, $t_map_log, $t_map_config;

        $sql = "SELECT t1.*, t2.name FROM $t_map_log t1 left join $t_map_config t2 on t1.config_id = t2.id WHERE 1";
        if ($this->task_id) {
            $sql .= ' AND t1.config_id = ' . $this->task_id;
        }
        $sql .= ' ORDER BY t1.id DESC';
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        return $wpdb->get_results($sql);
    }

    /**
     * 获取日志条数
     *
     * @return int      返回满足条件的日志条数
     */
    public function get_logs_count()
    {
        global $wpdb, $t_map_log;

        $sql = "SELECT count(*) FROM $t_map_log";
        if ($this->task_id) {
            $sql .= ' WHERE config_id = ' . $this->task_id;
        }

        return $wpdb->get_var($sql);
    }

    /**
     * 显示表格导航条与分页控件
     *
     * @param $which    top:导航栏上方,bottom:导航栏下方
     */
    protected function display_tablenav($which)
    {
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">
            <?php
            $this->extra_tablenav($which);
            $this->pagination($which);
            ?>
            <br class="clear" />
        </div>
        <?php
    }

    /**
     * 显示表格上传导航条界面
     *
     * @param $which    top:导航栏上方,bottom:导航栏下方
     * @return array    批量操作格式数组
     */
    protected function extra_tablenav($which)
    {
        global $wpdb, $t_map_config;
        if ($which == 'top') {
            $tasks = $wpdb->get_results('SELECT id, name FROM ' . $t_map_config . ' ORDER BY id');
            ?>
            <div class="alignleft actions">
                <select name="task_id" id="task_id" onchange="queryTask()">
                    <option value=""><?php _e('View all tasks', 'my-autopost'); ?></option>
                    <?php foreach ($tasks as $task): ?>
                        <option value="<?php echo $task->id; ?>" <?php selected($this->task_id == $task->id); ?>><?php echo $task->name; ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="alignright">
                <input value="<?php _e('Empty Logs', 'my-autopost'); ?>" class="button" type="button" onclick="emptyLogs()"/>
            </div>
            <script type="text/javascript">
                function queryTask(){
                    jQuery("#myform").submit();
                }
                function emptyLogs(){
                    if(confirm("<?php _e('Confirm Empty Logs?', 'my-autopost'); ?>")){
                        jQuery("#saction").val("clear_logs");
                        jQuery("#myform").submit();
                    }else return false;
                }
            </script>
            <?php
        }
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
     * 显示表头
     */
    public function print_column_headers()
    {
        ?>
        <th scope="col" style="text-align:center"></th>
        <th scope="col" style="text-align:center"><?php _e('Task Name', 'my-autopost'); ?></th>
        <th scope="col" style="text-align:center"><?php _e('Info', 'my-autopost'); ?></th>
        <th scope="col" style="text-align:center"><?php _e('Involved URL', 'my-autopost'); ?></th>
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
            _e('No logs found', 'my_autopost');
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
        $date_time = date('Y-m-d H:i:s', $item->date_time);
        $url = substr($item->url, 0, 40) . (strlen($item->url) > 40 ? '...' : '');

        $r = "<tr style=\"text-align:center\" >";
        $r .= "<td>$date_time</td>";
        $r .= "<td>$item->name</td>";
        $r .= "<td><span style='color: red'>$item->info</td>";
        $r .= "<td><a href='$item->url' target='_blank' title='$item->url'>$url</td>";
        $r .= '</tr>';

        return $r;
    }

    /**
     * 为表格显示准备数据
     */
    public function prepare_items()
    {
        $per_page     = $this->get_items_per_page('logs_per_page', 15);
        $current_page = $this->get_pagenum();
        $this->items  = $this->get_logs($per_page, $current_page);
        $total_items  = $this->get_logs_count();

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
        <?php $this->display_tablenav('top'); ?>
        <table class="widefat"  style="margin-top:4px">
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
        $this->display_tablenav('bottom');
    }
}