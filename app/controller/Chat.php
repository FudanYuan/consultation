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
        $source_user_id = $this->getUserId();
        $select = ['*'];
        $cond = [];
        $cond['a.id'] = ['=', $source_user_id];
        $account = D('UserAdmin')->getUserAdmin($select, $cond);
        $select = ['*'];
        $cond = [];
        $cond['a.id'] = ['<>', $source_user_id];
        $users = D('UserAdmin')->getUserAdmin($select, $cond);

        if(!empty($data)){
            $apply_id = input('post.apply_id');
            $apply = D('Apply')->getById($apply_id);
            $user_id_post = input('post.user_id');
            return view('', ['users' => $users, 'account' => $account, 'apply' => $apply_id, 'target_user_id' => $user_id_post]);
        }
        return view('', ['users' => $users, 'account' => $account, ]);
    }

    /**
     * 聊天界面
     * @return \think\response\View
     */
    public function chat(){
        $id = input('get.id', -1);
        return view('', ['id' => $id]);
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
        $ret = ['errorcode' => 0, 'data' => [], 'msg' => ""];
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
     * 新建
     */
    public function create(){
        $params = input('post.');
        $cond = [];
        $cond['id'] = ['<>', $this->getUserId()];
        $target_users = D('UserAdmin')->getList($cond);
        if(!empty($params)) {
            $data = [];
            $ret = ['code' => 1, 'msg' => '新建成功'];
            $title = input('post.title', '');
            $priority = input('post.priority', '');
            if (!isset($params['target_user_ids'])) {
                $params['target_user_ids'] = [];
            }
            if (!isset($params['content'])){
                $params['content'] = '';
            }

            $data['source_user_id'] = $this->getUserId();
            $data['title'] = $title;
            $data['content'] = $params['content'];
            $data['operation'] = '查看';
            $data['priority'] = (int)$priority;
            $data['status'] = 0;

            $dataSet = [];
            if(!empty($params['target_user_ids'])){
                for($i=0;$i<count($params['target_user_ids']);$i++){
                    $data['target_user_id'] = (int)$params['target_user_ids'][$i];
                    array_push($dataSet, $data);
                }
                // 添加Communication
                $res_inform = D('Communication')->addAllData($dataSet);
                if (!empty($res_inform['errors'])) {
                    $ret['code'] = 2;
                    $ret['msg'] = '新建失败';
                    $ret['errors'] = $res_inform['errors'];
                    $this->jsonReturn($ret);
                }
                $log['user_id'] = $this->getUserId();
                $log['IP'] = $this->getUserIp();
                $log['section'] = '消息';
                $log['action_descr'] = '新建通知';
                D('OperationLog')->addData($log);
            }
            else{
                $data['target_user_id'] = '';
                // 添加Communication
                $res_inform = D('Communication')->addData($data);
                if (!empty($res_inform['errors'])) {
                    $ret['code'] = 2;
                    $ret['msg'] = '新建失败';
                    $ret['errors'] = $res_inform['errors'];
                }
            }
            $ret['dataSet'] = $dataSet;
            $this->jsonReturn($ret);
        }
        return view('', ['target_users' => $target_users]);
    }
}