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
        $ID_number = input('post.patient_ID_number');
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
        $ret['params'] = $params;
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;
        $this->jsonReturn($ret);
    }

    /**
     * 获取患者信息
     */
    function info(){
        $id = input('get.id');
        return view('', ['id' => $id]);
    }

    /**
     * 获取患者详情
     */
    public function getPatientInfo(){
        $id = input('post.id');
        $ret = ['error_code' => 0, 'msg' => ''];
        $list = D('Patient')->getById($id);
        $user_id = $this->getUserId();
        $user_info = D('UserAdmin')->getById($user_id);
        $doctor_id = $user_info['doctor_id'];
        $hospital_office = D('Doctor')->getById($doctor_id);
        $hospital_office_id = $hospital_office['hospital_office_id'];
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
     * 新建患者信息
     */
    public function create(){
        $params = input('post.');
        if(!empty($params)) {
            $ret = ['error_code' => 0, 'msg' => '新建成功'];
            $data['name'] = input('patient_name');
            $data['ID_number'] = input('post.patient_ID_number');
            $data['gender'] = input('post.patient_gender','');
            $data['age'] = input('post.patient_age');
            $data['occupation'] = input('post.patient_occupation');
            $data['phone'] = input('post.patient_phone');
            $data['email'] = input('post.patient_email');
            $data['birthplace'] = input('post.patient_birthplace');
            $data['address'] = input('post.patient_address');
            $data['work_unit'] = input('post.patient_work_unit');
            $data['postcode'] = input('post.patient_postcode');
            $data['height'] = input('post.patient_height');
            $data['weight'] = input('post.patient_weight');
            $data['vision_left'] = input('post.patient_vision_left');
            $data['vision_right'] = input('post.patient_vision_right');
            $data['pressure_left'] = input('post.patient_pressure_left');
            $data['pressure_right'] = input('post.patient_pressure_right');
//            $data['exam_img'] = input('post.eye_photo_left');
//            $data['exam_img_origin'] = input('post.eye_photo_left_origin');
            $data['eye_photo_left'] = input('post.eye_photo_left');
            $data['eye_photo_right'] = input('post.eye_photo_right');
            $data['eye_photo_left_origin'] = input('post.eye_photo_left_origin');
            $data['eye_photo_right_origin'] = input('post.eye_photo_right_origin');
            $data['ill_type'] = input('post.patient_eyes_type');
            $data['other_ill_type'] = input('post.other_ill_type','');
            $data['ill_state'] = input('post.patient_illness_state');
            $data['diagnose_state'] = input('post.diagnose_state');
            $data['files_path'] = input('post.files_path');
            $data['files_path_origin'] = input('post.files_path_origin');
            $data['in_hospital_time'] = input('post.in_hospital_time');
            $data['narrator'] = input('post.narrator');
            $data['main_narrate'] = input('post.main_narrate');
            $data['in_hospital_time'] = strtotime($data['in_hospital_time']);
            $res = D('Patient')->addData($data);
            $ret['params'] = $params;
            if(!empty($res['errors'])) {
                $ret['error_code'] = 2;
                $ret['msg'] = '新建失败';
                $ret['errors'] = $res['errors'];
            }
            $this->jsonReturn($ret);
        }
        return view('',[]);
    }

    /**
     * 编辑患者信息
     * @return \think\response\View
     */
    public function edit(){
            $id = input('get.id');
            $params = input('post.');
            $patient = D('Patient')->getById($id);
            if(!empty($params)){
                $ret = ['error_code' => 0, 'msg' => '编辑成功'];
                $data['name'] = input('patient_name');
                $data['ID_number'] = input('post.patient_ID_number');
                $data['gender'] = input('post.patient_gender','');
                $data['age'] = input('post.patient_age');
                $data['occupation'] = input('post.patient_occupation');
                $data['phone'] = input('post.patient_phone');
                $data['email'] = input('post.patient_email');
                $data['birthplace'] = input('post.patient_birthplace');
                $data['address'] = input('post.patient_address');
                $data['work_unit'] = input('post.patient_work_unit');
                $data['postcode'] = input('post.patient_postcode');
                $data['height'] = input('post.patient_height');
                $data['weight'] = input('post.patient_weight');
                $data['vision_left'] = input('post.patient_vision_left');
                $data['vision_right'] = input('post.patient_vision_right');
                $data['pressure_left'] = input('post.patient_pressure_left');
                $data['pressure_right'] = input('post.patient_pressure_right');
//            $data['exam_img'] = input('post.eye_photo_left');
//            $data['exam_img_origin'] = input('post.eye_photo_left_origin');
                $data['eye_photo_left'] = input('post.eye_photo_left');
                $data['eye_photo_right'] = input('post.eye_photo_right');
                $data['eye_photo_left_origin'] = input('post.eye_photo_left_origin');
                $data['eye_photo_right_origin'] = input('post.eye_photo_right_origin');
                $data['ill_type'] = input('post.patient_eyes_type');
                $data['other_ill_type'] = input('post.other_ill_type','');
                $data['ill_state'] = input('post.patient_illness_state');
                $data['diagnose_state'] = input('post.diagnose_state');
                $data['files_path'] = input('post.files_path');
                $data['files_path_origin'] = input('post.files_path_origin');
                $data['in_hospital_time'] = input('post.in_hospital_time');
                $data['narrator'] = input('post.narrator');
                $data['main_narrate'] = input('post.main_narrate');
                $data['in_hospital_time'] = strtotime($data['in_hospital_time']);
                if(empty($data['eye_photo_left'])){
                    unset($data['eye_photo_left']);
                    unset($data['eye_photo_left_origin']);
                }
                if(empty($data['eye_photo_right'])){
                    unset($data['eye_photo_right']);
                    unset($data['eye_photo_right_origin']);
                }
                $res = D('Patient')->saveData($params['patient_id'],$data);
                if(!empty($res['errors'])) {
                    $ret['error_code'] = 2;
                    $ret['msg'] = '编辑失败';
                    $ret['errors'] = $res['errors'];
                }
                $this->jsonReturn($ret);
            }else{
                return view('',['patient' => $patient]);
            }
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


}