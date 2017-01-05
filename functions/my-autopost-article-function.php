<?php

/**
 * 将URL通配符匹配规则转换为相应的正则规则
 *
 * @param string    $url    URL通配符匹配规则
 * @return string   正则规则
 */
function my_autopost_preg_url($url)
{
    $f = array('/', '?', '.');
    $r = array('\\/', '\\?', '\\.');
    $url = str_ireplace($f, $r, $url);
    $url = str_ireplace('(*)', '[a-z0-9A-Z_%-]+', $url);
    $url = ('/^' . $url) . '$/';
    return $url;
}

/**
 * 压缩html页面代码
 *
 * @param string    $html   html页面代码
 * @return string   压缩后的html页面代码
 */
function my_autopost_compress_html($html)
{
    $html = str_replace("\r\n", ' ', $html);
    $html = str_replace("\r\n", ' ', $html);
    $html = str_replace('	', ' ', $html);
    return preg_replace('/>[ ]+</', '> <', $html);
}

/**
 * 获取所有的文章列表页面链接
 *
 * @param array $task_config    任务配置
 * @param array $list_urls      文章列表页面
 * @return array    文章列表数组
 */
function my_autopost_get_article_list_urls($task_config, $list_urls)
{
    $article_list_urls = array();
    if ($task_config->source_type == 0) {
        foreach ($list_urls as $list_url) {
            $article_list_urls[] = $list_url->url;
        }
    } else if ($task_config->source_type == 1) {
        foreach ($list_urls as $list_url) {
            for ($i = $task_config->start_num; $i <= $task_config->end_num; $i++) {
                $article_list_urls[] = str_ireplace('(*)', $i, $list_url->url);
            }
        }
    }
    return $article_list_urls;
}

/**
 * 打印所有文章列表页面链接以及文章列表页面内的所有文章链接(测试时最多只打开两个文章列表页面)
 *
 * @param array $task_config    任务配置
 * @param array $list_urls      文章列表页面
 * @return array    所有文章链接数组
 */
function my_autopost_print_article_list_urls($task_config, $list_urls)
{
    $test_list_urls = my_autopost_get_article_list_urls($task_config, $list_urls);

    $url_num = 0;
    $article_urls = array();
    foreach ($test_list_urls as $url) {
        if (++$url_num > LIST_URL_NUM) {
            echo '.......<br/><p><code><b>'.__('In test only try to open', 'my-autopost').LIST_URL_NUM.__('URLs of Article List', 'my-autopost').'</b></code></p>';
            break;
        }
        $urls = my_autopost_print_article_urls($task_config, $url);
        $article_urls = array_merge($article_urls, $urls);
    }
    return $article_urls;
}

/**
 * 打印文章列表页面所有的文章链接
 *
 * @param array     $task_config    任务配置
 * @param string    $url            文章列表页面URL
 * @return array    文章链接数组
 */
function my_autopost_print_article_urls($task_config, $url)
{
    $article_urls = my_autopost_get_article_urls($task_config, $url);
    if (!is_array($article_urls)) {
        $error_code = $article_urls;
        my_autopost_print_err($error_code, $url);
        return array();
    }

    echo '<p><b>' . __('The Article List URL', 'my-autopost') . ':<code>' . $url . '</code>, ' . __('All articles in the following', 'my-autopost') . '</b></p>';
    foreach ($article_urls as $article_url) {
        echo "<a href='$article_url' target='_blank'>$article_url</a><br>";
    }

    return $article_urls;
}

/**
 * 获取文章列表页面所有的文章链接
 *
 * @param array     $task_config    任务配置
 * @param string    $url            文章列表页面URL
 * @return array|int    成功时返回文章链接数组,否则返回错误码
 */
function my_autopost_get_article_urls($task_config, $url)
{
    $use_p = json_decode($task_config->proxy);
    $proxy = get_option('my-autopost-proxy');
    $html_dom = my_autopost_file_get_html($url, $task_config->page_charset, Method, $use_p[0], $use_p[1], $proxy);
    if (!$html_dom) {
        return ERROR_OPEN_URL;
    }
    if ($task_config->a_match_type == 1) {
        $article_tags = $html_dom->find($task_config->a_selector);
    } else {
        $article_tags = $html_dom->find('a');
    }
    if (!$article_tags) {
        return ERROR_FIND_ARTICLE_URL;
    }

    $article_urls = array();
    foreach ($article_tags as $article_tag) {
        $article_urls[] = htmlspecialchars_decode(trim($article_tag->href));
    }
    if ($task_config->a_match_type == 0) {
        $article_urls = array_unique($article_urls);
        $preg_url = my_autopost_preg_url($task_config->a_selector);
        $article_urls = preg_grep($preg_url, $article_urls);
    }
    if (count($article_urls) < 1) {
        return ERROR_FIND_ARTICLE_URL;
    }
    if ($task_config->reverse_sort == 1) {
        $article_urls = array_reverse($article_urls);
    }

    $html_dom->clear();
    return $article_urls;
}

/**
 * 获取文章信息
 *
 * @param string     $url           文章url
 * @param array      $task_config   任务配置
 * @param bool|false $only_title    是否只抓取文章标题
 * @return array|int 成功时返回文章信息数组,否则返回错误码
 */
function my_autopost_get_article($url, $task_config, $only_title = false)
{
    $proxy = get_option('my-autopost-proxy');
    $use_p = json_decode($task_config->proxy);
    $html_dom = my_autopost_file_get_html($url, $task_config->page_charset, Method, $use_p[0], $use_p[1], $proxy);
    if (!$html_dom) {
        return ERROR_OPEN_URL;
    }

    $article = array();
    if ($task_config->page_charset != 'UTF-8') {
        $utf_html = $html_dom->save();
        $utf_html = iconv($task_config->page_charset, 'UTF-8//IGNORE', $utf_html);
        $utf_html = my_autopost_compress_html($utf_html);
    } else {
        $utf_html = $html_dom->save();
        $utf_html = my_autopost_compress_html($utf_html);
    }

    if (!trim($task_config->title_selector)) {
        $article['title'] = '';
    } else {
        if ($task_config->title_match_type == 0) {
            $article['title'] = $html_dom->find($task_config->title_selector, 0)->plaintext;
            if ($task_config->page_charset != 'UTF-8') {
                $article['title'] = trim(iconv($task_config->page_charset, 'UTF-8//IGNORE', $article['title']));
            }
        } else {
            $article['title'] = trim(my_autopost_get_content_by_rule($utf_html, $task_config->title_selector));
        }
        if ($article['title']) {
            $article['title'] = strip_tags($article['title']);
        }
    }

    if (!$only_title) {
        if (!trim($task_config->content_selector)) {
            $article['content'] = '';
        } else {
            $content_match_types = json_decode($task_config->content_match_type);
            $content_selectors = json_decode($task_config->content_selector);
            $content_match_type = array();
            $outer = array();
            $objective = array();
            $index = array();
            foreach ($content_match_types as $cmts) {
                list($content_match_type[], $outer[], $objective[], $index[]) = explode(',', $cmts);
            }

            for ($i = 0, $match_num = count($content_selectors); $i < $match_num; $i++) {
                if ($content_match_type[$i] == 0) {
                    switch ($objective[$i]) {
                        case '0':
                            $article['content'] .= my_autopost_get_content_by_css($html_dom, $content_selectors[$i], $task_config->page_charset, $outer[$i], $index[$i]);
                            break;
                        case '1':
                            $article['date'] = strtotime(my_autopost_get_content_by_css($html_dom, $content_selectors[$i], $task_config->page_charset, 2, $index[$i]));
                            break;
                        case '2':
                            $article['excerpt'] = my_autopost_get_content_by_css($html_dom, $content_selectors[$i], $task_config->page_charset, $outer[$i], $index[$i]);
                            break;
                    }
                } else {
                    switch ($objective[$i]) {
                        case '0':
                            $article['content'] .= my_autopost_get_content_by_rule($utf_html, $content_selectors[$i], $outer[$i]);
                            break;
                        case '1':
                            $article['date'] = strtotime(my_autopost_get_content_by_rule($utf_html, $content_selectors[$i], $outer[$i]));
                            break;
                        case '2':
                            $article['excerpt'] = my_autopost_get_content_by_rule($utf_html, $content_selectors[$i], $outer[$i]);
                            break;
                    }
                }
            }
            if ($article['content']) {
                if (get_option('my_autopost_del_comment') == 1) {
                    $article['content'] = my_autopost_filter_comment($article['content']);
                }
                $article['content'] = my_autopost_filter_comm_attr($article['content'],
                    get_option('my_autopost_del_attr_id'),
                    get_option('my_autopost_del_attr_class'),
                    get_option('my_autopost_del_attr_style'));
            }

        }
    }

    $html_dom->clear();
    unset($html_dom);
    unset($utf_html);
    return $article;
}

/**
 * 输出抓取的文章内容
 *
 * @param array $article    文章信息数组
 */
function my_autopost_print_article($article)
{
    if ($article['title']) {
        echo '<p><b>'.__('Article Title', 'my-autopost').':</b> '.$article['title'].'</p>';
    } else {
        echo '<p><b>'.__('Article Title', 'my-autopost').':</b>';
        my_autopost_print_err(ERROR_FIND_ARTICLE_TITLE);
    }

    if ($article['date']) {
        $post_date = date('Y-m-d H:i:s', $article['date']);
        echo '<p><b>'.__('Post Date', 'my-autopost').':</b>'.$post_date.'</p>';
    }

    if ($article['excerpt']) {
        echo '<p><b>'.__('Post Excerpt', 'my-autopost').':</b></p><div>'.$article['excerpt'].'</div>';
    }

    echo '<br/><b>'.__('Post Content', 'my-autopost').':</b>';
    if ($article['content']) {
?>
        <input type="hidden" id="ap_content_s" value="0">
        <script type="text/javascript">
            function showHTML(){
                var s = jQuery("#ap_content_s").val();
                if (s == 0) {
                    jQuery("#ap_content").hide();
                    jQuery("#ap_content_html").show();
                    jQuery("#ap_content_s").val(1);
                } else {
                    jQuery("#ap_content_html").hide();
                    jQuery("#ap_content").show();
                    jQuery("#ap_content_s").val(0);
                }
            }
        </script>
        <a href="javascript:;" onclick="showHTML()">[ HTML ]</a><br/>
        <div id="ap_content"><?php echo $article['content']; ?></div>
        <textarea id="ap_content_html" style="display:none;"><?php echo $article['content']; ?></textarea>
<?php
    } else {
        my_autopost_print_err(ERROR_FIND_ARTICLE_CONTENTS);
    }
}

/**
 * 根据URL通配符匹配文章内容
 *
 * @param string    $utf_html   文章html页面
 * @param string    $rule       URL通配符规则
 * @param int       $outer      0: outtext 1: innertext
 * @return string  匹配的文章内容
 */
function my_autopost_get_content_by_rule($utf_html, $rule, $outer = 0)
{
    $match = explode('(*)', trim($rule));
    $p0 = stripos($utf_html, trim($match[0]));
    if ($outer == 1) {
        $start = $p0;
    } else {
        $start = $p0 + strlen($match[0]);
    }
    $p1 = stripos($utf_html, trim($match[1]), $start);
    if ($p0 === false || $p1 === false) {
        return '';
    }
    if ($outer == 1) {
        $length = ($p1 + strlen($match[1])) - $start;
    } else {
        $length = $p1 - $start;
    }
    return substr($utf_html, $start, $length);
}

/**
 * 使用CSS选择器查找文章内容
 *
 * @param simple_html_node  $html_dom   代表文章页面的simple_html_node对象
 * @param string            $selector   CSS选择器
 * @param string            $charset    页面字符集
 * @param int               $outer      0: outtext  1: innertext  2: plaintext
 * @param int               $index      索引(0: 返回所有符合CSS选择器的内容 非0: 返回符合CSS选择器所有内容中对应索引的部分)
 * @return string   匹配的文章内容
 */
function my_autopost_get_content_by_css($html_dom, $selector, $charset, $outer, $index)
{
    $content = '';
    $elements = $html_dom->find($selector);
    if ($index == 0) {
        foreach ($elements as $e) {
            if ($outer == 0) {
                $content .= $e->innertext;
            } elseif ($outer == 1) {
                $content .= $e->outertext;
            } elseif ($outer == 2) {
                $content .= $e->plaintext;
            }
        }
    } else {
        $i = 0;
        if ($index >= 1) {
            $i = $index - 1;
        } elseif ($index < 0) {
            $i = count($elements) + $index;
        }
        $e = $elements[$i];
        if ($e) {
            if ($outer == 0) {
                $content .= $e->innertext;
            } elseif ($outer == 1) {
                $content .= $e->outertext;
            } elseif ($outer == 2) {
                $content .= $e->plaintext;
            }
        }
    }

    unset($elements);
    if ($charset != 'UTF-8') {
        $content = iconv($charset, 'UTF-8//IGNORE', $content);
    }
    return $content;
}

/**
 * 过滤文章内容中的注释部分
 *
 * @param string    $s  文章内容
 * @return string   过滤注释后的文章内容
 */
function my_autopost_filter_comment($s)
{
    $dom = my_autopost_str_get_html($s);
    foreach ($dom->find('comment') as $e) {
        $e->outertext = '';
    }
    $s = $dom->save();
    $dom->clear();
    unset($dom);
    return $s;
}

/**
 * 过滤文章内容中的通用属性
 *
 * @param string    $s          文章内容
 * @param int       $f_id       是否过滤id属性
 * @param int       $f_class    是否过滤class属性
 * @param int       $f_style    是否过滤style属性
 * @return string   过滤通用属性后的文章内容
 */
function my_autopost_filter_comm_attr($s, $f_id, $f_class, $f_style)
{
    $dom = my_autopost_str_get_html($s);
    if ($f_id == 1) {
        foreach ($dom->find('[id]') as $e) {
            $e->removeAttribute('id');
        }
    }
    if ($f_class == 1) {
        foreach ($dom->find('[class]') as $e) {
            $e->removeAttribute('class');
        }
    }
    if ($f_style == 1) {
        foreach ($dom->find('[style]') as $e) {
            $e->removeAttribute('style');
        }
    }
    $s = $dom->save();
    $dom->clear();
    unset($dom);
    return $s;
}

/**
 * 对文章内容进行自动链接替换
 *
 * @param string    $content    文章内容
 * @param array     $autolinks  自动链接关键字数组
 * @return string   进行自动链接替换后的文章内容
 */
function my_autopost_link_replace($content, $autolinks)
{
    $ignore_pre = 1;
    global $my_autolink_replaced;
    $my_autolink_replaced = false;
    foreach ($autolinks as $autolink) {
        $keyword = $autolink->keyword;
        list($link, $desc, $nofollow, $newwindow, $firstonly, $ignorecase, $WholeWord) = explode('|', $autolink->details);
        if ($ignorecase == 1) {
            if (stripos($content, $keyword) === false) {
                continue;
            }
        } else {
            if (strpos($content, $keyword) === false) {
                continue;
            }
        }
        $my_autolink_replaced = true;
        $cleankeyword = stripslashes($keyword);
        if (!$desc) {
            $desc = $cleankeyword;
        }
        $desc = addcslashes($desc, '$');
        $url = "<a href=\"{$link}\" title=\"{$desc}\"";
        if ($nofollow) {
            $url .= ' rel="nofollow"';
        }
        if ($newwindow) {
            $url .= ' target="_blank"';
        }
        $url .= ('>' . addcslashes($cleankeyword, '$')) . '</a>';
        if ($firstonly) {
            $limit = 1;
        } else {
            $limit = -1;
        }
        if ($ignorecase) {
            $case = 'i';
        } else {
            $case = '';
        }
        $ex_word = preg_quote($cleankeyword, '\'');
        if ($ignore_pre) {
            if ($num_1 = preg_match_all('/<pre.*?>.*?<\\/pre>/is', $content, $ignore_pre)) {
                for ($i = 1; $i <= $num_1; $i++) {
                    $content = preg_replace('/<pre.*?>.*?<\\/pre>/is', "%ignore_pre_{$i}%", $content, 1);
                }
            }
        }
        $content = preg_replace(('|(<img)(.*?)(' . $ex_word) . ')(.*?)(>)|U', '$1$2%&&&&&%$4$5', $content);
        $cleankeyword = preg_quote($cleankeyword, '\'');
        if ($WholeWord == 1) {
            $regEx = (('\'(?!((<.*?)|(<a.*?)))(\\b' . $cleankeyword) . '\\b)(?!(([^<>]*?)>)|([^>]*?</a>))\'s') . $case;
        } else {
            $regEx = (('\'(?!((<.*?)|(<a.*?)))(' . $cleankeyword) . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s') . $case;
        }
        $content = preg_replace($regEx, $url, $content, $limit);
        $content = str_replace('%&&&&&%', stripslashes($ex_word), $content);
        if ($ignore_pre) {
            for ($i = 1; $i <= $num_1; $i++) {
                $content = str_replace("%ignore_pre_{$i}%", $ignore_pre[0][$i - 1], $content);
            }
        }
    }
    return $content;
}

/**
 * 对文章进行自动链接
 *
 * @param array $object     文章对象
 * @param array $autolinks  自动链接关键字数组
 */
function my_autopost_link_post($object, $autolinks)
{
    $content = my_autopost_link_replace($object->post_content, $autolinks);
    global $my_autolink_replaced, $wpdb;
    if ($my_autolink_replaced) {
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = %s WHERE ID = %d ", $content, $object->ID));
    }
}