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
        // 获取当前userId
        $account_id = $this->getUserId();
        $select = ['a.id as id'];
        $cond = ['a.id' => $account_id];
        $account = D('UserAdmin')->getUserAdmin($select, $cond);
        $cond = [];
        $cond['id'] = ['<>', $account_id];
        $users = D('UserAdmin')->getList($cond);

        if(!empty($data)){
            $apply_id = input('post.apply_id');
            $apply = D('Apply')->getById($apply_id);
            $user_id_post = input('post.user_id');
            return view('', ['users' => $users, 'account' => $account, 'apply' => $apply, 'target_user_id' => $user_id_post]);
        }
        return view('', ['users' => $users, 'account' => $account, 'target_user_id' => $users[0]['id']]);
    }

    /**
     * 获取聊天列表
     */
    public function getChatList(){
        $params = input('post.');
        $keywords = input('post.search','');
        $ret = ['error_code' => 0, 'msg' => '加载成功'];
        $user_id = $this->getUserId();
        $cond_or = [];
        if($keywords){
            $cond_or['d.name|f.name|g.name'] = ['like','%'.myTrim($keywords).'%'];
        }
        $cond_and['b.is_green_channel'] = 0;
        $cond_and['a.target_user_id'] = $user_id;
        $select = ['c.id as user_id,d.id as doctor_id,d.name as doctor_name,f.id as hospital_id,
                    f.name as hospital_name,g.name as office, d.photo as logo,b.id as apply_id,
                    count(a.id) as count'];
        $normal =  D('Chat')->getList($select,$cond_or,$cond_and);
        $cond_and['b.is_green_channel'] = 1;
        $green =  D('Chat')->getList($select,$cond_or,$cond_and);
        $ret['normal'] = $normal;
        $ret['green'] = $green;
        $this->jsonReturn($ret);
    }

    /**
     * 删除消息
     */
    public function remove(){
        $ret = ['code' => 1, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            $res = D('Chat')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['code'] = 2;
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
        $target_user_id = input('post.target_user_id', '');
        $source_user_id = input('post.source_user_id', '');
        $type = input('post.type', '');
        $content = input('post.content', '');
        $content_origin = input('post.content_origin', '');
        $ret = ['error_code' => 0, 'msg' => '发送成功'];
        /**
         * 发送逻辑
         */
        $res = D('Chat')->addData($data);
        if(!empty($res['errors'])){
            $ret['error_code'] = 2;
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
        $source_user_id = input('post.source_user_id', -1);
        $target_user_id = input('post.target_user_id', -1);
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        if($request_new == 0){
            $page = input('post.current_page',0);
            $per_page = input('post.per_page',0);
            $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
            $cond['source_user_id | target_user_id'] = ['=', $source_user_id];
            $cond['source_user_id | target_user_id'] = ['=', $target_user_id];
            $cond['status'] = ['<>', 2];
            $list = D('Chat')->getList($cond);

            //分页时需要获取记录总数，键值为 total
            $ret["total"] = count($list);
            //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
            $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
            $ret['current_page'] = $page;
            $this->jsonReturn($ret);
        } else{
            date_default_timezone_set("PRC");
            set_time_limit(0);//无限请求超时时间
            while (true) {
                $cond['source_user_id'] = ['=', $source_user_id];
                $cond['target_user_id'] = ['=', $target_user_id];
                $cond['status'] = ['=', 0];
                $list = D('Chat')->getList($cond);
                if (count($list) > 0) { // 如果有新的消息，则返回数据信息
                    $ret["data"] = $list;
                    $this->jsonReturn($ret);
                    exit();
                } else { // 模拟没有数据变化，将休眠 hold住连接
                    sleep(10);
                    exit();
                }
            }
        }
    }
}