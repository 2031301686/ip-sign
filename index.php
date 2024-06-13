<?php

/*
* 原项目开源地址：https://github.com/xhboke/IP
* 二开开源地址：https://github.com/2031301686/ip-sign/
* 更新地址：https://wxsnote.cn/2762.html
* 二开作者：天无神话-王先生笔记
* 博客地址：https://wxsnote.cn/2762.html
*/


header("Content-type: image/JPEG");
use UAParser\Parser;
require_once './vendor/autoload.php';

//获取真实IP，注意主要为CDN站点服务，若无CDN，有被伪造的可能
function wxs_get_ip() {
	if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$ip = getenv('HTTP_CLIENT_IP');
	} elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$ip = getenv('REMOTE_ADDR');
	} elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
}


//公共变量
$ip = wxs_get_ip();
$ua = $_SERVER['HTTP_USER_AGENT'];
$get = $_GET["s"];
$get = base64_decode(str_replace(" ","+",$get));
$weekarray = array("日","一","二","三","四","五","六");
//ua
$parser = Parser::create();
$result = $parser->parse($ua);
$os = $result->os->toString();
// Mac OS X
$browser = $result->device->family.'-'.$result->ua->family;
// Safari 6.0.2 

//必须信息
//高德key
$gd_key = '填这里或删除';
$gd_Secret_key ="这里或删除";
//腾讯key
$tx_key = '填这里或删除';
$tx_Secret_key ="填这里或删除";


//高德api
function gd_api($ip, $gd_key, $gd_Secret_key) {
    $gd_api_url = 'http://restapi.amap.com/v3/ip?key='.$gd_key.'&ip='.$ip;
    
    if ($gd_Secret_key) {
            $gd_api_url .= '&sig=' . md5('ip=' . $ip . '&key=' . $gd_key . $gd_Secret_key);
        }
        
    $UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $gd_api_url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_ENCODING, '');
    curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    $data = curl_exec($curl);
    // 将JSON响应转换为关联数组
    $data = json_decode($data, true);
    
    // 提取province和city
    $province = $data['province'];
    $city = $data['city'];
    
    // 返回仅包含province和city的数组
    return array('province' => $province, 'city' => $city);
}


//腾讯api
function tx_api($ip, $tx_key, $tx_Secret_key) {
    //通过腾讯接口获取
    $tx_api_url = 'http://apis.map.qq.com/ws/location/v1/ip?ip=' . $ip . '&key=' . $tx_key;
    //签名校验
    if ($tx_Secret_key) {
        $tx_api_url .= '&sig=' . md5('/ws/location/v1/ip?ip=' . $ip . '&key=' . $tx_key . $tx_Secret_key);
    }
        
    $UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $tx_api_url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_ENCODING, '');
    curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    $data = curl_exec($curl);
    // 将JSON响应转换为关联数组
    $data = json_decode($data, true);
    
    // 提取province和city
    $province = $data['result']['ad_info']['province'];
    $city = $data['result']['ad_info']['city'];
    
    // 返回仅包含province和city的数组
    return array('province' => $province, 'city' => $city);
}


function tpy_api($ip) {
    
    $tpy_api_url = 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip=' . $ip;
    $UserAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $tpy_api_url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_ENCODING, '');
    curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    $data = curl_exec($curl);
    $data = mb_convert_encoding($data, 'UTF-8', 'GBK');
    
    // 将JSON响应转换为关联数组
    $data = json_decode($data, true);
    
    if(isset($data['pro'])) {
        $province = $data['pro'];
    }
    
    if(isset($data['city'])) {
        $city = $data['city'];
    }
    
    // 返回仅包含province和city的数组
    return array('province' => $province, 'city' => $city);
}


// 调用高德API
$gd_data = gd_api($ip, $gd_key, $gd_Secret_key);

// 处理高德API返回的数据
$gd_data_json = json_encode($gd_data);
$gd_data = json_decode($gd_data_json, true);

// 判断高德API返回数据是否包含地址信息
if (!empty($gd_data['province']) && !empty($gd_data['city'])) {
    // 判断省份和城市是否相同，如果相同则只输出城市
    if ($gd_data['province'] == $gd_data['city']) {
        $address = $gd_data['city'];
    } else {
        $address = $gd_data['province'] . '-' . $gd_data['city'];
    }
} else {
    // 调用腾讯API
    $tx_data = tx_api($ip, $tx_key, $tx_Secret_key);
    
    // 处理腾讯API返回的数据
    $tx_data_json = json_encode($tx_data);
    $tx_data = json_decode($tx_data_json, true);
    
    // 判断腾讯API返回数据是否包含地址信息
    if (!empty($tx_data['province']) && !empty($tx_data['city'])) {
        // 判断省份和城市是否相同，如果相同则只输出城市
        if ($tx_data['province'] == $tx_data['city']) {
            $address = $tx_data['city'];
        } else {
            $address = $tx_data['province'] . '-' . $tx_data['city'];
        }
    } else {
        // 调用太平洋API
        $tpy_data = tpy_api($ip);

        // 判断太平洋API返回数据是否包含地址信息
        if (!empty($tpy_data['province']) && !empty($tpy_data['city'])) {
            // 判断省份和城市是否相同，如果相同则只输出城市
            if ($tpy_data['province'] == $tpy_data['city']) {
                $address = $tpy_data['city'];
            } else {
                $address = $tpy_data['province'] . '-' . $tpy_data['city'];
            }
        } else {
            $address = '神秘星球';
        }
    }
}

//定义背景
$im = imagecreatefromjpeg("./xhxh.jpg");
//定义颜色
$black = ImageColorAllocate($im, 0,0,0);
//定义颜色的值为红色
$red = ImageColorAllocate($im, 255,0,0);
//加载字体
$font = 'msyh.ttf';
//输出
imagettftext($im, 16, 0, 10, 40, $red, $font,'欢迎您来自 '.$address.' 的朋友');
imagettftext($im, 16, 0, 10, 72, $red, $font, '今天是'.date('Y年n月j日').' 星期'.$weekarray[date("w")]);
//当前时间添加到图片
imagettftext($im, 16, 0, 10, 104, $red, $font,'您的IP是:'.$ip.'');
//ip
imagettftext($im, 16, 0, 10, 140, $red, $font,'您使用的是'.$os.'操作系统');
imagettftext($im, 16, 0, 10, 175, $red, $font,'欢迎您访问王先生笔记');
imagettftext($im, 13, 0, 10, 200, $black, $font,$get);
ImageGif($im);
ImageDestroy($im);
function curl_get($url, array $params = array(), $timeout = 6) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$file_contents = curl_exec($ch);
	curl_close($ch);
	return $file_contents;
}
?>