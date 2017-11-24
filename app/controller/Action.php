<?php
/**
 * 操作权限--控制器
 * Created by Jeremy
 * time 2017.10.19
 */
namespace app\controller;

class Action extends Common
{
    /**
     * 操作权限
     * @return \think\response\View
     */
    public function index()
    {
        return view('', []);
    }

    /**
     * 获取权限
     */
    public function authority(){
        $actions = D('Action')->getActions();
        $this->jsonReturn($actions);
    }

    /**
     * 新建操作权限
     */
    public function create(){
        $params = input('post.');
        $ret = ['error_code' => 0, 'msg' => '导入成功'];
        $res = D('JSON')->import($params);
        if(!empty($res['errors'])){
            $ret['errors'] = $res['errors'];
            $ret['error_code'] = 1;
            $ret['msg'] = '导入失败';
        }else{
            $data = $res['data'];
            $ret['data'] = $data;
            // 添加权限
//            $res_action = D('Action')->addAllData($data);
//            $ret['count'] = $res_action['i'];
//            $ret['result'] = $res_action['result'];
//            $ret['exception'] = $res_action['exception'];
//            if (!empty($res_action['errors'])) {
//                $ret['code'] = 2;
//                $ret['msg'] = '新建失败';
//                $ret['errors'] = $res_action['errors'];
//                $this->jsonReturn($ret);
//            }
//            $log['user_id'] = $this->getUserId();
//            $log['IP'] = $this->getUserIp();
//            $log['section'] = '权限设置';
//            $log['action_descr'] = '新建权限列表';
//            D('OperationLog')->addData($log);
            $this->jsonReturn($ret);
        }
    }

    /**
     * 清空权限表
     */
    public function remove(){
        $ret = ['error_code' => 0, 'msg' => '清空成功'];
        try{
            $res = D('Action')->remove();
        }catch(MyException $e){
            $ret['error_code'] = 1;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 导入json
     */
    public function import(){
        try{
            $res = D('Action')->remove();
        }catch(MyException $e){
            $ret['error_code'] = 1;
            $ret['msg'] = '删除失败';
            $this->jsonReturn($ret);
        }
        $params = input('post.');
        $ret = ['error_code' => 0, 'msg' => '导入成功'];
        $res = D('JSON')->import($params);
        if(!empty($res['errors'])){
            $ret['errors'] = $res['errors'];
            $ret['error_code'] = 1;
            $ret['msg'] = '导入失败';
        }else{
            $data = $res['data'];
            $ret['data'] = $data;
            // 添加权限
            $res_action = D('Action')->addAllData($data);
            if (!empty($res_action['errors'])) {
                $ret['error_code'] = 1;
                $ret['msg'] = '新建失败';
                $ret['errors'] = $res_action['errors'];
                $this->jsonReturn($ret);
            }
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '权限设置';
            $log['action_descr'] = '导入权限文件';
            D('OperationLog')->addData($log);
            $this->jsonReturn($ret);
        }
    }
}