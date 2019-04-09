<?php
/**
 * User: chenjunliang
 * Time: 2019/3/17 21:40
 */
header("Content-Type: text/html;charset=utf-8");
date_default_timezone_set('PRC');
set_time_limit(0);

//header("Content-Type: text/html; charset=gb2312");

require './simple_html_dom.php';
$url     = [
    //    'http://139.199.132.84/index.php/category/%E5%B8%88%E8%B5%84%E9%98%9F%E4%BC%8D/',
    'http://chxy.wxc.edu.cn/2539/list.htm',
    'http://chxy.wxc.edu.cn/2539/list2.htm',


];
$reg_url = 'http://chxy.wxc.edu.cn/20';
$arr     = getUrl($url, $reg_url);
var_dump($arr);
//die;
$ret = '';
foreach($arr as $value) {
    $info = getInfo($value);
    if($info && ($info['mobile'] || preg_match('~qq~', $info['email']))) {
        $ret = $info['name'] . "\t" . $info['mobile'] . "\t" . $info['email'] . "\n";
        file_put_contents('./1.txt', $ret, FILE_APPEND);
    }
}
function getInfo($url) {
    $reg_mobile = '~[^0-9]{1}1[34578][0-9]{9}[^0-9]{1}~';
    $reg_email  = '/([a-z0-9\-_\.]+@[a-z0-9]+\.[a-z0-9\-_\.]+)/';
    //    $url = strtr($url, array("amp;" => ''));
    //    var_dump($url);
    $content = curl_get_contents($url);
    if(!$content) {
        return false;
    }
    $html = new simple_html_dom();
    $html->load($content);
    $title = $html->find('title')[0]->innertext;
    //                    $title = $html->find('#read_teacher_content td')[1]->innertext;
    $ret           = ['name' => $title];
    $flag          = preg_match_all($reg_mobile, $content, $result1);
    $ret['mobile'] = '';
    $badPhone      = '13637097396';
    if($flag) {
        $mobile = $result1[0][0];
        preg_match_all('~1[34578][0-9]{9}~', $mobile, $ree);
        if($ree[0][0] == $badPhone) {
            if(isset($result1[0][1]) && $result1[0][1]) {
                $mobile = $result1[0][1];
                preg_match_all('~1[34578][0-9]{9}~', $mobile, $ree1);
                $ret['mobile'] = $ree1[0][0];
            }
        } else {
            $ret['mobile'] = $ree[0][0];
        }
    }
    $num = preg_match_all($reg_email, $content, $result2);
    if($num) {
        $ret['email'] = implode(' ', array_unique($result2[0]));
    } else {
        $ret['email'] = '';
    }
    unset($content);

    return $ret;
}

function getUrl($url, $reg) {
    $retArr = [];
    //    $content = curl_get_contents($url[0]);
    //    $html    = new simple_html_dom();
    //    $html->load($content);
    //    $urlInfo = $html->find('a');
    //    foreach($urlInfo as $value) {
    //        $urlArr[] = $reg.$value->href;
    //    }
    //    $url = $urlArr;
    foreach($url as $item) {
        $content = curl_get_contents($item);
        $html    = new simple_html_dom();
        $html->load($content);
        $urlInfo = $html->find('a');
        foreach($urlInfo as $value) {
            $href = $value->href;
            var_dump($href);
            if(preg_match('~^\.\./\.\.\/info~', $href)) {
                //                $retArr[] = str_replace("../../info", $reg, $href);
            } else if(preg_match('~^\.\.\/info~', $href)) {
                //                $retArr[] = str_replace("../info", $reg, $href);
            } else if(preg_match('~^/20~', $href)) {
                $retArr[] = str_replace("/20", $reg, $href);
            } else if(preg_match('~^http\:\/\/sxx~', $href)) {
                //                $retArr[] = $href;
            } else {
                //                                $retArr[] = $reg . $href;
            }
        }
    }

    return array_unique($retArr);
}


function curl_get_contents($url, $cookie = '', $referer = '', $timeout = 300, $ishead = 0) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36');
    if($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    if($referer) {
        curl_setopt($curl, CURLOPT_REFERER, $referer);
    }
    $ssl = substr($url, 0, 8) == "https://" ? true : false;
    if($ssl) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    }
    $res      = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if($httpCode != 200) {
        return false;
    }

    return $res;
}

function curl_get($url) {
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // https请求 不验证证书和hosts
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    $tmpInfo = curl_exec($curl);     //返回api的json对象
    //关闭URL请求
    curl_close($curl);

    return $tmpInfo;    //返回json对象
}