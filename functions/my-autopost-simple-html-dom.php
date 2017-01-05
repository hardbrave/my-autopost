<?php

include MAP_PLUGIN_DIR . '/third-libraries/simple_html_dom.php';

if (function_exists('curl_init')) {
    define('Method', 0);
} else {
    define('Method', 1);
}

if ((ini_get('safe_mode') == 0 || ini_get('safe_mode') == null) && ini_get('open_basedir') == '') {
    define('CAN_FOLLOWLOCATION', 1);
} else {
    define('CAN_FOLLOWLOCATION', 0);
}

function curl_get_contents($url, $useProxy = 0, $proxy = null, $hideIP = 0, $timeout = 30)
{
    $curlHandle = curl_init();
    $agent = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.19 (KHTML, like Gecko) Chrome/25.0.1323.1 Safari/537.19';
    curl_setopt($curlHandle, CURLOPT_URL, $url);
    curl_setopt($curlHandle, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curlHandle, CURLOPT_USERAGENT, $agent);
    curl_setopt($curlHandle, CURLOPT_REFERER, _REFERER_);
    curl_setopt($curlHandle, CURLOPT_HEADER, false);
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlHandle, CURLOPT_ENCODING, '');
    if ($useProxy == 1) {
        curl_setopt($curlHandle, CURLOPT_PROXY, $proxy['ip']);
        curl_setopt($curlHandle, CURLOPT_PROXYPORT, $proxy['port']);
        if ((($proxy['user'] != '' && $proxy['user'] != NULL) && $proxy['password'] != '') && $proxy['password'] != NULL) {
            $userAndPass = ($proxy['user'] . ':') . $proxy['password'];
            curl_setopt($curlHandle, CURLOPT_PROXYUSERPWD, $userAndPass);
        }
    }
    if ($hideIP == 1) {
        $ip = (((((rand(1, 223) . '.') . rand(1, 254)) . '.') . rand(1, 254)) . '.') . rand(1, 254);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:' . $ip, 'CLIENT-IP:' . $ip));
    }
    $result = curl_exec_follow($curlHandle);
    curl_close($curlHandle);
    return $result;
}

function curl_exec_follow($ch, &$maxredirect = null)
{
    $mr = $maxredirect === null ? 5 : intval($maxredirect);
    if (CAN_FOLLOWLOCATION == 1) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
    } else {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        if ($mr > 0) {
            $newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $rch = curl_copy_handle($ch);
            curl_setopt($rch, CURLOPT_HEADER, true);
            curl_setopt($rch, CURLOPT_NOBODY, true);
            curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
            curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
            do {
                curl_setopt($rch, CURLOPT_URL, $newurl);
                $header = curl_exec($rch);
                if (curl_errno($rch)) {
                    $code = 0;
                } else {
                    $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                    if ($code == 301 || $code == 302) {
                        preg_match('/Location:(.*?)\\n/', $header, $matches);
                        $newurl = trim(array_pop($matches));
                    } else {
                        $code = 0;
                    }
                }
            } while ($code && --$mr);
            curl_close($rch);
            if (!$mr) {
                if ($maxredirect === null) {
                    trigger_error('Too many redirects. When following redirects, libcurl hit the maximum amount.', E_USER_WARNING);
                } else {
                    $maxredirect = 0;
                }
                return false;
            }
            curl_setopt($ch, CURLOPT_URL, $newurl);
        }
    }
    return curl_exec($ch);
}

function my_autopost_file_get_html($url, $target_charset = DEFAULT_TARGET_CHARSET, $method = 0, $useProxy = 0, $hideIP = 0, $proxy = null, $use_include_path = false, $context=null, $offset = -1, $maxLen=-1, $lowercase = true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT)
{
    $dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
    if ($method == 0) {
        $contents = curl_get_contents($url, $useProxy, $proxy, $hideIP);
    } else {
        $contents = file_get_contents($url, $use_include_path, $context, $offset);
    }
    if (empty($contents) || strlen($contents) > MAX_FILE_SIZE)
    {
        return false;
    }
    $dom->load($contents, $lowercase, $stripRN);
    return $dom;
}

function my_autopost_str_get_html($str, $lowercase=true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT)
{
    return str_get_html($str, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
}

function my_autopost_dump_html_tree($node, $show_attr=true, $deep=0)
{
    $node->dump($node);
}
