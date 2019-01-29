<?php

if ($_GET['activate']) {
    my_autopost_do_activate_task($_GET['activate']);
    exit;
}

if ($_GET['deactivate']) {
    my_autopost_do_deactivate_task($_GET['deactivate']);
    exit;
}




$saction = $_POST['saction'];
switch ($saction) {
    case 'do_add':
        my_autopost_do_add_task($_POST['task_name']);
        exit;
    case 'do_delete':
        my_autopost_do_delete_task($_POST['task_id']);
        exit;
    case 'edit_submit':
        my_autopost_edit_submit($_POST['task_id']);
        exit;
    case 'save_url_list':
        my_autopost_save_url_list($_POST['task_id']);
        exit;
    case 'test_url_list':
        my_autopost_save_url_list($_POST['task_id'], true);
        exit;
    case 'save_crawl':
        my_autopost_save_craw($_POST['task_id']);
        exit;
    case 'test_crawl':
        my_autopost_save_craw($_POST['task_id'], true);
        exit;
    default:
        break;
}

$saction = $_GET['saction'];
switch ($saction) {
    case 'new':
        my_autopost_show_add_task();
        exit;
    case 'delete':
        my_autopost_show_delete_task($_GET['task_id']);
        exit;
    case 'edit':
        my_autopost_show_task_setting($_GET['task_id']);
        exit;
    case 'test_fetch':
        my_autopost_show_task_setting($_GET['task_id'], false, false, false, '', '', true, false, false);
        exit;
    case 'fetch':
        my_autopost_show_task_list('', '', $_GET['task_id']);
        exit;
    case 'ignore':
        my_autopost_ignore($_GET['task_id']);
        exit;
    case 'abort':
        my_autopost_abort($_GET['task_id']);
        exit;
    default:
        my_autopost_show_task_list();
        break;
}

/**
 * 终止任务的运行
 *
 * @param int   $task_id    任务id
 */
function my_autopost_abort($task_id)
{
    global $wpdb, $t_map_config;
    $wpdb->query('UPDATE '.$t_map_config.' SET is_running = 0 WHERE id = '.$task_id);
    my_autopost_show_task_list();
}

/**
 * 忽略任务执行过程中发生的错误
 *
 * @param int   $task_id    任务id
 */
function my_autopost_ignore($task_id)
{
    global $wpdb, $t_map_config;
    $wpdb->query('UPDATE '.$t_map_config.' SET last_error = 0 WHERE id = '.$task_id);
    my_autopost_show_task_list();
}

/**
 * 显示新增任务界面
 */
function my_autopost_show_add_task()
{
    include_once(MAP_PLUGIN_DIR . '/views/view-add-task.php');
}

/**
 * 新增一个任务
 *
 * @param string $task_name 任务名称
 */
function my_autopost_do_add_task($task_name)
{
    global $wpdb, $t_map_config;
    if ($task_name) {
        if ($wpdb->query("insert into $t_map_config(name) values ('$task_name')")) {
            $suc_msg = __('A new task has been created.', 'my-autopost');
        } else {
            $err_msg = __('Create task failed', 'my-autopost');
        }
    }
    my_autopost_show_task_list($suc_msg, $err_msg);
}

/**
 * 显示删除任务界面
 *
 * @param int $task_id  任务ID
 */
function my_autopost_show_delete_task($task_id)
{
    global $wpdb, $t_map_config;
    $task_config = $wpdb->get_row('SELECT name FROM ' . $t_map_config . ' WHERE id = ' . $task_id);
    $task_name = $task_config->name;
    include_once(MAP_PLUGIN_DIR . '/views/view-delete-task.php');
}

/**
 * 删除一个任务
 *
 * @param int $task_id  任务ID
 */
function my_autopost_do_delete_task($task_id)
{
    global $wpdb, $t_map_config;
    if ($task_id) {
        if ($wpdb->query('DELETE FROM ' . $t_map_config . ' WHERE id = ' . $task_id)) {
            $suc_msg = __('A new task has been deleted.', 'my-autopost');
        } else {
            $err_msg = __('Delete task failed', 'my-autopost');
        }
    }
    my_autopost_show_task_list($suc_msg, $err_msg);
}

/**
 * 激活一个任务
 *
 * @param int $task_id  任务ID
 */
function my_autopost_do_activate_task($task_id)
{
    global $wpdb, $t_map_config;
    if ($task_id) {
        if ($wpdb->query('UPDATE '.$t_map_config.' SET activation = 1, last_update_time = '.current_time('timestamp').' WHERE id = ' . $task_id)) {
            $suc_msg = __('Task activated.', 'my-autopost');
        } else {
            $err_msg = __('Task activated failed.', 'my-autopost');
        }
    }
    my_autopost_show_task_list($suc_msg, $err_msg);
}

/**
 * 停用一个任务
 *
 * @param int $task_id  任务ID
 */
function my_autopost_do_deactivate_task($task_id)
{
    global $wpdb, $t_map_config;
    if ($task_id) {
        if ($wpdb->query('UPDATE '.$t_map_config.' SET activation = 0, last_update_time = '.current_time('timestamp').' WHERE id = ' . $task_id)) {
            $suc_msg = __('Task deactivated.', 'my-autopost');
        } else {
            $err_msg = __('Task deactivated failed.', 'my-autopost');
        }
    }
    my_autopost_show_task_list($suc_msg, $err_msg);
}

/**
 * 显示任务列表界面
 *
 * @param string    $suc_msg        成功状态消息
 * @param string    $err_msg        错误状态消息
 * @param int       $fetch_task_id  抓取任务id
 */
function my_autopost_show_task_list($suc_msg = '', $err_msg = '', $fetch_task_id = 0)
{
    require_once( MAP_PLUGIN_DIR . '/classes/class-my-autopost-task-list-table.php');
    $task_list_table = new My_Autopost_Task_List_Table();
    include_once(MAP_PLUGIN_DIR . '/views/view-task-list.php');
}

/**
 * 保存/测试文章抓取设置
 *
 * @param int        $task_id   任务ID
 * @param bool|false $test      是否测试文章抓取设置
 */
function my_autopost_save_craw($task_id, $test = false)
{
    global $wpdb, $t_map_config;
    $title_match_type = $_POST['title_match_type'];
    if ($title_match_type == 0)
        $title_selector = stripslashes(trim($_POST['title_selector_0']));
    elseif ($title_match_type == 1)
        $title_selector = stripslashes(trim($_POST['title_selector_1']));

    $content_match_type = array();
    $content_selector = array();

    if ($_POST['outer_0'] == 'on')
        $outer = 1;
    else
        $outer = 0;
    $objective = 0;

    if ($_POST['content_match_type_0'] == 0) {
        $content_match_type[] = implode(',', array($_POST['content_match_type_0'], $outer, $objective, $_POST['index_0']));
        $content_selector[] = stripslashes(trim($_POST['content_selector_0_0']));
    } elseif ($_POST['content_match_type_0'] == 1) {
        $content_match_type[] = implode(',', array($_POST['content_match_type_0'], $outer, $objective, $_POST['index_0']));
        $content_selector[] = stripslashes(trim($_POST['content_selector_1_0']));
    }

    if ($_POST['cmr_num'] >= 1) {
        for ($i = 1; $i <= $_POST['cmr_num']; $i++) {
            if ($_POST['outer_'.$i] == 'on')
                $outer = 1;
            else $outer = 0;
            $objective = $_POST['objective_'.$i];

            if ($_POST['content_match_type_'.$i] == 0 && trim($_POST['content_selector_0_'.$i])) {
                $content_match_type[] = implode(',', array($_POST['content_match_type_'.$i], $outer, $objective, $_POST['index_'.$i]));
                $content_selector[] = stripslashes(trim($_POST['content_selector_0_'.$i]));

            } elseif ($_POST['content_match_type_'.$i] == 1 && trim($_POST['content_selector_1_'.$i])) {
                $content_match_type[] = implode(',', array($_POST['content_match_type_'.$i], $outer, $objective, $_POST['index_'.$i]));
                $content_selector[] = stripslashes(trim($_POST['content_selector_1_'.$i]));
            }

        }
    }

    if ($wpdb->query($wpdb->prepare("UPDATE $t_map_config SET
          title_match_type = %d,
          title_selector = %s,
          content_match_type = %s,
          content_selector = %s
          WHERE id = %d",
            $title_match_type,
            $title_selector,
            json_encode($content_match_type),
            json_encode($content_selector),
            $task_id)) === false) {
        $err_msg = __('Updated failed!', 'my-autopost');
    } else {
        $suc_msg = __('Updated!', 'my-autopost');
    };

    if($test) {
        my_autopost_show_task_setting($task_id, false, false, true, '', '', false, false, true, $_POST['test_url']);
    } else {
        my_autopost_show_task_setting($task_id, false, false, true, $suc_msg, $err_msg);
    }
}

/**
 * 保存/测试文章列表设置
 *
 * @param int        $task_id   任务ID
 * @param bool|false $test      是否测试文章列表设置
 */
function my_autopost_save_url_list($task_id, $test = false)
{
    global $wpdb, $t_map_config, $t_map_config_url_list;
    if ($_POST['a_match_type'] == 0)
        $a_selector = trim($_POST['a_selector_0']);
    elseif ($_POST['a_match_type'] == 1)
        $a_selector = trim($_POST['a_selector_1']);
    if ($_POST['reverse_sort'] == 'on')
        $reverse_sort = 1;
    else
        $reverse_sort = 0;
    if ($wpdb->query('update '.$t_map_config.' set
               a_match_type = '.$_POST['a_match_type'].',
			   a_selector = "'.$a_selector.'",
			   source_type = '.$_POST['source_type'].',
			   reverse_sort = '.$reverse_sort.',
			   start_num = '.$_POST['start_num'].',
			   end_num =  '.$_POST['end_num'].' WHERE id = '.$task_id) === false) {
        $err_msg = __('Updated failed!','wp-autopost');
    } else {
        $suc_msg = __('Updated!','wp-autopost');
    };

    if ($_POST['source_type'] == 0) {
        $wpdb->query("DELETE FROM $t_map_config_url_list WHERE config_id = $task_id");
        $urls = explode("\n", $_POST['urls']);
        foreach ($urls as $url) {
            $url = trim($url);
            if ($url)
                $wpdb->query("INSERT INTO $t_map_config_url_list(config_id, url) VALUES ($task_id, '$url')");
        }
    }

    if ($_POST['source_type'] == 1) {
        $wpdb->query("DELETE FROM $t_map_config_url_list WHERE config_id = $task_id");
        $url = trim($_POST['url']);
        if ($url)
            $wpdb->query("INSERT INTO $t_map_config_url_list(config_id, url) VALUES ($task_id, '$url')");
    }

    if ($test) {
        my_autopost_show_task_setting($task_id, false, true, false, '', '', false, true, false);
    } else {
        $suc_msg = __('Updated!', 'my-autopost');
        my_autopost_show_task_setting($task_id, false, true, false, $suc_msg, $err_msg);
    }

}

/**
 * 显示文章目录树
 *
 * @param string $post_type         文章类型
 * @param array  $selected_cats     被选中的目录
 */
function my_autopost_show_category($post_type, $selected_cats)
{
    include_once(MAP_PLUGIN_DIR . '/classes/class-my-autopost-walker-category.php');
    $walker_category = new My_Autopost_Walker_Category();
    $taxonomy_names = get_object_taxonomies($post_type, 'objects');
    foreach ($taxonomy_names as $taxonomy) {
        if ($taxonomy->name=='post_tag' || $taxonomy->name =='post_format')
            continue;
        $args = array(
            'descendants_and_self'  => 0,
            'selected_cats'         => $selected_cats,
            'popular_cats'          => false,
            'walker'                => $walker_category,
            'taxonomy'              => $taxonomy->name,
            'checked_ontop'         => false,
        );
        echo '<ul id="categorychecklist" class="list:category categorychecklist form-no-clear">';
        echo '<strong>'.$taxonomy->label.'</strong>';
        wp_terms_checklist( 0, $args );
        echo '</ul>';
    }
}

/**
 * 编辑任务基本信息并提交
 *
 * @param int $task_id  任务ID
 */
function my_autopost_edit_submit($task_id)
{
    global $wpdb, $t_map_config;

    $sub_saction = $_POST['sub_saction'];

    if ($sub_saction == 'change_post_type') {
        if ($wpdb->query($wpdb->prepare("UPDATE $t_map_config SET post_type = %s WHERE id = %d", $_POST['post_type'], $task_id)) === false) {
            $err_msg = __('Update task failed.', 'my-autopost');
        } else {
            $suc_msg = __('A new task has been updated.', 'my-autopost');
        }
    } else {
        if ($_POST['post_type'] != 'page' && $_POST['post_category']) {
            $cat = implode(',', $_POST['post_category']);
        }

        $charset = $_POST['charset'];
        if (!$charset)
            $page_charset = 'UTF-8';
        else
            $page_charset = $_POST['page_charset'];
        if (!trim($page_charset))
            $page_charset = 'UTF-8';

        $auto_sets = array();
        $auto_sets[0] = 0;
        $auto_sets[1] = 0;
        $auto_sets[2] = intval($_POST['publish_status']);

        $proxy = array();
        if ($_POST['use_proxy']) {
            $proxy_options = get_option('my-autopost-proxy');
            if (!$proxy_options['ip']) {
                $set_proxy_options = __('Use proxy please set Proxy Options first!', 'my-autopost');
                $err_msg = '<a href="admin.php?page=my-autopost/my-autopost-proxy.php">' . $set_proxy_options . '</a></p>';
                $proxy[0] = 0;
            }else{
                $proxy[0] = 1;
            }
        } else {
            $proxy[0] = 0;
        }
        $proxy[1] = intval($_POST['hide_ip']);

        $post_scheduled = array();
        $post_scheduled[0] = $_POST['post_scheduled'];
        $post_scheduled[1] = intval($_POST['post_scheduled_hour']);
        if ($post_scheduled[1] < 0)
            $post_scheduled[1] = 0;
        if ($post_scheduled[1] > 23)
            $post_scheduled[1] = 23;
        $post_scheduled[2] = intval($_POST['post_scheduled_minute']);
        if ($post_scheduled[2] < 0)
            $post_scheduled[1] = 0;
        if ($post_scheduled[2] > 59)
            $post_scheduled[1] = 59;

        if ($wpdb->query($wpdb->prepare("update $t_map_config set
               m_extract = %d,
			   name = %s,
			   page_charset = %s,
			   cat = %s,
			   author = %d,
			   update_interval = %d,
			   published_interval = %d,
               post_scheduled = %s,
			   auto_tags = %s,
			   proxy = %s,
			   post_format = %s,
			   check_duplicate = %d,
               err_status = %d
			   WHERE id = %d",
            $_POST['manually_extraction'],
            $_POST['task_name'],
            $page_charset,
            $cat,
            $_POST['author'],
            $_POST['update_interval'],
            $_POST['published_interval'],
            json_encode($post_scheduled),
            json_encode($auto_sets),
            json_encode($proxy),
            $_POST['post_format'],
            $_POST['check_duplicate'],
            $_POST['err_status'], $task_id)
        ) === false) {
            $err_msg = __('Update task failed.', 'my-autopost');
        } else {
            $suc_msg = __('A new task has been updated.', 'my-autopost');
        }
    }

    my_autopost_show_task_setting($task_id, true, false, false, $suc_msg, $err_msg);
}

/**
 * 显示任务设置界面
 *
 * @param int        $task_id               任务ID
 * @param bool|false $show_basic_box        是否展开基本设置框
 * @param bool|false $show_source_box       是否展开文章来源框
 * @param bool|false $show_crawl_box        是否展开文章抓取框
 * @param string     $suc_msg               成功状态信息
 * @param string     $err_msg               错误状态信息
 * @param bool|false $show_test_fetch       是否测试整体抓取
 * @param bool|false $show_test_url_list    是否测试抓取文章列表
 * @param bool|false $show_test_crawl       是否测试抓取文章内容
 */
function my_autopost_show_task_setting($task_id, $show_basic_box = false, $show_source_box = false, $show_crawl_box = false,
                           $suc_msg = '', $err_msg = '', $show_test_fetch = false, $show_test_url_list = false, $show_test_crawl = false, $test_url = '')
{
    global $wpdb, $t_map_config, $t_map_config_url_list;

    $task_config = my_autopost_db_get_task($task_id);
    $custom_post_types = get_post_types(array('_builtin' => false), 'objects');
    $post_formats = get_theme_support('post-formats');
    if (is_array($post_formats[0])) {
        $format_name = get_post_format_strings();
    }
    $users = $wpdb->get_results("SELECT $wpdb->users.ID, $wpdb->users.display_name FROM $wpdb->users", OBJECT);
    $post_scheduled = json_decode($task_config->post_scheduled);
    if (!$post_scheduled) {
        $post_scheduled = array(0, 12, 0);
    }
    $urls = my_autopost_db_get_list_urls($task_id);
    $content_selector = json_decode($task_config->content_selector);
    $content_match_types = json_decode($task_config->content_match_type);
    if (!$content_match_types) {
        list($content_match_type[], $outer[], $objective[], $index[]) = array($task_config->content_match_type, 0, 0, 0);
    } else {
        $content_match_type = array();
        $outer = array();
        $objective = array();
        $index = array();
        foreach ($content_match_types as $cmts) {
            list($content_match_type[], $outer[], $objective[], $index[]) = explode(',', $cmts);
        }
    }

    $proxy = json_decode($task_config->proxy);

    include_once(MAP_PLUGIN_DIR . '/views/view-task-setting.php');
}
