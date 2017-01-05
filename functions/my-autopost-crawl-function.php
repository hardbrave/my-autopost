<?php

define('LIST_URL_NUM', 2);
define('FETCH_URL_NUM', 1);

/**
 * 测试文章来源抓取
 *
 * @param int   $task_id    任务id
 */
function my_autopost_test_url_list($task_id)
{
    $task_config = my_autopost_db_get_task($task_id);
    $list_urls = my_autopost_db_get_list_urls($task_id);
    if (!$list_urls) {
        my_autopost_print_err(ERROR_SET_ARTICLE_SOURCE_URL, '', true);
        return;
    }
    if (!trim($task_config->a_selector)) {
        my_autopost_print_err(ERROR_SET_ARTICLE_URL_MATCHING_RULES, '', true);
        return;
    }
    echo '<div class="updated fade"><p><b>' . __('Post articles in the following order', 'my-autopost') . '</b></p>';
    my_autopost_print_article_list_urls($task_config, $list_urls);
    echo '</div>';
}

/**
 * 测试文章内容抓取
 *
 * @param int       $task_id    任务id
 * @param string    $url        文章url
 */
function my_autopost_test_crawl($task_id, $url)
{
    echo '<div class="updated fade">';
    $task_config = my_autopost_db_get_task($task_id);
    $article = my_autopost_get_article($url, $task_config);
    if (!is_array($article)) {
        $error_code = $article;
        my_autopost_print_err($error_code, $url);
    } else {
        my_autopost_print_article($article);
    }
    echo '</div>';
}

/**
 * 测试文章抓取(文章来源+文章网址)
 *
 * @param int $task_id  任务id
 */
function my_autopost_test_fetch($task_id)
{
    $task_config = my_autopost_db_get_task($task_id);
    $list_urls = my_autopost_db_get_list_urls($task_id);
    if (!$list_urls) {
        my_autopost_print_err(ERROR_SET_ARTICLE_SOURCE_URL, '', true);
        return;
    }
    if (!trim($task_config->a_selector)) {
        my_autopost_print_err(ERROR_SET_ARTICLE_URL_MATCHING_RULES, '', true);
        return;
    }
    if (!trim($task_config->title_selector)) {
        my_autopost_print_err(ERROR_SET_ARTICLE_TITLE_MATCHING_RULES, '', true);
        return;
    }
    if (!trim($task_config->content_selector)) {
        my_autopost_print_err(ERROR_SET_ARTICLE_CONTENT_MATCHING_RULES, '', true);
        return;
    }

    echo '<div class="updated fade">';
    echo '<p><b>'.__('Post articles in the following order', 'my-autopost').'</b></p>';

    $urls = my_autopost_print_article_list_urls($task_config, $list_urls);
    if ($urls) {
        echo '<br/><h3>'.__('Article Crawl', 'my-autopost').'</h3>';
        $url_nums = 0;
        foreach ($urls as $url) {
            if ($url_nums++ >= FETCH_URL_NUM) {
                echo '.......<br/><p><code><b>'.__('In test only try to open', 'my-autopost').FETCH_URL_NUM.__('URLs of Article', 'my-autopost').'</b></code></p>';
                break;
            }
            echo '<p>'.__('URL : ', 'my-autopost').'<code><b>'.$url.'</b></code></p>';
            $article = my_autopost_get_article($url, $task_config);
            if (!is_array($article)) {
                $error_code = $article;
                my_autopost_print_err($error_code, $url);
            } else {
                my_autopost_print_article($article);
            }
        }
    }
    echo '</div>';
}

/**
 * 文章抓取
 *
 * @param int $task_id  任务id
 * @param int $print    是否输出相关信息
 * @param int $ignore   是否在客户端断开连接时继续执行脚本
 */
function my_autopost_fetch($task_id, $print = 1, $ignore = 1)
{
    /* 如果任务已经在执行,则直接退出 */
    if (my_autopost_db_task_is_running($task_id) == 1) {
        return;
    }

    /* 任务开始执行 */
    my_autopost_db_update_task_running_status($task_id, 1);

    /* 在客户端断开连接时继续执行脚本 */
    if ($ignore == 1) {
        ignore_user_abort(true);
        set_time_limit((int)get_option('my_autopost_time_limit'));
        if ($print) {
            my_autopost_print_info('<div class="updated fade"><p><b>' . __('Being processed, the processing may take some time, you can close the page', 'my-autopost') . '</b></p></div>');
        }
    }

    /* 错误处理 */
    $list_urls = my_autopost_db_get_list_urls($task_id);
    $task_config = my_autopost_db_get_task($task_id);
    if (!$list_urls) {
        my_autopost_log_err($task_id, '', ERROR_SET_ARTICLE_SOURCE_URL, $task_config->name, $print, true);
        return;
    }
    if (!trim($task_config->a_selector)) {
        my_autopost_log_err($task_id, '', ERROR_SET_ARTICLE_URL_MATCHING_RULES, $task_config->name, $print, true);
        return;
    }
    if (!trim($task_config->title_selector)) {
        my_autopost_log_err($task_id, '', ERROR_SET_ARTICLE_TITLE_MATCHING_RULES, $task_config->name, $print, true);
        return;
    }
    if (!trim($task_config->content_selector)) {
        my_autopost_log_err($task_id, '', ERROR_SET_ARTICLE_CONTENT_MATCHING_RULES, $task_config->name, $print, true);
        return;
    }

    if ($print) {
        echo '<div class="updated fade">';
        my_autopost_print_info('<p>'.__('Task', 'my-autopost').': <b>'.$task_config->name.'</b></p>');
    }

    /* 获取要抓取的文章网址 */
    $crawl_urls = my_autopost_get_article_list_urls($task_config, $list_urls);
    $urls = array();
    foreach ($crawl_urls as $crawl_url) {
        if ($print) {
            my_autopost_print_info('<p>'.__('Crawl URL : ', 'my-autopost').$crawl_url.'</p>');
        }

        $article_urls = my_autopost_get_article_urls($task_config, $crawl_url);
        if (!is_array($article_urls)) {
            $error_code = $article_urls;
            my_autopost_log_err($task_id, $crawl_url, $error_code, $task_config->name, $print);
            continue;
        }

        $urls = array_merge($urls, $article_urls);
    }

    /* 文章网址去重 */
    $urls = array_unique($urls);
    $article_urls = array();
    foreach ($urls as $url) {
        if (!my_autopost_db_check_url($task_id, $url)) {
            $article_urls[] = $url;
        }
    }

    /* 手动抓取 */
    if ($article_urls && $task_config->m_extract == 1) {
        $pre_post_num = my_autopost_pre_fetch($task_id, $article_urls, $task_config, $print);
        /* 输出文章抓取结果 */
        if ($print) {
            if ($pre_post_num > 0) {
                echo '<p>'.__('Task', 'my-autopost').': <b>'.$task_config->name.'</b>'.__('found', 'min_height').' <b>'.$pre_post_num.'</b> '.__('articles', 'my-autopost').'</p>';
            } else {
                echo '<p>'.__('Task', 'my-autopost').': <b>'.$task_config->name.'</b>'.__('does not detect a new article', 'my-autopost').'</p>';
            }
            echo '</div>';
        }
        /* 任务执行完毕 */
        my_autopost_db_update_task_running_status($task_id, 0);
        return;
    }

    /* 自动抓取 */
    if ($article_urls && $task_config->m_extract == 0) {
        $post_num = my_autopost_fetch_and_post($task_id, $article_urls, $task_config, $print);
        /* 输出文章抓取结果 */
        if ($print) {
            if ($post_num > 0) {
                echo '<p>'.__('Task', 'my-autopost').': <b>'.$task_config->name.'</b>'.__('updated', 'my-autopost').' <b>'.$post_num.'</b> '.__('articles', 'my-autopost').'</p>';
            } else {
                echo '<p>'.__('Task', 'my-autopost').': <b>'.$task_config->name.'</b>'.__('does not detect a new article', 'my-autopost').'</p>';
            }
            echo '</div>';
        }

        /* 任务执行完毕 */
        my_autopost_db_update_task_running_status($task_id, 0);
        return;
    }
}

function my_autopost_pre_fetch($task_id, $urls, $task_config, $print)
{
    global $wpdb, $t_map_updated_record, $t_map_config;
    $num = 0;
    foreach ($urls as $url) {
        if ($print) {
            my_autopost_print_info('<p>' . __('Crawl URL : ', 'my-autopost') . $url . '</p>');
        }
        $article = my_autopost_get_article($url, $task_config, true);
        if (!is_array($article)) {
            $error_code = $article;
            my_autopost_log_err($task_id, $url, $error_code, $task_config->name, $print);
            continue;
        } else {
            $title = $article['title'];
            if ($title == '') {
                my_autopost_log_err($task_id, $url, ERROR_FIND_ARTICLE_TITLE, $task_config->name, $print);
                continue;
            }
            if ($task_config->check_duplicate == 1) {
                if (my_autopost_db_check_title($task_id, $title)) {
                    continue;
                }
            }

            $success = true;
            $wpdb->query('SET AUTOCOMMIT=0');
            $wpdb->query('BEGIN');
            if (!$wpdb->query($wpdb->prepare("INSERT INTO {$t_map_updated_record}(config_id, url, title, post_id, date_time, url_status) VALUES (%d, %s, %s, %d, %d, %d)",
                $task_id, $url, $title, 0, current_time('timestamp'), 0))) {
                $wpdb->query('ROLLBACK');
                $success = false;
            };
            if (!$wpdb->query('UPDATE ' . $t_map_config . ' SET last_update_time = ' . current_time('timestamp') . ' WHERE id=' . $task_id)) {
                $wpdb->query('ROLLBACK');
                $success = false;
            }
            $wpdb->query('COMMIT');
            if ($success) {
                $num++;
                if ($print) {
                    my_autopost_print_info('<p>' . __('Find Article : ', 'my-autopost') . $title . '</p>');
                }
            }
        }
    }
    return $num;
}

/**
 * 抓取并发布文章
 *
 * @param int       $task_id        任务id
 * @param array     $urls           抓取文章URL数组
 * @param array     $task_config    任务配置
 * @param int       $print          是否打印处理过程中的信息
 * @param array     $record_ids     record表中的文章id列表
 * @return int
 */
function my_autopost_fetch_and_post($task_id, $urls, $task_config, $print, $record_ids = NULL)
{
    wp_set_current_user(get_option('my_autopost_admin_id'));
    $num = count($urls);
    $current_time = current_time('timestamp');
    $post_scheduled = json_decode($task_config->post_scheduled);
    if (!is_array($post_scheduled)) {
        $post_scheduled = array(0, 12, 0);
    }
    $is_post_scheduled = $post_scheduled[0];
    if ($is_post_scheduled) {
        $post_time = mktime($post_scheduled[1], $post_scheduled[2], 0, date('m', $current_time), date('d', $current_time), date('Y', $current_time));
        if ($post_time < $current_time) {
            $post_time += 86400;
        }
    } else {
        $post_time_p = $task_config->published_interval / 12;
        $post_time = $current_time - (($num - 1) * $task_config->published_interval) * 60;
    }
    if (!$record_ids) {
        $record_ids = array();
    }
    $i = 0;
    for ($j = 0; $j < $num; $j++) {
        if (!$is_post_scheduled && $post_time > $current_time) {
            $post_time = $current_time - ((($num - 1) - $j) * $task_config->published_interval) * 60;
        }
        if (!my_autopost_db_task_is_running($task_id)) {
            return $i;
        }
        if (!$is_post_scheduled && $i == $num - 1) {
            $post_time = current_time('timestamp');
        }
        if ($print) {
            my_autopost_print_info('<p>'.__('Crawl URL : ', 'my-autopost').$urls[$j].'</p>');
        }
        $article = my_autopost_get_article($urls[$j], $task_config, 0);
        if (!is_array($article)) {
            $error_code = $article;
            my_autopost_log_err($task_id, $urls[$i], $error_code, $task_config->name);
            continue;
        }

        if (!trim($article['title'])) {
            my_autopost_log_err($task_id, $urls[$i], ERROR_FIND_ARTICLE_TITLE, $task_config->name);
            continue;
        }

        if (!trim($article['content'])) {
            my_autopost_log_err($task_id, $urls[$i], ERROR_FIND_ARTICLE_CONTENTS, $task_config->name);
            continue;
        }

        $post_id = my_autopost_insert_article($article, $task_config, $urls[$j], $post_time, $record_ids[$j]);
        if ($post_id > 0) {
            if ($print) {
                my_autopost_print_info('<p>'.__('Updated Post', 'my-autopost').' : <a href="'.get_permalink($post_id).'" target="_blank">'.$article[0].'</a></p>');
            }
        }
        if (!$is_post_scheduled) {
            $post_time += mt_rand(($task_config->published_interval - $post_time_p), ($task_config->published_interval + $post_time_p)) * mt_rand(50, 70);
        }
        $i++;
    }
    unset($post_scheduled);
    return $i;
}

/**
 * 将抓取到的文章插入wordpress文章数据表
 *
 * @param array     $article        文章信息数组
 * @param array     $task_config    任务配置
 * @param string    $url            抓取文章链接
 * @param int       $time           文章发布日期
 * @param int       $record_id      文章在my_autopost_record中的ID
 * @return int|WP_Error
 */
function my_autopost_insert_article($article, $task_config, $url, $time, $record_id = 0)
{
    /* 如果文章已发布过,则直接返回 */
    if (my_autopost_db_check_posted_url($task_config->id, $url)) {
        return 0;
    }
    $cats = array_map('intval', explode(',', $task_config->cat));
    if ($article['date'] > 0) {
        $post_date = date('Y-m-d H:i:s', $article['date']);
    } else {
        $post_date = date('Y-m-d H:i:s', $time);
    }
    $post_type = 'post';
    if ($task_config->post_type == 'page') {
        $post_type = 'page';
        $cats = null;
    } else {
        $post_type = $task_config->post_type;
    }
    $post = array(
        'post_title' => $article['title'],
        'post_content' => $article['content'],
        'post_excerpt' => $article['excerpt'],
        'post_status' => 'publish',
        'post_author' => $task_config->author,
        'post_category' => $cats,
        'post_date' => $post_date,
        'post_type' => $post_type);

    $post_id = wp_insert_post($post);
    if (!$cats) {
        foreach ($cats as $cat) {
            wp_set_object_terms($post_id, $cat, my_autopost_db_get_taxonomy_by_term_id($cat), true);
        }
    }
    if ($task_config->post_format) {
        set_post_format($post_id, $task_config->post_format);
    }
    if ($post_id > 0) {
        my_autopst_db_transaction_work('my_autopost_db_insert_or_update_record', array(
            'task_config'   =>  $task_config,
            'post_id'       =>  $post_id,
            'url'           =>  $url,
            'title'         =>  $article['title'],
            'status'        =>  1,
            '$record_id'    =>  $record_id,
        ));
    }
    return $post_id;
}

/**
 * 检查任务是否需要更新
 *
 * @param bool|true $print  是否输出相关信息
 */
function my_autopost_check_update($print = true)
{
    global $wpdb, $t_map_config;
    if ($wpdb->get_var("SHOW TABLES LIKE '{$t_map_config}'") != $t_map_config) {
        return;
    }
    $tasks = $wpdb->get_results("SELECT id, last_update_time, update_interval, is_running FROM $t_map_config WHERE activation = 1 ORDER BY id");
    if ($tasks && is_array($tasks)) {
        $current_time = current_time('timestamp');
        foreach ($tasks as $task) {
            /* 如果任务已运行超过了两个小时,则将其停止 */
            if ($task->is_running == 1 && $current_time > $task->last_update_time + 120 * 60) {
                $wpdb->query("UPDATE $t_map_config SET set is_running = 0 where id = $task->id");
                return;
            }
            /* 如果任务上次更新时间与更新时间间隔的总和小于当前时间,则自动更新任务 */
            if ($current_time > $task->last_update_time + $task->update_interval * 60 && $task->is_running == 0) {
                $wpdb->query("UPDATE $t_map_config SET last_update_time = $current_time WHERE id = $task->id");
                ignore_user_abort(true);
                set_time_limit((int)get_option('my_autopost_time_limit'));
                my_autopost_fetch($task->id, $print, 0);
                if ($print) {
                    ob_flush();
                    flush();
                }
            }
        }
    }
}