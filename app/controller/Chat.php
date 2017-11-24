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
            $cond_and['a.status'] = ['>=', 2];
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

        return view('', ['error' => '', 'apply_id' => $apply_id, 'source_user_id' => $user_id, 'target_user_id' => $target_user_id,]);
    }

    /**
     * 获取聊天列表
     */
    public function getChatList(){
        $params = input('post.');
        $keywords = input('post.search','');
        $ret = ['error_code' => 0, 'msg' => '加载成功'];

        $cond_or = [];
        if($keywords){
            $cond_or['f.name|h.name|i.name|j.name|l.name|m.name'] = ['like','%'.myTrim($keywords).'%'];
        }
        // 获取所有的会诊申请记录
        $select = ['b.id as apply_id, 
        a.status as status, 
        count(*) as count, 
        b.source_user_id as source_user_id,
        c.username as source_user_name,
        c.logo as source_user_logo,
        f.id as source_doctor_id,
        f.name as source_doctor_name,
        f.photo as source_doctor_photo,
        h.id as source_hospital_id, 
        h.name as source_hospital_name, 
        h.logo as source_hospital_logo, 
        i.id as source_office_id,
        i.name as source_office_name,
       
        d.target_user_id as target_user_id,
        e.username as target_user_name,
        e.logo as target_user_logo,
        j.id as target_doctor_id,
        j.photo as target_doctor_photo,
        j.name as target_doctor_name, 
        l.id as target_hospital_id,
        l.name as target_hospital_name, 
        l.logo as target_hospital_logo'];
        $cond_and = [];
        $user_id = $this->getUserId();
        $cond_and['a.target_user_id'] = $user_id;
        $cond_and['a.status'] = ['<>', 2];
        $cond_and['b.status'] = ['<', 3];
        $cond_and['b.is_green_channel'] = 0;
        $normal = D('Chat')->getUserList($select,$cond_or,$cond_and,'a.apply_id, a.status');
        $cond_and['b.is_green_channel'] = 1;
        $green = D('Chat')->getUserList($select,$cond_or,$cond_and,'a.apply_id, a.status');

        $normal_info = [];
        for($i=0;$i<count($normal);$i++){
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

        // 根据apply_id合并
        $normal_ret = [];
        $normal_apply_ids = [];
        for($i=0; $i<count($normal_info);$i++){
            $apply_id_temp = $normal_info[$i]['apply_id'];
            $status_temp = $normal_info[$i]['status'];
            $normal_info[$i]['count'] = $status_temp == 0 ? $normal_info[$i]['count'] : 0;
            if(!in_array($apply_id_temp, $normal_apply_ids)){
                array_push($normal_apply_ids, $apply_id_temp);
                array_push($normal_ret, $normal_info[$i]);
            } else{
                $index = array_search($apply_id_temp, $normal_apply_ids);
                $normal_ret[$index]['count'] += $normal_info[$i]['count'];
            }
        }

        $green_ret = [];
        $green_apply_ids = [];
        for($i=0; $i<count($green_info);$i++){
            $apply_id_temp = $green_info[$i]['apply_id'];
            $status_temp = $green_info[$i]['status'];
            $green_info[$i]['count'] = $status_temp == 0 ? $green_info[$i]['count'] : 0;
            if(!in_array($apply_id_temp, $green_apply_ids)){
                array_push($green_apply_ids, $apply_id_temp);
                array_push($green_ret, $green_info[$i]);
            } else{
                $index = array_search($apply_id_temp, $green_apply_ids);
                $green_ret[$index]['count'] += $green_info[$i]['count'];
            }
        }
        $ret['normal'] = $normal_ret;
        $ret['green'] = $green_ret;
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
        /**
         * 发送逻辑
         */
        unset($data['target_user_id']);
        $dataSet = [];
        $target_user_id = explode('-',$target_user_id);
        foreach($target_user_id as $item){
            $data['target_user_id'] = $item;
            array_push($dataSet, $data);
        }
        $ret['target'] = $dataSet;
        $res = D('Chat')->addAllData($dataSet);
        if(!empty($res['errors'])){
            $ret['error_code'] = 1;
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
        $source_user_id = input('post.source_user_id', -1);
        $target_user_id = input('post.target_user_id', -1);
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        if($request_new == 0){
            $page = input('post.current_page',0);
            $per_page = input('post.per_page',0);
            $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
            $cond['apply_id'] = $apply_id;
            $cond['status'] = ['<>', 2];
            $list = D('Chat')->getList($cond);

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
                $cond['apply_id'] = $apply_id;
                $cond['target_user_id'] = $source_user_id;
                $cond['status'] = 0;
                $list = D('Chat')->getList($cond);
                if (count($list) > 0) { // 如果有新的消息，则返回数据信息
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