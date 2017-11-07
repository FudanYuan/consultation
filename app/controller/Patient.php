<?php
/**
 * 患者信息--控制器
 * Created by
 * time 2017.10.19
 */
namespace app\controller;

class Patient extends Common
{
    public $exportCols = [];
    public $colsText = [];

    /**
     * 患者信息
     * @return mixed
     */
    public function index()
    {
        return view('', []);
    }

    /**
     * 获取病人信息根据身份证号
     */
    public function getPatientByIDNum(){
        $params = input('post.');
        $ID_number = input('post.ID_number');
        $ret = ['error_code' => 0, 'msg' =>''];
        $patient_data = D('Patient')->getPatientByIdNum($ID_number);
        if(empty($patient_data)){
            $ret['error_code'] = 2;
            $ret['msg'] = '未找到这名患者';
        }else{
            $ret['patient'] = $patient_data;
        }
        $ret['id_number'] = $params;
        $this->jsonReturn($ret);
    }

    /**
     * 获取患者信息列表
     */
    public function getPatientList(){
        $params = input('post.');
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
//        $user_id = $this->getUserId();
        $cond = [];
//        $cond['hospital_id'] = ['=', $user_id];
        $list = D('Patient')->getList($cond);
        $page = input('post.current_page',0);
        $per_page = input('post.per_page',0);
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;
        $this->jsonReturn($ret);
    }


    ///////////未修改////
    /**
     * 删除患者信息
     */
    public function remove(){
        $ret = ['code' => 1, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            $res = D('Patient')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['code'] = 2;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 新建患者信息
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
                // 添加Patient
                $res_apply = D('Patient')->addAllData($dataSet);
                if (!empty($res_apply['errors'])) {
                    $ret['code'] = 2;
                    $ret['msg'] = '新建失败';
                    $ret['errors'] = $res_apply['errors'];
                    $this->jsonReturn($ret);
                }
                $log['user_id'] = $this->getUserId();
                $log['IP'] = $this->getUserIp();
                $log['section'] = '患者信息';
                $log['action_descr'] = '添加患者信息';
                D('OperationLog')->addData($log);
                $this->jsonReturn($ret);
            }
            else{
                $data['target_user_id'] = '';
                // 添加Patient
                $res_apply = D('Patient')->addData($data);
                if (!empty($res_apply['errors'])) {
                    $ret['code'] = 2;
                    $ret['msg'] = '新建失败';
                    $ret['errors'] = $res_apply['errors'];
                }
                $this->jsonReturn($ret);
            }

        }
        $office = [];
        $office[0] = ['id' => 1, 'name' => '骨科'];
        $office[1] = ['id' => 2, 'name' => '眼科'];
        return view('', ['office' => $office]);
    }
}