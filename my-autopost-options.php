<?php

if ($_POST['submit_update']) {
    update_option('my_autopost_update_method', $_POST['update_method']);
    $t = $_POST['time_limit'];
    if (!is_numeric($t) || $t < 0)
        $t = 0;
    update_option('my_autopost_time_limit', $t);
    update_option('my_autopost_run_only_one_task', $_POST['run_only_one_task']);

    $msg = __('Updated!', 'my-autopost');
} elseif ($_POST['submit_remote_img']) {
    $w = $_POST['img_min_width'];
    if (!is_numeric($w) || $w < 0)
        $w = 0;
    update_option('my_autopost_down_img_min_width', $w);

    $msg = __('Updated!', 'my-autopost');
} elseif ($_POST['submit_other']) {
    update_option('my_autopost_del_comment', $_POST['del_comment']);
    update_option('my_autopost_del_attr_id', $_POST['del_attr_id']);
    update_option('my_autopost_del_attr_class', $_POST['del_attr_class']);
    update_option('my_autopost_del_attr_style', $_POST['del_attr_style']);

    $msg = __('Updated!', 'my-autopost');
} elseif ($_POST['submit_remote_attach']) {
    $download_types = explode("\r\n", stripslashes($_POST['download_types']));
    $my_autopost_download_types = array();
    foreach ($download_types as $download_type) {
        if (trim($download_type))
            $my_autopost_download_types[] = $download_type;
    }
    update_option('my_autopost_download_types', json_encode($my_autopost_download_types));

    $msg = __('Updated!', 'my-autopost');
}

$update_method = get_option('my_autopost_update_method');
$run_only_one_task = get_option('my_autopost_run_only_one_task');
$time_limit = get_option('my_autopost_time_limit');
$update_post_url = get_bloginfo('url') . '?update_autopost=1';
$del_comment = get_option('my_autopost_del_comment');
$del_attr_id = get_option('my_autopost_del_attr_id');
$del_attr_class = get_option('my_autopost_del_attr_class');
$del_attr_style = get_option('my_autopost_del_attr_style');
$download_types = json_decode(get_option('my_autopost_download_types'));

$down_img_min_width = get_option('my_autopost_down_img_min_width');

include_once(MAP_PLUGIN_DIR . '/views/view-options.php');