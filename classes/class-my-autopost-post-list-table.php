<?php

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class My_Autopost_Post_List_Table
 */
class My_Autopost_Post_List_Table extends WP_List_Table
{
    /**
     * @var 文章状态(1:已发布 2:待发布 3:已忽略)
     */
    private $url_status;

    /**
     * @var 任务id
     */
    private $task_id;

    /**
     * @var 搜索文本
     */
    private $search_text;

    /**
     * My_Autopost_Post_List_Table constructor.
     *
     * @param array|string $args
     */
    public function __construct($args)
    {
        parent::__construct($args);

        $args = wp_parse_args($args, array(
            'url_status' => 0,
            'task_id' => 0,
            'search_text' => '',
        ));
        $this->url_status = $args['url_status'];
        $this->task_id = $args['task_id'];
        $this->search_text = $args['search_text'];
    }

    /**
     * 设置$url_status/$task_id/$search_text
     *
     * @param string $name
     * @param mixed  $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (in_array($name, array('url_status', 'task_id', 'search_text'))) {
            $this->$name = $value;
        } else {
            return parent::__set($name, $value);
        }
    }

    /**
     * 返回表格上侧切换条格式数组
     *
     * @return array      二维数组,表格上侧切换条格式数组
     */
    protected function get_views()
    {
        global $wpdb, $t_map_updated_record;
        $post_url = 'admin.php?page=my-autopost/my-autopost-posts.php';
        $views = array(
            array(
                'All',
                $post_url,
                $wpdb->get_var('SELECT count(*) FROM ' . $t_map_updated_record),
                (!$this->url_status) ? 'class="current"' : '',
                __('All'),
            ),
            array(
                'Published',
                $post_url . '&url_status=1',
                $wpdb->get_var('SELECT count(*) FROM ' . $t_map_updated_record . ' WHERE url_status = 1'),
                ($this->url_status == 1) ? 'class="current"' : '',
                __('Published'),
            ),
            array(
                'Pending',
                $post_url . '&url_status=2',
                $wpdb->get_var('SELECT count(*) FROM ' . $t_map_updated_record . ' WHERE url_status = 0'),
                ($this->url_status == 2) ? 'class="current"' : '',
                __('Pending Extraction','my-autopost'),
            ),
            array(
                'Ignored',
                $post_url . '&url_status=3',
                $wpdb->get_var('SELECT count(*) FROM ' . $t_map_updated_record . ' WHERE url_status = -1'),
                ($this->url_status == 3) ? 'class="current"' : '',
                __('Ignored','my-autopost'),
            ),
            array(
                'NotExist',
                $post_url . '&url_status=4',
                $wpdb->get_var('SELECT count(*) FROM ' . $t_map_updated_record . ' t1 where t1.post_id >0 AND not exists (select * from '.$wpdb->posts.' t2 where t2.ID = t1.post_id)'),
                ($this->url_status == 4) ? 'class="current"' : '',
                __('Not Exists','my-autopost'),
            ),
        );

        $view_links = array();
        foreach($views as $view) {
            list($key, $url, $number, $class, $title) = $view;
            $view_links[$key] = $this->get_view_link($url, $class, $number, $title);
        }
        return $view_links;
    }

    /**
     * 返回视图链接
     *
     * @param string $url       链接URL
     * @param string $class     链接样式
     * @param int    $number    数量
     * @param string $title     链接标题
     * @return string           视图链接
     */
    private function get_view_link($url, $class, $number, $title)
    {
        return "<a href='$url' $class>$title<span class='count'>(" . number_format($number) . ")</span></a>";
    }

    /**
     * 获取文章列表
     *
     * @param int $per_page         每页显示多少条文章,默认显示30条
     * @param int $page_number      显示第几页的文章,默认显示第一页
     * @return array                二维数组,返回文章列表
     */
    public function get_posts($per_page = 30, $page_number = 1)
    {
        global $wpdb, $t_map_updated_record, $t_map_config;
        $sql = 'SELECT t1.*, t2.name FROM '.$t_map_updated_record.' t1 left join '.$t_map_config.' t2 on t1.config_id = t2.id WHERE 1';
        if ($this->url_status) {
            $sql .= " AND t1.url_status = $this->url_status";
        }
        if ($this->search_text) {
            $sql .= " AND t1.title LIKE '%$this->search_text%'";
        }
        if ($this->task_id) {
            $sql .= " AND t2.id = $this->task_id";
        }
        $sql .= ' ORDER BY t1.id DESC';
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        $posts =  $wpdb->get_results($sql);
        return $posts;
    }

    /**
     * 获取文章条数
     *
     * @return int      返回满足条件的文章条数
     */
    public function get_posts_count()
    {
        global $wpdb, $t_map_updated_record, $t_map_config;
        $sql = 'SELECT COUNT(*) FROM '.$t_map_updated_record.' t1 left join '.$t_map_config.' t2 on t1.config_id = t2.id WHERE 1';
        if ($this->url_status) {
            $sql .= " AND t1.url_status = $this->url_status";
        }
        if ($this->search_text) {
            $sql .= " AND t1.title LIKE '%$this->search_text%'";
        }
        if ($this->task_id) {
            $sql .= " AND t2.id = $this->task_id";
        }

        return $wpdb->get_var($sql);
    }

    /**
     * 没有项目时显示的内容
     */
    public function no_items()
    {
        _e('No posts found.', 'my_autopost');
    }

    /**
     * 获取行信息
     *
     * @return array    返回行信息数组
     */
    protected function get_column_info()
    {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'name'      =>  __('Task Name', 'my-autopost'),
            'url'       =>  __('Source URL', 'my-autopost'),
            'title'     =>  __('Title', 'my-autopost'),
            'date'      =>  __('Date'),
            'status'    =>  __('Status')
        );
        $hidden = array();
        $sortable = array();
        $primary = 'name';

        return array($columns, $hidden, $sortable, $primary);
    }

    /**
     * 返回checkbox列格式
     *
     * @param object $item  行数据
     * @return string       checkbox列格式字符串
     */
    public function column_cb($item)
    {
        $cb = "<input type='checkbox' name='ids[]' value='$item->id'/>";
        return $cb;
    }

    /**
     * 返回文章名称列格式
     *
     * @param object $item  行数据
     * @return string       任务名称列格式字符串
     */
    public function column_name($item)
    {
        $name = "<a href='admin.php?page=my-autopost/my-autopost-posts.php&task_id=$item->config_id'>$item->name</a>";
        return $name;
    }

    /**
     * 返回文章url列格式
     *
     * @param object $item  行数据
     * @return string       任务url列格式字符串
     */
    public function column_url($item)
    {
        $url = "<a href='$item->url' target='_blank' title='$item->url'>" . substr($item->url, 0, 40).((strlen($item->url)>40)?'...':'') . "</a>";
        return $url ;
    }

    /**
     * 返回任务名称列格式
     *
     * @param object $item  行数据
     * @return string       任务名称列格式字符串
     */
    public function column_title($item)
    {
        $title = get_the_title($item->post_id);
        $title = '<strong><a href="'.get_permalink($item->post_id).'" target="_blank">'.$title.'</a></strong>';;
        return $title;
    }

    /**
     * 返回文章日期列格式
     *
     * @param object $item  行数据
     * @return string       文章日期列格式字符串
     */
    public function column_date($item)
    {
        $date = date('Y-m-d H:i:s', $item->date_time);
        return $date;
    }

    /**
     * 返回文章状态列格式
     *
     * @param object $item  行数据
     * @return string       文章状态列格式字符串
     */
    public function column_status($item)
    {
        switch ($item->url_status) {
            case 0:
                $status = '<a href="admin.php?page=my-autopost/my-autopost-posts.php&extraction_id='.$item->id.'" title="'.__('Extraction and post', 'my-autopost').'">'.__('Pending Extraction', 'my-autopost').'</a>';
                break;
            case 1:
                $status = __('Published');
                break;
            case -1:
                $status = '<i>'.__('Ignored', 'my-autopost').'</i>';
                break;
        }
        return $status;
    }

    /**
     * 显示表格导航条与分页控件
     *
     * @param $which    top:导航栏上方,bottom:导航栏下方
     */
    protected function display_tablenav( $which )
    {
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
            <?php
            $this->extra_tablenav( $which );
            $this->pagination( $which );
            ?>
            <br class="clear" />
        </div>
        <?php
    }

    /**
     * 显示表格上方导航条界面
     *
     * @param $which    top:导航栏上方,bottom:导航栏下方
     */
    protected function extra_tablenav($which)
    {
        if ('top' === $which) {
            global $wpdb, $t_map_config;
            $tasks = $wpdb->get_results('SELECT id,name FROM '.$t_map_config.' ORDER BY id');

        ?>
            <div class="alignleft actions">
                <select name="bulk_action" id="bulk-action">
                    <option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
                    <?php if (!$this->url_status || $this->url_status == 2 || $this->url_status == 4): ?>
                        <option value="bulk_extraction"><?php _e('Extraction and post', 'my-autopost'); ?></option>
                        <option value="bulk_ignore"><?php _e('Ignore', 'my-autopost'); ?></option>
                    <?php endif ?>
                    <option value="bulk_delete"><?php _e('Delete'); ?></option>
                </select>
                <?php if (!$this->url_status || $this->url_status == 1 ): ?>
                    <select name="trash_post_bulk_action" id="trash_post_bulk_action" style="display:none;">
                        <option value="1" selected="true" ><?php _e('Delete posts simultaneously', 'my-autopost'); ?></option>
                        <option value="0"><?php _e('Do not delete posts', 'my-autopost'); ?></option>
                    </select>
                <?php endif ?>
                <input type="button" name="" class="button action" value="<?php _e('Apply'); ?>"  onclick="bulkAction()"/>
            </div>

            <div class="alignleft actions">
                <select name="task_id" id="task_id">
                    <option value="0"><?php _e('View all tasks', 'my-autopost'); ?></option>
                    <?php foreach ($tasks as $task):  ?>
                    <option value="<?php echo $task->id; ?>" <?php selected($this->task_id == $task->id); ?> ><?php echo $task->name; ?></option>
                    <?php endforeach ?>
                </select>
                <select name="url_status" id="url_status">
                    <option value="0" <?php selected(!$this->url_status);  ?> ><?php _e('View all status', 'my-autopost'); ?></option>
                    <option value="1" <?php selected($this->url_status == 1); ?> ><?php _e('Published'); ?></option>
                    <option value="2" <?php selected($this->url_status == 2); ?> ><?php _e('Pending Extraction', 'my-autopost'); ?></option>
                    <option value="3" <?php selected($this->url_status == 3); ?> ><?php _e('Ignored', 'my-autopost'); ?></option>
                </select>
                <input value="<?php _e('Filter'); ?>" type="button" class="button" onclick="filterData()" />
            </div>

            <div class="alignright">
                <select name="rdays" id="rdays">
                    <option value="90"><?php _e('Retain the data of last 90 days', 'my-autopost'); ?></option>
                    <option value="60"><?php _e('Retain the data of last 60 days', 'my-autopost'); ?></option>
                    <option value="30"><?php _e('Retain the data of last 30 days', 'my-autopost'); ?></option>
                    <option value="0"><?php _e('Does not retain any data', 'my-autopost'); ?></option>
                </select>
                <select name="trash_post" id="trash_post">
                    <option value="0"><?php _e('Do not delete posts', 'my-autopost'); ?></option>
                    <option value="1"><?php _e('Delete posts simultaneously', 'my-autopost'); ?></option>
                </select>
                <input value="<?php _e('Delete'); ?>" class="button" type="button" onclick="emptyData()"/>
            </div>

            <script type="text/javascript">
                jQuery("#bulk_action").change(function() {
                    if (jQuery(this).val() == 'bulk_delete') {
                        jQuery("#trash_post_bulk_action").show();
                    }
                });
                function bulkAction() {
                    jQuery("#saction").val(jQuery("#bulk_action").val());
                    jQuery("#myform").submit();
                }
                function emptyData() {
                    if (jQuery("#rdays").val() == 0 && jQuery("#trash_post").val() == 0) {
                        if (confirm("<?php _e('This operation may cause duplicate publish, continue?','my-autopost'); ?>")) {
                            jQuery("#saction").val("empty_data");
                            jQuery("#myform").submit();
                        } else
                            return false;
                    } else if (jQuery("#trash_post").val() == 1) {
                        if (confirm("<?php echo __('Confirm Delete?', 'my-autopost'); ?>")) {
                            jQuery("#saction").val("empty_data");
                            jQuery("#myform").submit();
                        } else
                            return false;
                    } else {
                        jQuery("#saction").val("empty_data");
                        jQuery("#myform").submit();
                    }
                }
                function filterData() {
                    jQuery("#saction").val('filter_data');
                    jQuery("#myform").submit();
                }
            </script>
        <?php
        }
    }

    /**
     * 只在表格下侧显示分页列表
     *
     * @param string $which  top表示表格上方,bottom表示表格下方
     */
    protected function pagination($which)
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
        $this->_column_headers = $this->get_column_info();

        $per_page     = $this->get_items_per_page('posts_per_page', 30);
        $current_page = $this->get_pagenum();
        $this->items  = $this->get_posts($per_page, $current_page);
        $total_items  = $this->get_posts_count();

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ));
    }

}