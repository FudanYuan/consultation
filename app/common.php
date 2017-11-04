<?php
use think\Cache;
use syhapp\model\SyhException;
use think\Request;

function http_get($url, $timeout = 5)
{
    if (!$url) return '';
    $ssl = substr($url, 0, 8) == "https://" ? true : false;
    $ch = curl_init($url);
    if ($ssl) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function formatUrl($name)
{
    return PRO_PATH . '/Media/getimg?name=' . $name;
}

function ueditorFormmatUrl($name)
{
    $request = Request::instance();
    return $request->domain() . $name;
}

function mydump($data)
{
    echo '<pre>';
    print_r(json_decode(json_encode($data), true));
    echo '</pre>';
}

/**
 * 格式化时间
 * @param $time
 * @return false|string
 */
function formatTime($time)
{
    if (!$time) return '';
    $formatTime = '';
    $curTime = time();
    $interval = $curTime - $time;
    if ($interval >= 3600 * 24) {
        $formatTime = date('m月d日', $time);
    } elseif ($interval >= 3600) {
        $formatTime = floor($interval / 3600) . '小时前';
    } else {
        $formatTime = floor($interval / 60) . '分钟前';
    }
    return $formatTime;
}

/**
 * 格式化数字
 * @param $count
 * @return int|string
 */
function formatNum($count)
{
    $count = intval($count);
    if ($count >= 100000) {
        $count = '10万+';
    } elseif ($count >= 10000) {
        $num = floor($count / 10000);
        $count = $num . '万+';
    }
    return $count;
}


/**
 * 格式化文本 --yzs 2017/08/19
 * @param $text
 * @param $count
 * @return string
 */
function formatText($text, $count)
{
    if (mb_strlen($text) > $count)
        return mb_substr($text, 0, $count, 'UTF-8') . '...';
    else
        return $text;
}


function formatAuthor($author)
{
    if (mb_strlen($author) > 7)
        return mb_substr($author, 0, 7, 'UTF-8') . '...';
    else
        return $author;
}

function formatAlias($userid)
{
    $userid = formatUserId($userid);
    $alias = 'ID' . str_repeat('0', 8 - strlen($userid)) . $userid;
    return $alias;
}

function formatUserId($userid)
{
    return $userid * 2 + 521;
}

function cache_hash_hset($key, $hkey, $value)
{
    Cache::handler()->hset($key, $hkey, $value);
}

function cache_hash_hget($key, $hkey)
{
    return Cache::handler()->hget($key, $hkey);
}

function cache_hdel($key, $hkey)
{
    return Cache::handler()->hdel($key, $hkey);
}

function cache_hash_set($key, $value)
{
    if (is_array($value)) {
        Cache::handler()->hMset($key, $value);
        return true;
    } else
        return false;
}

function cache_hash_get($key, $vkey = '')
{
    if ($vkey) {
        $res = Cache::handler()->hGet($key, $vkey);
        return $res;
    } else {
        return Cache::handler()->hGetAll($key);
    }
}

function cache_del($key)
{
    Cache::handler()->del($key);
}

/**
 * 获取有序集
 * @param $key
 * @param $start
 * @param $end
 * @param bool $withScore
 * @return mixed
 */
function cache_zRange($key, $start, $end, $withScore = false)
{
    return Cache::handler()->zRange($key, $start, $end, $withScore);
}

/**
 * 添加有序集
 * @param unknown $key
 * @param unknown $score
 * @param unknown $value
 */
function cache_zAdd($key, $score, $value)
{
    Cache::handler()->zAdd($key, $score, $value);
}

function cache_zRank($key, $val)
{
    return Cache::handler()->zRank($key, $val);
}

function cache_zRem($key, $val)
{
    Cache::handler()->zRem($key, $val);
}

function cache_zRemRangeByScore($key, $start, $end)
{
    Cache::handler()->zRemRangeByScore($key, $start, $end);
}

/**
 * 获取有序集
 * @param $key
 * @param $start
 * @param $end
 * @return mixed
 */
function cache_zRangeByScore($key, $start, $end)
{
    return Cache::handler()->zRangeByScore($key, $start, $end);
}

function testLog($title, $con)
{
    $time = date('Y-m-d');
    file_put_contents(RUNTIME_PATH . 'test' . $time . '.log', "\n" . $title . '----' . $con . "\n", FILE_APPEND);
}

/**
 * 将错误信息添加到错误队列  other main  --xjp 2016/01/11
 * @param unknown $errors
 * @param unknown $error
 */
function put_errors(&$errors, $error, $type = 'other')
{
    if (empty($errors)) $errors = array();
    if (!is_array($errors)) {
        $errors = explode(',', $errors);
    }
    if ($type == 'other') {
        if (!isset($errors['other']))
            $errors['other'] = array();
        array_push($errors['other'], $error);
    } else {
        if (is_array($error))
            $errors = array_merge($errors, $error);
        else
            array_push($errors, $error);
    }
}

function formatReturn($ret)
{
    return json_encode($ret);
}

function D($name)
{
    if (!$name) throw new SyhException('创建模型失败，名称为空', 5);
    static $models;
    if (!isset($models[$name])) {
        $full = '\\app\model\\' . $name;
        $models[$name] = new $full();
    }
    return $models[$name];
}

function generateSign($vid)
{
    $secretkey = "dQI1zt18A8";
    $ts = time() * 1000;  //10位的秒级时间戳，后面加多3个0，最后为13位的数值
    $hash = md5($secretkey . $vid . $ts);
    return ['ts' => $ts, 'hash' => $hash];
}

function getPolyToken($vid, $userid)
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    }

    $userId = 'c0ffd4aff8';       // polyv 提供的服务器间的通讯验证
    $secretkey = 'dQI1zt18A8';     // polyv 提供的接口调用签名访问的key
    $videoId = $vid;  // 视频对应vid
    $ts = time() * 1000;      // 时间戳
    $viewerIp = $ipaddress;  // 用户 ip
    $viewerId = $userid;      // 自定义用户 id
    $viewerName = 'polyuser';  // 用户昵称
    $extraParams = 'HTML5';  // 自定义参数

    /* 将参数 $userId、$secretkey、$videoId、$ts、$viewerIp、$viewerIp、$viewerId、$viewerName、$extraParams
     按照ASCKII升序 key + value + key + value ... +value 拼接
     */
    $concated = 'extraParams' . $extraParams . 'ts' . $ts . 'userId' . $userId . 'videoId' . $videoId . 'viewerId' . $viewerId . 'viewerIp' . $viewerIp . 'viewerName' . $viewerName;

    // 再首尾加上 secretkey
    $plain = $secretkey . $concated . $secretkey;

    // 取大写MD5
    $sign = strtoupper(md5($plain));

    // 然后将下列参数用post请求  https://hls.videocc.net/service/v1/token 获取 token
    $url = 'https://hls.videocc.net/service/v1/token';
    $data = array('userId' => $userId, 'videoId' => $videoId, 'ts' => $ts, 'viewerIp' => $viewerIp, 'viewerName' => $viewerName, 'extraParams' => $extraParams, 'viewerId' => $viewerId, 'sign' => $sign);

    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    // 获取返回结果的 token, 再传入 playsafe 中播放加密视频
    $token = json_decode($result)->data->token;
    return $token;
}

function formatMoney($price)
{
    return number_format($price / 100, 2);
}

function objToArr($obj)
{
    if (empty($obj)) return [];
    return json_decode(json_encode($obj), true);
}

/**
 * 验证权限
 * @param unknown $tag
 * @return boolean
 */
function authority($tag)
{
    static $actions;
    $user = config('user');
    if ($user['roleid'] == 1) return true;
    if (empty($actions))
        $actions = D('Role')->getActionsByRoleId($user['roleid']);
    return in_array($tag, $actions);
}
