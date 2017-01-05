<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class My_Autopost_Autolink_List_Table extends WP_List_Table
{
    /**
     * 获取自动链接列表
     *
     * @param int $per_page         每页显示多少条自动链接,默认显示20条
     * @param int $page_number      显示第几页的自动链接,默认显示第一页
     * @return array                二维数组,返回自动链接列表
     */
    public function get_autolinks($per_page = 20, $page_number = 1)
    {
        global $wpdb, $t_map_autolink;

        $sql = "SELECT * FROM $t_map_autolink";
        $sql .= " ORDER BY id DESC";
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        return $wpdb->get_results($sql);
    }

    /**
     * 获取自动链接条数
     *
     * @return int      返回满足条件的自动链接条数
     */
    public function get_autolinks_count()
    {
        global $wpdb, $t_map_autolink;

        $sql = "SELECT count(*) FROM $t_map_autolink";
        return $wpdb->get_var($sql);
    }

    /**
     * 没有项目时显示的内容
     */
    public function no_items()
    {
        _e('No autolink found.', 'my_autopost');
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
            'keyword'   =>  __('Keyword', 'wp-autopost'),
            'link'      =>  __('Link', 'wp-autopost'),
            'desc'      =>  __('Description', 'wp-autopost'),
            'attr'      =>  __('Attributes', 'wp-autopost'),
            'op'        =>  ''
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
     * 返回关键词列格式
     *
     * @param object $item  行数据
     * @return string       关键词列格式字符串
     */
    public function column_keyword($item)
    {
        $current_page = $this->get_pagenum();
        $keyword = "<a href='admin.php?page=my-autopost/my-autopost-autolinks.php&saction=edit&id=$item->id&paged=$current_page'>$item->keyword</a>";
        return $keyword;
    }

    /**
     * 返回关键词链接列格式
     *
     * @param object $item  行数据
     * @return string       关键词链接列格式字符串
     */
    public function column_link($item)
    {
        $details = explode('|', $item->details);
        $link = "<a href='$details[0]'>$details[0]</a>";
        return $link;
    }

    /**
     * 返回关键词链接列格式
     *
     * @param object $item  行数据
     * @return string       关键词链接列格式字符串
     */
    public function column_desc($item)
    {
        $details = explode('|', $item->details);
        return $details[1];
    }

    /**
     * 返回关键词链接列格式
     *
     * @param object $item  行数据
     * @return string       关键词链接列格式字符串
     */
    public function column_attr($item)
    {
        $details = explode('|', $item->details);
        $attr = '';
        if ($details[2]) {
            $attr .= '[<code>' . __('No Follow', 'my-autopost') . '</code>]';
        }
        if ($details[3]) {
            $attr .= '[<code>' . __('New Window','my-autopost') . '</code>]';
        }
        if ($details[4]) {
            $attr .= '[<code>' . __('First Match Only', 'my-autopost') . '</code>]';
        }
        if ($details[5]) {
            $attr .= '[<code>' . __('Ignore Case', 'my-autopost') . '</code>]';
        }
        if ($details[6]) {
            $attr .= '[<code>' . __('Match Whole Word', 'my-autopost') . '</code>]';
        }
        return $attr;
    }

    /**
     * 返回操作列格式
     *
     * @param object $item  行数据
     * @return string       操作列格式字符串
     */
    public function column_op($item)
    {
        $delete = __('Delete');
        $current_page = $this->get_pagenum();
        $op = "<a href='javascript:' onclick='Delete($item->id)'>$delete</a>";
        return $op;
    }

    /**
     * 显示表格
     */
    public function display()
    {
        ?>
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
                <td colspan="6"><input type="button" class="button-primary" value="<?php _e('Batch Delete', 'my_autopost'); ?>" onclick="BulkDelete()"></td>
            </tr>
            </tfoot>
        </table>
        <script type="text/javascript">
            function BulkDelete() {
                jQuery("#saction").val("bulk_delete");
                jQuery("#myform").submit();
            }
            function Delete(id) {
                jQuery("#id").val(id);
                jQuery("#saction").val("delete");
                jQuery("#myform").submit();
            }
        </script>
        <?php
        $this->display_tablenav('bottom');
    }

    /**
     * 为表格显示准备数据
     */
    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();

        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $this->items  = $this->get_autolinks($per_page, $current_page);
        $total_items  = $this->get_autolinks_count();

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ));
    }
}