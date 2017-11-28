<?php
/**
 * 消息--控制器
 * Created by shiren.
 * time 2017.10.19
 */
namespace app\controller;

class Chat extends Common
{
    public $exportCols = [];
    public $colsText = [];

    /**
     * 消息
     * @return \think\response\View
     */
    public function index(){
        $data = input('get.');
        $apply_id = input('get.id', '');
        // 获取当前userId
        $user_id = $this->getUserId();
        $select = ['a.id as id, a.source_user_id as source_user_id, 
            c.target_user_id as target_user_id'];
        $cond_and = [];
        if(!$apply_id){ // 默认打开的apply_id
            $cond_and['a.status'] = 2;
            $cond_and['a.source_user_id | c.target_user_id'] = $user_id;
            $apply_info = D('Apply')->getList($select, [], $cond_and, ['a.create_time desc']);
            if(count($apply_info) > 0){
                $apply_id = $apply_info[0]['id'];
            } else {
                return view('', ['error' => '您的会话记录为空，赶紧发起申请，激烈讨论吧!']);
            }
        }

        $cond_and = [];
        $cond_and['c.apply_id'] = $apply_id;
        $apply_info = D('Apply')->getList($select, [], $cond_and, ['a.create_time desc']);

        // 合并相同apply_id
        $apply_ids = [];
        $list = [];
        for($i=0;$i<count($apply_info);$i++){
            $apply_id_temp = $apply_info[$i]['id'];
            if(!in_array($apply_id_temp, $apply_ids)){
                array_push($apply_ids, $apply_id_temp);
                if($user_id == (int)$apply_info[$i]['target_user_id']){
                    $apply_info[$i]['target_user_id'] = '0';
                }
                array_push($list, $apply_info[$i]);
            } else{
                $index = array_search($apply_id_temp, $apply_ids);
                if($user_id != (int)$apply_info[$i]['target_user_id']){
                    $list[$index]['target_user_id'] .= '-'.$apply_info[$i]['target_user_id'];
                }
            }
        }

        $source_user_id = $list[0]['source_user_id'];
        $list[0]['target_user_id'] = str_replace('0-', '', $list[0]['target_user_id']);
        if($user_id == $source_user_id){
            $target_user_id = $list[0]['target_user_id'];
        }
        else{
            if($list[0]['target_user_id']){
                $target_user_id = $source_user_id . '-'. $list[0]['target_user_id'];
            } else{
                $target_user_id = $source_user_id;
            }
        }

        $select = [
            'a.id as id,
            a.logo as logo,
            b.name as name'];
        $user_info = D('UserAdmin')->getUserAdmin($select, ['a.id' => $user_id]);
        $chat_user = [
            'id' => $user_info[0]['id'],
            'logo' => $user_info[0]['logo'],
            'name' => $user_info[0]['name'],
        ];

//        $list = D('Chat')->getChatHistory('', '', $apply_id, $user_id);
//        mydump($list);

        return view('', ['error' => '', 'apply_id' => $apply_id, 'source_user_id' => $user_id, 'target_user_id' => $target_user_id, 'chat_user' => $chat_user]);
    }

    /**
     * 获取聊天列表
     * 1. 构建讨论组。
     * step1: 获取正在处理的会诊申请或绿色通道申请（apply.status=2）的apply_id
     * step2: 根据apply_id,得到讨论组的成员信息，即 source_user_id 及 target_user_id（可能多个）
     *
     * 2. 获取未读信息数量
     * step3: 根据 status=0 获取未读消息的数量
     *
     */
    public function getChatList(){
        $params = input('post.');
        $keywords = input('post.search','');
        $ret = ['error_code' => 0, 'msg' => '加载成功'];

        $cond_or = [];
        if($keywords){
            $cond_or['e.name|g.name|l.name|h.name|j.name|m.name'] = ['like','%'.myTrim($keywords).'%'];
        }

        // 获取所有的会诊申请记录
        $select = ['a.id as apply_id,
        a.source_user_id as source_user_id,
        b.username as source_user_name,
        b.logo as source_user_logo,
        e.id as source_doctor_id,
        e.name as source_doctor_name,
        e.photo as source_doctor_photo,
        g.id as source_hospital_id,
        g.name as source_hospital_name,
        g.logo as source_hospital_logo,
        f.id as source_office_id,
        l.name as source_office_name,

        c.target_user_id as target_user_id,
        d.username as target_user_name,
        d.logo as target_user_logo,
        h.id as target_doctor_id,
        h.photo as target_doctor_photo,
        h.name as target_doctor_name,
        j.id as target_hospital_id,
        j.name as target_hospital_name,
        j.logo as target_hospital_logo,
        i.id as target_office_id,
        m.name as target_office_name'];

        $cond_and = [];
        $user_id = $this->getUserId();
        $cond_and['a.source_user_id | c.target_user_id'] = $user_id;
        $cond_and['a.status'] = 2;
        $cond_and['a.is_green_channel'] = 0;
        $normal = D('Apply')->getList($select,$cond_or,$cond_and);
        $cond_and['a.is_green_channel'] = 1;
        $green = D('Apply')->getList($select,$cond_or,$cond_and);

        $normal_info = [];
        for($i=0;$i<count($normal);$i++){
            $normal[$i]['count'] = 0;
            if($normal[$i]['source_user_id'] == $user_id){ // 如果当前用户是提出申请一方并且会诊医生有多个时，直接显示会诊医院的logo
                $normal_info_temp = json_decode($normal[$i]);
                foreach ($normal_info_temp as $k => $v){
                    if(strpos($k, 'target_') !== false){
                        $k = str_replace('target_', '', $k);
                        $normal_info[$i][$k] = $v;
                    } else if(strpos($k, 'source_') !== false){
                        continue;
                    } else {
                        $normal_info[$i][$k] = $v;
                    }
                }
            } else{ // 如果当前用户不是提出申请一方时，直接显示申请医生的信息
                $normal_info_temp = json_decode($normal[$i]);
                foreach ($normal_info_temp as $k => $v){
                    if(strpos($k, 'source_') !== false){
                        $k = str_replace('source_', '', $k);
                        $normal_info[$i][$k] = $v;
                    } else if(strpos($k, 'target_') !== false){
                        continue;
                    } else {
                        $normal_info[$i][$k] = $v;
                    }
                }
            }
        }

        $green_info = [];
        for($i=0;$i<count($green);$i++){
            $green[$i]['count'] = 0;
            if($green[$i]['source_user_id'] == $user_id){ // 如果当前用户是提出申请一方并且会诊医生有多个时，直接显示会诊医院的logo
                $green_info_temp = json_decode($green[$i]);
                foreach ($green_info_temp as $k => $v){
                    if(strpos($k, 'target_') !== false){
                        $k = str_replace('target_', '', $k);
                        $green_info[$i][$k] = $v;
                    } else if(strpos($k, 'source_') !== false){
                        continue;
                    } else {
                        $green_info[$i][$k] = $v;
                    }
                }
            } else{ // 如果当前用户不是提出申请一方时，直接显示申请医生的信息
                $green_info_temp = json_decode($green[$i]);
                foreach ($green_info_temp as $k => $v){
                    if(strpos($k, 'source_') !== false){
                        $k = str_replace('source_', '', $k);
                        $green_info[$i][$k] = $v;
                    } else if(strpos($k, 'target_') !== false){
                        continue;
                    } else {
                        $green_info[$i][$k] = $v;
                    }
                }
            }
        }

        // 根据apply_id得到聊天记录
        $normal_apply_ids = [];
        for($i=0; $i<count($normal_info);$i++){
            $apply_id_temp = $normal_info[$i]['apply_id'];
            if(!in_array($apply_id_temp, $normal_apply_ids)){
                array_push($normal_apply_ids, $apply_id_temp);
            }
        }

        $green_apply_ids = [];
        for($i=0; $i<count($green_info);$i++){
            $apply_id_temp = $green_info[$i]['apply_id'];
            if(!in_array($apply_id_temp, $green_apply_ids)){
                array_push($green_apply_ids, $apply_id_temp);
            }
        }

        $select = ['a.apply_id as apply_id, 
        g.status as status, 
        count(*) as count'];
        $cond_and = [];
        $cond_or = [];
        $normal_apply_ids_implode = implode($normal_apply_ids, ',');
        $cond_and['a.apply_id'] = ['in', $normal_apply_ids_implode];
        $cond_and['g.target_user_id'] = $user_id;
        $cond_and['a.status'] = ['<>', 2];
        $cond_and['g.status'] = 0;
        $normal_chat = D('Chat')->getList($select,$cond_or,$cond_and,'a.apply_id, g.status');

        $green_apply_ids_implode = implode($green_apply_ids, ',');
        $cond_and['a.apply_id'] = ['in', $green_apply_ids_implode];
        $green_chat = D('Chat')->getList($select,$cond_or,$cond_and,'a.apply_id, g.status');

        $normal_chat_count = count($normal_chat);
        if($normal_chat_count > 0){
            for($i=0;$i<$normal_chat_count;$i++){
                $apply_id_temp = $normal_chat[$i]['apply_id'];
                $index = array_search($apply_id_temp,$normal_apply_ids);
                $normal_info[$index]['count'] = $normal_chat[$i]['count'];
            }
        }

        $green_chat_count = count($green_chat);
        if($green_chat_count > 0){
            for($i=0;$i<$green_chat_count;$i++){
                $apply_id_temp = $green_chat[$i]['apply_id'];
                $index = array_search($apply_id_temp,$green_apply_ids);
                $green_info[$index]['count'] = $green_chat[$i]['count'];
            }
        }

        $ret['normal'] = $normal_info;
        $ret['green'] = $green_info;
        $this->jsonReturn($ret);
    }

    /**
     * 删除消息
     */
    public function remove(){
        $ret = ['error_code' => 0, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            $res = D('Chat')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['error_code'] = 1;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 标为已读
     */
    public function markRead(){
        $ret = ['error_code' => 0, 'msg' => '标记成功'];
        $ids = input('post.ids');
        try{
            $res = D('Chat')->markRead(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['error_code'] = 1;
            $ret['msg'] = '标记失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 发送消息
     *
     * 发送逻辑
     * step 1: 判断$target_user_id是否为空，如果为空，则代表非私聊模式；否则为私聊模式
     * step 2: 保存数据至数据库
     */
    public function send(){
        $data = input('post.');
        $apply_id = input('post.apply_id', '');
        $target_user_id = input('post.target_user_id', '');
        $source_user_id = input('post.source_user_id', '');
        $type = input('post.type', '');
        $content = input('post.content', '');
        $content_origin = input('post.content_origin', '');
        $ret = ['error_code' => 0, 'msg' => '发送成功'];
        $data['target_user_id'] = explode("-", $target_user_id);
        $res = D('Chat')->addData($data);
        if(!empty($res['errors'])){
            $ret['error_code'] = 1;
            $ret['msg'] = '发送失败';
            $ret['errors'] = $res['errors'];
        }
        $this->jsonReturn($ret);
    }

    /**
     * 接收消息
     */
    public function receive(){
        $params = input('post.');
        $request_new = input('post.request_time', 0);
        $apply_id = input('post.apply_id', -1);
        $user_id = $this->getUserId();
        $source_user_id = input('post.source_user_id', -1);
        $target_user_id = input('post.target_user_id', -1);
        $ret = ['error_code' => 0, 'data' => [], 'msg' => "接收成功"];
        if($request_new == 0){
            $page = input('post.current_page',0);
            $per_page = input('post.per_page',0);
            $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
            $select = ['a.id as chat_id,
                g.id as id,
                a.source_user_id as source_user_id,
                g.target_user_id as target_user_id,
                a.type as type,
                a.content as content,
                a.content_origin as content_origin,
                a.create_time as create_time,
                g.status as status,
                c.id as doctor_id,
                c.name as doctor_name,
                c.photo as doctor_logo,
                c.phone as doctor_phone,
                c.email as doctor_email,
                e.id as hospital_id,
                e.name as hospital_name,
                d.id as hospital_office_id,
                f.name as office_name'];
            $cond_and = [];
            $cond_and['a.apply_id'] = $apply_id;
            $cond_and['a.source_user_id | g.target_user_id'] = $user_id;
            $result = D('Chat')->getList($select, [], $cond_and);

            $list = [];
            array_push($list, $result[0]);
            for($i=1;$i<count($result);$i++){
                if($result[$i]['chat_id'] != $result[$i-1]['chat_id']){
                    array_push($list, $result[$i]);
                }
            }
            // $list = D('Chat')->getChatHistory('', '', $apply_id, $user_id);

            //分页时需要获取记录总数，键值为 total
            $ret["total"] = count($list);
            //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置,
            //取最后一页
            if($page == 0){
                $page = ceil(count($list) / $per_page);
                $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
            } else{
                $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
            }
            $ret['current_page'] = $page;
            $this->jsonReturn($ret);
        } else{
            date_default_timezone_set("PRC");
            set_time_limit(0);//无限请求超时时间
            while (true) {
                $select = ['g.id as id,
                    a.source_user_id as source_user_id,
                    g.target_user_id as target_user_id,
                    a.type as type,
                    a.content as content,
                    a.content_origin as content_origin,
                    a.create_time as create_time,
                    g.status as status,
                    c.id as doctor_id,
                    c.name as doctor_name,
                    c.photo as doctor_logo,
                    c.phone as doctor_phone,
                    c.email as doctor_email,
                    e.id as hospital_id,
                    e.name as hospital_name,
                    d.id as hospital_office_id,
                    f.name as office_name'];
                $cond_and = [];
                $cond_and['a.status'] = 0;
                $cond_and['a.apply_id'] = $apply_id;
                $cond_and['a.source_user_id'] = ['<>', $user_id];
                $cond_and['g.target_user_id'] = $user_id;
                $result = D('Chat')->getList($select, [], $cond_and);
                if (count($result) > 0) { // 如果有新的消息，则返回数据信息
                    $list = [];
                    array_push($list, $result[0]);
                    for($i=1;$i<count($result);$i++){
                        if($result[$i]['chat_id'] != $result[$i-1]['chat_id']){
                            array_push($list, $result[$i]);
                        }
                    }
                    $ret["data"] = $list;
                } else { // 模拟没有数据变化，将休眠 hold住连接
                    sleep(10);
                }
                $this->jsonReturn($ret);
                exit();
            }
        }
    }
}