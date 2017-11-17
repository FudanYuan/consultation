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
        $select = ['*'];
        $cond = [];
        $cond['a.id'] = ['=', $account_id];
        $account = D('UserAdmin')->getUserAdmin($select, $cond);
        $cond = [];
        $cond['id'] = ['<>', $account_id];
        $users = D('UserAdmin')->getList($cond);
        if(!empty($data)){
            $apply_id = input('post.apply_id');
            $apply = D('Apply')->getById($apply_id);
            $user_id_post = input('post.user_id');
            return view('', ['users' => $users, 'account' => $account, 'apply' => $apply_id, 'target_user_id' => $user_id_post]);
        }
//        mydump($account_id);
//        mydump($account);
//        mydump($users);
        return view('', ['users' => $users, 'account' => $account, 'target_user_id' => $users[0]['id']]);
    }

    /**
     * 获取消息列表
     */
    public function getCommunicationList(){
        $params = input('post.');
        // 获取当前登陆的用户id，根据此id查询表，返回结果
        $user_id = $this->getUserId();
        $page = input('post.current_page',0);
        $per_page = input('post.per_page',0);
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        $cond['target_user_id'] = ['=', $user_id];
        $list = D('Communication')->getList($cond);
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;
        $this->jsonReturn($ret);
    }

    /**
     * 删除消息
     */
    public function remove(){
        $ret = ['code' => 1, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            $res = D('Communication')->remove(['id' => ['in', $ids]]);
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
        $ret = ['code' => 1, 'msg' => '标记成功'];
        $ids = input('post.ids');
        try{
            $res = D('Communication')->markRead(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['code'] = 2;
            $ret['msg'] = '标记失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 发送消息
     */
    public function send(){
        $params = input('post.');
        $target_user_id = input('post.target_user_id', -1);
        $source_user_id = input('post.source_user_id', -1);
        $ret = ['error_code' => 0, 'msg' => '发送成功'];
        /**
         * 发送逻辑
         */
        $this->jsonReturn($ret);
    }

    /**
     * 接收消息
     */
    public function receive(){
        $params = input('post.');
        $source_user_id = input('post.source_user_id', -1);
        $target_user_id = input('post.target_user_id', -1);
        $page = input('post.current_page',0);
        $per_page = input('post.per_page',0);
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        $cond['source_user_id | target_user_id'] = ['=', $source_user_id];
        $cond['source_user_id | target_user_id'] = ['=', $target_user_id];
        $cond['status'] = ['=', 0];
        $list = D('Chat')->getList($cond);
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;
        $this->jsonReturn($ret);
    }

    /**
     * 接收新消息
     */
    public function newInform(){

    }
}