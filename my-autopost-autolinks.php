<?php

require_once(MAP_PLUGIN_DIR . '/classes/class-my-autopost-autolink-list-table.php');
$autolink_list_table = new My_Autopost_Autolink_List_Table();

$saction = $_POST['saction'];
switch ($saction) {
    case 'bulk_delete':
        my_autopost_bulk_delete_auotlink($_POST['ids']);
        exit;
    case 'delete':
        my_autopost_bulk_delete_auotlink(array($_POST['id']));
        exit;
    case 'do_add':
        $details = implode('|', array($_POST['link'], $_POST['desc'], $_POST['no_follow'],
            $_POST['new_window'], $_POST['first_match_only'], $_POST['ignore_case'], $_POST['whole_word']));
        my_autopost_do_add_autolink($_POST['keyword'], $details);
        exit;
    case 'do_edit':
        $details = implode('|', array($_POST['link'], $_POST['desc'], $_POST['no_follow'],
            $_POST['new_window'], $_POST['first_match_only'], $_POST['ignore_case'], $_POST['whole_word']));
        my_autopost_do_edit_autolink($_POST['id'], $_POST['keyword'], $details);
        exit;
}

$saction = $_GET['saction'];
switch ($saction) {
    case 'new':
        my_autopost_show_add_autolink();
        exit;
    case 'edit':
        my_autopost_show_edit_autolink($_GET['id']);
        exit;
    case 'auto_link':
        my_autopost_autolink_old_posts();
        exit;
    default:
        my_autopost_show_autolink_list();
        break;
}

/**
 * 对已有文章进行关键字自动链接
 */
function my_autopost_autolink_old_posts()
{
    global $wpdb, $t_map_autolink;
    $autolinks = $wpdb->get_results('SELECT * FROM '.$t_map_autolink);

    $n = $_GET['n'];
    $page_num = 20;
    $objects = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_content FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' ORDER BY ID DESC LIMIT %d, %d", $n,$page_num));

    include_once(MAP_PLUGIN_DIR . '/views/view-autolink-old-posts.php');
}

/**
 * 批量删除自动链接关键字
 *
 * @param array $ids    自动链接关键字数组
 */
function my_autopost_bulk_delete_auotlink($ids)
{
    global $wpdb, $t_map_autolink;
    if ($ids) {
        $ids = implode(',', $ids);
        echo $ids;
        $wpdb->query("DELETE FROM $t_map_autolink WHERE id in ($ids)");
        if ($wpdb->last_error) {
            $err_msg = __('Delete failed.', 'my_autopost');
        } else {
            $suc_msg = __('Delete successed.', 'my_autopost');
        }
    }

    my_autopost_show_autolink_list($suc_msg, $err_msg);
}

/**
 * 显示添加自动链接关键字页面
 */
function my_autopost_show_add_autolink()
{
    my_autopost_show_modify_autolink();
}

/**
 * 显示编辑自动链接关键字页面
 *
 * @param int   $id 自动链接关键字id
 */
function my_autopost_show_edit_autolink($id)
{
    global $wpdb, $t_map_autolink;
    $autolink = $wpdb->get_row("SELECT * FROM $t_map_autolink WHERE id = $id");
    list($link, $desc, $no_follow, $new_window, $first_match_only, $ignore_case) = explode('|', $autolink->details);
    my_autopost_show_modify_autolink($autolink->id, $autolink->keyword, $link, $desc, $no_follow, $new_window, $first_match_only, $ignore_case);
}

/**
 * 显示新增/编辑自动链接关键字页面
 *
 * @param int    $id                自动链接关键字id
 * @param string $keyword           关键字
 * @param string $link              链接
 * @param string $desc              描述
 * @param int    $no_follow         no follow
 * @param int    $new_window        新开窗口
 * @param int    $first_match_only  仅自动链接第一个匹配的
 * @param int    $ignore_case       忽略大小写
 */
function my_autopost_show_modify_autolink($id = 0, $keyword = '', $link = '', $desc = '', $no_follow = 0, $new_window = 0, $first_match_only = 0, $ignore_case = 0)
{
    include_once(MAP_PLUGIN_DIR . '/views/view-modify-autolink.php');
}

/**
 * 新增自动链接关键字
 *
 * @param string    $keyword    关键字
 * @param string    $details    详细信息
 */
function my_autopost_do_add_autolink($keyword, $details)
{
    global $wpdb, $t_map_autolink;
    $wpdb->query($wpdb->prepare("INSERT INTO $t_map_autolink(keyword, details) VALUES (%s, %s)", $keyword, $details));
    if ($wpdb->last_error) {
        $err_msg = __('Add failed.', 'my_autopost');
    } else {
        $suc_msg = __('Add successed.', 'my_autopost');
    }
    my_autopost_show_autolink_list($suc_msg, $err_msg);
}

/**
 * 编辑自动链接关键字
 *
 * @param int       $id         自动链接关键字id
 * @param string    $keyword    关键字
 * @param string    $details    详细信息
 */
function my_autopost_do_edit_autolink($id, $keyword, $details)
{
    global $wpdb, $t_map_autolink;
    $wpdb->query($wpdb->prepare("UPDATE $t_map_autolink SET keyword = %s, details = %s WHERE id = %d", $keyword, $details, $id));
    if ($wpdb->last_error) {
        $err_msg = __('Update failed.', 'my_autopost');
    } else {
        $suc_msg = __('Update successed.', 'my_autopost');
    }
    my_autopost_show_autolink_list($suc_msg, $err_msg);
}

/**
 * 显示自动链接关键字列表
 *
 * @param string $suc_msg   成功信息
 * @param string $err_msg   错误信息
 */
function my_autopost_show_autolink_list($suc_msg = '', $err_msg = '')
{
    include_once(MAP_PLUGIN_DIR . '/views/view-autolink-list.php');
}