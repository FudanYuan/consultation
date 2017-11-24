<?php
/**
 * 用户角色-控制器
 * author：yzs
 * create：2017.8.15
 */
namespace app\controller;

use app\model\MyException;

class Role extends Common{
    /**
     * 角色列表
     */
    public function index(){
        $list = D('Role')->getList();
        return view('', ['list' => $list]);
    }

    public function getRolesList(){
        $params = input('post.');
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        $cond = [];
        $name = input('post.name', '');
        if($name){
            $cond['name'] = $name;
        }
        $list = D('Role')->getList($cond);
        $page = input('post.current_page',0);
        $per_page = input('post.per_page',0);
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;

        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '角色设置';
        $log['action_descr'] = '查看角色列表';
        //D('OperationLog')->addData($log);

        $this->jsonReturn($ret);
    }
    /**
     * 新建角色
     */
    public function create(){
        $data = input('post.');
        if(!empty($data)){
            $ret = ['error_code' => 0, 'msg' => '创建角色成功'];
            $res = D('Role')->addData($data);
            if(!$res){
                $ret['error_code'] = 1;
                $ret['msg'] = '创建角色失败';
            }

            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '角色设置';
            $log['action_descr'] = '新建角色-' . $data['name'];
            D('OperationLog')->addData($log);

            $this->jsonReturn($ret);
        }
        return view('', []);
    }
    /**
     * 编辑角色
     */
    public function edit(){
        $data = input('post.');
        if(!empty($data)){
            $ret = ['error_code' => 0, 'msg' => '编辑角色成功'];
            $res = D('Role')->saveData($data['id'], $data);
            $ret['res'] = $res;
            if(!$res){
                $ret['error_code'] = 1;
                $ret['msg'] = '编辑角色失败';
            }

            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '角色设置';
            $log['action_descr'] = '编辑角色-' . $data['id'];
            D('OperationLog')->addData($log);

            $this->jsonReturn($ret);
        }
        $role_id = input('get.id');
        $role = D('Role')->getById($role_id);
        return view('', ['role' => $role]);
    }
    /**
     * 批量删除
     */
    public function remove(){
        $ret = ['error_code' => 0, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            $res = D('Role')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['error_code'] = 1;
            $ret['msg'] = '删除失败';
        }

        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '角色设置';
        $log['action_descr'] = '删除角色-' . $ids;
        D('OperationLog')->addData($log);
        $this->jsonReturn($ret);
    }
}
?>