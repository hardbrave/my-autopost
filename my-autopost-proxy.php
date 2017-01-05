<?php

$proxy = get_option('my-autopost-proxy');
if ($_POST['save_setting']!='') {
    $proxy['ip']        =  $_POST['ip'];
    $proxy['port']      =  $_POST['port'];
    $proxy['user']      =  $_POST['user'];
    $proxy['password']  =  $_POST['password'];
    update_option('my-autopost-proxy', $proxy);
    $proxy = get_option('my-autopost-proxy');
}

if ($_POST['test_proxy']) {
    if (!$proxy['ip']) {
        $msg =  __( 'Please fill Hostname/IP', 'my-autopost' );
    } elseif (!$_POST['url']) {
        $msg =  __( 'Please fill URL', 'my-autopost' );
    } else {
        $curlHandle = curl_init();
        $agent='Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.19 (KHTML, like Gecko) Chrome/25.0.1323.1 Safari/537.19';
        curl_setopt( $curlHandle , CURLOPT_URL, $_POST['url'] );
        curl_setopt( $curlHandle , CURLOPT_TIMEOUT, 30 );
        curl_setopt( $curlHandle , CURLOPT_USERAGENT, $agent );
        curl_setopt( $curlHandle , CURLOPT_REFERER, _REFERER_ );
        curl_setopt( $curlHandle , CURLOPT_HEADER, false);
        curl_setopt( $curlHandle , CURLOPT_RETURNTRANSFER, 1 );

        curl_setopt($curlHandle,CURLOPT_PROXY,$proxy['ip']);
        curl_setopt($curlHandle,CURLOPT_PROXYPORT,$proxy['port']);
        if ($proxy['user'] && $proxy['password']) {
            $userAndPass = $proxy['user'].':'.$proxy['password'];
            curl_setopt($curlHandle,CURLOPT_PROXYUSERPWD,$userAndPass);
        }

        $result = curl_exec( $curlHandle );
        curl_close( $curlHandle );

        $file = dirname(__FILE__) . '/proxy_temp.html';
        $fileUrl = plugins_url('/proxy_temp.html' , __FILE__ );

        file_put_contents($file, $result);

        $show = true;
    }
}

include_once(MAP_PLUGIN_DIR . '/views/view-proxy-setting.php');