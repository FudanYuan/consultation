<?php
/**
 * 会诊申请--控制器
 * Created by shiren.
 * time 2017.10.19
 */
namespace app\controller;

class Apply extends Common
{
    public $exportCols = [];
    public $colsText = [];

    /**
     * 会诊申请
     * @return \think\response\View
     */
    public function index()
    {
        $hospital = D('Hospital')->getList();
        return view('', ['hospital' => $hospital]);
    }

    /**
     * 获取会诊申请列表
     */
    public function getApplyList(){
        $params = input('post.');
        // 获取当前登陆的用户id，根据此id查询表，返回结果
        $user_id = $this->getUserId();
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        $cond['target_user_id'] = ['=', $user_id];

        if(empty($params)){
            $cond['status'] = ['=', 0];
            $list = D('Apply')->applyList([],[],[]);
            for($i=0;$i<count($list);$i++){
                $list[$i]['time'] = formatTime($list[$i]['create_time']);
                $list[$i]['consultation_goal'] = formatText($list[$i]['consultation_goal'], 10);
            }
            $ret["total"] = count($list);
            $ret["data"] = $list;
            $this->jsonReturn($ret);
        } else {
            $apply_type = input('post.apply_type','-1');
            $apply_project = input('post.apply_project','-1');
            $status = input('post.status','-1');
            $is_charge = input('post.is_charge','-1');
            $apply_date = input('post.apply_date_str','');
            $hospital = input('post.hospital','-1');
            $keywords = input('post.keywords','');
            $cond_and = [];
            $cond_or = [];
            if($apply_type!=-1){
                $cond_and['apply_type'] = $apply_type;
            }
            if($apply_project!=-1){
                $cond_and['apply_type'] = $apply_project;
            }
            if($status != -1){
                $cond_and['a.status'] = $status;
            }
            if($is_charge != -1){
                $cond_and['is_charge'] = $is_charge;
            }
            if($apply_date){
                $cond_and['apply_date'] = $apply_date;
            }
            if($hospital!=-1){
                $cond_and['e.id'] = $hospital;
            }
            if($keywords){
                $cond_or['apply_type|e.name|c.name|c.phone|'] = ['like','%'.$keywords.'%'];
            }

            $list = D('Apply')->applyList($cond_or,$cond_and,[]);
            $page = input('post.current_page',0);
            $per_page = input('post.per_page',0);
            //分页时需要获取记录总数，键值为 total
            $ret["total"] = count($list);
            //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
            $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
            $ret['current_page'] = $page;
        }
        $ret['params'] = $params;
        $this->jsonReturn($ret);

//            $i=0;
//            foreach ($list as $v){
//                $doctor_data = D('Doctor')->getDoctorById($v['delivery_user_id']);
//                $list[$i]['doctor_name'] = $doctor_data['name'];
//                $list[$i]['doctor_id'] = $doctor_data['id'];
//                $list[$i]['phone'] = $doctor_data['phone'];
//                $ret['doctor'][$i] = $doctor_data;
//                $HospitalOffice_data = D('HospitalOffice')->getHospitalOfficeById($doctor_data['office_id']);
//                $Hospital_data = D('Hospital')->getHospitalById($HospitalOffice_data['hospital_id']);
//                $list[$i]['hospital_id'] = $Hospital_data['id'];
//                $list[$i]['hospital_name'] = $Hospital_data['name'];
//                $i++;
//            }
        //            $list = [];
//            $list[0] = ['id' => 1, 'hospital_id' => 1, 'hospital_logo' => '',
//                'hospital_name' => '医院甲','doctor_id' => 1, 'doctor_name' => '张三',
//                'phone' => '135210263021','apply_type' => 1,'apply_project' => 1,
//                'consultation_goal' => '12324353456', 'apply_date' => 1509871680,
//                'status' => 1, 'price' => 1000, 'is_charge' => 0,
//                'create_time' =>  1509871680
//            ];
//            $list[1] = ['id' => 2, 'hospital_id' => 1, 'hospital_logo' => '',
//                'hospital_name' => '医院乙','doctor_id' => 1, 'doctor_name' => '张三',
//                'phone' => '135210263021','apply_type' => 1,'apply_project' => 1,
//                'consultation_goal' => '放假啦减肥放假啦', 'apply_date' => 1509871680,
//                'status' => 1, 'price' => 1000, 'is_charge' => 0,
//                'create_time' =>  1509871680
//            ];
    }

    /**
     * 新建会诊申请
     */
    public function create(){
        $params = input('post.');
        if(!empty($params)) {
            $data = [];
            $ret = ['error_code' => 0, 'msg' => '新建成功'];
            $params['apply_doctor_name'] = input('apply_doctor_name', '');
            if ($params['apply_doctor_name'] == '') {
                $data['is_definite_purpose'] = 0;
            } else {
                $data['is_definite_purpose'] = 1;
            }
            $data['patient_id'] = input('post.patient_id', '-1');
            $data['delivery_user_id'] = $this->getUserId();
            $data['apply_type'] = input('post.apply_type', '2');
            $data['diagnose_state'] = input('post.diagnose_state', '');
            $data['consultation_goal'] = input('post.consultation_goal', '');
            $data['other_apply'] = input('post.other_apply', '');
            $data['apply_date'] = input('post.apply_date');
            $ret['params'] = $params;
            //如果病患不存在，手动输入
            if ($data['patient_id'] == -1) {
                $patient = [];
                $patient['name'] = input('post.patient_name');
                $patient['ID_number'] = input('post.patient_ID_number');
                //$patient['gender'] = input('post.');
                $patient['age'] = input('post.patient_age');
                $patient['phone'] = input('post.patient_phone');
            }
            $res = D('Apply')->addData($data);
            $this->jsonReturn($ret);
        }
        $hospital = D('Hospital')->getList();
        $office = D('Office')->getList();
        // 还要返回医生信息
        return view('', ['hospital' => $hospital,'office' => $office]);
    }

    /**
     * 会诊申请
     * @return \think\response\View
     */
    public function info(){
        $id = input('get.id');
        return view('', ['id' => $id]);
    }

    /**
     * 获取申请详情
     */
    public function getApplyInfo(){
        //$list = D('Apply')->getById($id);
        $ret = ['error_code' => 0, 'msg' => ''];
        $ret['apply_info'] = ['id' => 1, 'patient_id' => 1, 'source_user_id' => 1, 'apply_type' => 1, 'apply_project' => 1, 'consultation_goal' => '放假啦减肥放假啦放假啦减肥放假啦放假啦减肥放假啦放假啦减肥放假啦放假啦减肥放假啦放假啦减肥放假啦放假啦减肥啦放假啦减肥放假啦放假啦减肥放假啦放假啦减肥啦放假啦减肥放假啦放假啦减肥放假啦放假啦减肥啦放假啦减肥放假啦放假啦减肥放假啦放假啦减肥啦放假啦减肥放假啦放假啦减肥放假啦放假啦减肥啦放假啦减肥放假啦放假啦减肥放假啦放假啦减肥啦放假啦减肥放假啦放假啦减肥放假啦放假啦减肥放假啦放假啦减肥放假啦',
            'apply_date' => 1509871680, 'status' => 1, 'price' => 1000, 'is_charge' => 0, 'create_time' =>  1509871680, 'consultation_result' => '阿娇发来的会计法阿飞饭卡飞机', 'update_time' => 1509971680];
        $ret['patient_info'] = ['id' => 1, 'name' => '王二', 'gender' => 1, 'age' => 21, 'phone' => '1214141',
            'ID_num' => '1212', 'vision_left' => '5.0', 'vision_right' => '5.0', 'pressure_left' => '300', 'pressure_right' => '230'];
        $ret['source_doctor_info'] = ['id'=>1, 'hospital_id'=>1,'office_id'=>1, 'name' => '张三', 'phone'=>'2222222'];
        $ret['source_hospital_info'] = ['id'=>1, 'name' => '医院甲'];
        $ret['target_doctor_info'] = ['id' => 1, 'hospital_office_id' => 1, 'name' => '王五', 'phone'=>'1111111'];
        $ret['target_office_info'] = ['id'=>1, 'name' => '眼科'];
        $ret['target_hospital_info'] = ['id'=>1, 'name' => '湘雅医学院'];
        $this->jsonReturn($ret);
    }

    /////未修改/////
    /**
     * 删除会诊申请
     */
    public function remove(){
        $ret = ['code' => 1, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            $res = D('Apply')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['error_code'] = 2;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 标为已读
     */
    public function markRead(){
        $ret = ['error_code' => 1, 'msg' => '标记成功'];
        $ids = input('post.ids');
        try{
            $res = D('Apply')->markRead(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['error_code'] = 2;
            $ret['msg'] = '标记失败';
        }
        $this->jsonReturn($ret);
    }

    //            $list = D('Apply')->getList($cond);
//            $list = [];
//            $list[0] = ['id' => 1, 'hospital_id' => 1, 'hospital_logo' => '',
//                'hospital_name' => '医院甲','doctor_id' => 1, 'doctor_name' => '张三',
//                'phone' => '135210263021','apply_type' => 1,'apply_project' => 1,
//                'consultation_goal' => '12324353456', 'apply_date' => 1509871680,
//                'status' => 1, 'price' => 1000, 'is_charge' => 0,
//                'create_time' =>  1509871680
//            ];
//            $list[1] = ['id' => 2, 'hospital_id' => 1, 'hospital_logo' => '',
//                'hospital_name' => '医院乙','doctor_id' => 1, 'doctor_name' => '张三',
//                'phone' => '135210263021','apply_type' => 1,'apply_project' => 1,
//                'consultation_goal' => '放假啦减肥放假啦', 'apply_date' => 1509871680,
//                'status' => 1, 'price' => 1000, 'is_charge' => 0,
//                'create_time' =>  1509871680
//            ];

}