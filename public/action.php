<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/8/18
 * Time: 13:26
 */

// 从文件中读取数据到PHP变量
$json_string = file_get_contents('action_role.json');

// 把JSON字符串转成PHP数组
$data = json_decode($json_string, true);

// 显示出来看看
for($i=0; $i<count($data);$i++){
    $name = $data[$i]['name'];
    $tag = $data[$i]['tag'];
    $pid = $data[$i]['pid'];
    $pids = $data[$i]['pids'];
    $level = $data[$i]['level'];
    $status = 1;
    $createtime	= time();
    $sql = "insert into lab_action_admin(name, tag, pid, pids, level, status, createtime) values('".$name."','". $tag."',".
        $pid.",'". $pids."',". $level.",". $status.",". $createtime.");" . '<br/>';
    echo $sql;
}
