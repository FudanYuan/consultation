<?php
/**
 * 医生信息--控制器
 * Created by shiren.
 * time 2017.10.19
 */
namespace app\controller;

class Doctor extends Common
{
    public $exportCols = [];
    public $colsText = [];

    /**
     * 医生信息
     * @return \think\response\View
     */
    public function index()
    {
        return view('', []);
    }

    /**
     * 获取医生信息列表
     */
    public function getDoctorList(){
        $params = input('post.');
        $hospital = input('post.hospital','');
        $name = input('post.name','');
        $office = input('post.office','');
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        $cond_and = [];
        $cond_or =[];
        if($hospital){
            $cond_or['c.name'] = ['like','%'.myTrim($hospital).'%'];
        }
        if($office){
            $cond_or['d.name'] = ['like','%'.myTrim($office).'%'];
        }
        if($name){
            $cond_or['a.name'] = ['like','%'.myTrim($name).'%'];
        }

        // 如果有 看到所有医生信息 的权限的话，直接返回，否则只能看到本医院的医生
        if(!authority('DoctorAll')){
            // 获取当前登陆的用户id，根据此id查询表，返回结果
            $user_id = $this->getUserId();
            $doctor_info = D('UserAdmin')->getById($user_id);
            $doctor_id = $doctor_info['doctor_id'];
            $doctor_info = D('Doctor')->getById($doctor_id);
            $hospital_office_id = $doctor_info['hospital_office_id'];
            $hospital_office = D('HospitalOffice')->getById($hospital_office_id);
            $hospital_id = $hospital_office['hospital_id'];
            $hospital_ids = [];
            $hospital_infos = D('Hospital')->getHospital(['id'], ['role' => 1]);
            foreach ($hospital_infos as $item){
                array_push($hospital_ids, $item['id']);
            }
            $cond_or['c.id'] = ['in', $hospital_ids];
        }
        $ret['res'] = authority('DoctorAll');
        $list = D('Doctor')->getDoctorList($cond_or,$cond_and,[]);

        $page = input('post.current_page',0);
        $per_page = input('post.per_page',0);
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;
        $this->jsonReturn($ret);
    }

    /**
     * 删除医生信息
     */
    public function remove(){
        $ret = ['error_code' => 0, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            $res = D('Doctor')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['error_code'] = 1;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 新建医生信息
     */
    public function create(){
        $params = input('post.');
        $cond = [];
        $cond['id'] = ['<>', $this->getUserId()];
        if(!empty($params)) {
            $ret = ['error_code' => 0, 'msg' => '新建成功'];
            $ret['params'] = $params;
            $office_id = input('post.hospital_office_id');
            $user_id = $this->getUserId();
            $select = ['d.id as hospital_id'];
            $cond = ['a.id' => $user_id];
            $hospital_id = D('UserAdmin')->getUserAdmin($select,$cond);
            $hospital_office_id = D('HospitalOffice')->getIdByHospitalOffice(['id'],['hospital_id'=>$hospital_id[0]['hospital_id'],'office_id'=>$office_id]);
            $params['hospital_office_id'] = $hospital_office_id['id'];
            $res = D('Doctor')->addData($params);
            if(!empty($res['errors'])) {
                $ret['error_code'] = 1;
                $ret['msg'] = '新建失败';
                $ret['errors'] = $res['errors'];
            }
            $ret['params'] = $params;
            $this->jsonReturn($ret);
        }
        $select = ['id,name'];
        $hospital = D('Hospital')->getHospital($select,[]);
        $office = D('Office')->getOffice($select,[]);
        return view('', ['office' => $office,'hospital' =>$hospital]);
    }


    /**
     * 获取医生信息
     */
    function info(){
        $id = input('get.id');
        return view('', ['id' => $id]);
    }

    /**
     * 获取医生详情
     */
    public function getDoctorInfo(){
        $id = input('post.id');
        $ret = ['error_code' => 0, 'msg' => ''];
        $list = D('Doctor')->getById($id);
        $hospital_office_id = $list['hospital_office_id'];
        $hospital_office = D('HospitalOffice')->getById($hospital_office_id);
        $hospital_id = $hospital_office['hospital_id'];
        $office_id = $hospital_office['office_id'];
        $hospital_info = D('Hospital')->getById($hospital_id);
        $office_info = D('Office')->getById($office_id);
        $ret['info'] = $list;
        $ret['hospital'] = ['name' => $hospital_info['name']];
        $ret['office'] = ['name' => $office_info['name']];
        $this->jsonReturn($ret);
    }

    /**
     * 获取医生根据医院和科室id
     */
    public function getDoctorByHospitalOfficeId(){
        $params = input('post.params');
        $hospital_id = input('post.hospital_id');
        $office_ids = $params['office_ids'];
        $doctors =[];
        for($i = 0;$i<count($office_ids);$i++){
            $cond['hospital_id'] = ['=',$hospital_id];
            $cond['office_id'] = ['=',$office_ids[$i]];
            $select = ['id'];
            $hospital_office_id = D('Hospital_Office')->getIdByHospitalOffice($select,$cond);
            $doctor = D('Doctor')->getDoctor(['id,name'],['hospital_office_id']);
            array_push($doctors,$doctor);
        }
        $this->jsonReturn($doctors);
    }

    /**
     * 编辑医生信息
     */
    public function edit(){
        $id = input('get.id');
        $params = input('post.');
        $doctor = D('Doctor')->getById($id);
        if(!empty($params)){
            $ret['data'] = $params;
            $ret = ['error_code' => 1, 'msg' => '编辑成功'];
            $doctor_id = $params['doctor_id'];
            unset($params['doctor_id']);
            $res = D('Doctor')->saveData($doctor_id,$params);
            if(!empty($res['errors'])) {
                $ret['error_code'] = 1;
                $ret['msg'] = '编辑失败';
                $ret['errors'] = $res['errors'];
            }
            $this->jsonReturn($ret);
        }else{
            return view('',['doctor' => $doctor]);
        }
    }

}