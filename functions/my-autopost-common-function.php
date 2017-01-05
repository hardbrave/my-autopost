<?php

define('ERROR_OPEN_URL', 1);
define('ERROR_FIND_ARTICLE_URL', 2);
define('ERROR_FIND_ARTICLE_TITLE', 3);
define('ERROR_FIND_ARTICLE_CONTENTS', 4);
define('ERROR_SET_ARTICLE_SOURCE_URL', 5);
define('ERROR_SET_ARTICLE_URL_MATCHING_RULES', 6);
define('ERROR_SET_ARTICLE_TITLE_MATCHING_RULES', 7);
define('ERROR_SET_ARTICLE_CONTENT_MATCHING_RULES', 8);

$errors = array(
    ERROR_OPEN_URL  =>  __('Unable to open URL', 'my-autopost'),
    ERROR_FIND_ARTICLE_URL  =>  __('Did not find the article URL, Please check the [Article Source Settings => Article URL matching rules]', 'my-autopost'),
    ERROR_FIND_ARTICLE_TITLE    =>  __('Did not find the title of the article, Please check the [Article Extraction Settings => The Article Title Matching Rules]', 'my-autopost'),
    ERROR_FIND_ARTICLE_CONTENTS =>  __('Did not find the contents of the article, Please check the [Article Extraction Settings => The Article Content Matching Rules]', 'my-autopost'),
    ERROR_SET_ARTICLE_SOURCE_URL    =>  __('[Article Source URL] is not set yet', 'my-autopost'),
    ERROR_SET_ARTICLE_URL_MATCHING_RULES    =>  __('[The Article URL matching rules] is not set yet', 'my-autopost'),
    ERROR_SET_ARTICLE_TITLE_MATCHING_RULES  =>  __('[The Article Title Matching Rules] is not set yet', 'my-autopost'),
    ERROR_SET_ARTICLE_CONTENT_MATCHING_RULES    =>  __('[The Article Content Matching Rules] is not set yet', 'my-autopost'),
);

function my_autopost_invisibled($current, $echo = true)
{
    $r = '';
    if ($current) {
        $r = 'style="display:none;"';
    }
    if ($echo && $r) {
        echo $r;
    }
    return $r;
}

function my_autopost_value($condition, $value, $echo = true)
{
    $r = '';
    if ($condition && $value) {
        $r = 'value="' . $value . '"';
    }
    if ($echo && $r) {
        echo $r;
    }
    return $r;
}

function my_autopost_maktimes($time) {
    $now = current_time('timestamp');
    if ($now >= $time) {
        $t = $now - $time;
        $s = __(' ago', 'my-autopost'); }
    else {
        $t = $time - $now;
        $s = __(' after', 'my-autopost'); }
    if ($t == 0)
        $t = 1;
    $f = array(
        '31536000'=> __(' years', 'my-autopost'),
        '2592000' => __(' months', 'my-autopost'),
        '604800'  => __(' weeks', 'my-autopost'),
        '86400'   => __(' days', 'my-autopost'),
        '3600'    => __(' hours', 'my-autopost'),
        '60'      => __(' minutes', 'my-autopost'),
        '1'       => __(' seconds', 'my-autopost')
    );
    foreach ($f as $k => $v) {
        if (0 !=$c=floor($t/(int)$k)) {
            return $c . $v . $s;
        }
    }
}

/**
 * 及时输出任务执行过程中产生的信息
 *
 * @param string    $info   任务执行中产生的信息
 */
function my_autopost_print_info($info)
{
    echo $info;
    ob_flush();
    flush();
}

/**
 * 记录错误信息
 *
 * @param int        $task_id       任务id
 * @param string     $url           出错的url
 * @param int        $err_code      错误码
 * @param string     $task_name     任务名称
 * @param bool|true  $print         是否输出错误信息
 * @param bool|false $show_div      输出错误信息时是否显示div
 */
function my_autopost_log_err($task_id, $url, $err_code, $task_name, $print = true, $show_div = false)
{
    global $errors;
    if (isset($errors[$err_code])) {
        $info = $errors[$err_code];
        my_autopst_db_transaction_work('my_autopost_db_log_err', array(
            'task_id'   =>  $task_id,
            'info'      =>  $info,
            'url'       =>  $url
        ));
        if ($print) {
            $show_div && print '<div class="updated fade">';
            echo '<p>'.__('Task', 'my-autopost').': <b>'.$task_name.'</b><span class="red">'.__('an error occurs, please check the log information', 'my-autopost').'</span></p>';
            $show_div && print '</div>';
        }
    }
}

/**
 * 输出错误信息
 *
 * @param int        $err_code  错误码
 * @param string     $url       出错的url
 * @param bool|false $show_div  输出错误信息时是否显示div
 */
function my_autopost_print_err($err_code, $url = '', $show_div = false)
{
    global $errors;
    if (isset($errors[$err_code])) {
        $show_div && print '<div class="updated fade">';
        echo "<p><span class='red'><b>$errors[$err_code]</b></span>(<code>$url</code>)</span></p>";
        $show_div && print '</div>';
    }
}