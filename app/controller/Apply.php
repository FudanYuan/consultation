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
        $select=['id,name'];
        $hospital = D('Hospital')->getHospital($select,[]);
        return view('', ['hospital' => $hospital]);
    }

    /**
     * 获取会诊申请列表
     */
    public function getApplyList(){
        $params = input('post.');
        // 获取当前登陆的用户id，根据此id查询表，返回结果
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        if(!empty($params)){
            $apply_type = input('post.apply_type','');
            $apply_project = input('post.apply_project','');
            $status = input('post.status','');
            $is_charge = input('post.is_charge','');
            $apply_date = input('post.apply_date_str','');
            $hospital = input('post.hospital','');
            $keywords = input('post.keywords','');
            $green_channel = input('post.is_green_channel', -1);
            $cond_and = [];
            if($apply_type){
                $cond_and['a.apply_type'] = $apply_type;
            }
            if($apply_project){
                $cond_and['a.apply_project'] = $apply_project;
            }
            if($status){
                $cond_and['a.status'] = $status;
            }
            if($is_charge){
                $cond_and['a.is_charge'] = $is_charge;
            }
            if($apply_date){
                $cond_and['a.apply_date'] = ['between', [strtotime($apply_date),strtotime($apply_date) + 3600*24]];
            }
            if($hospital){
                $cond_and['e.id'] = $hospital;
            }
            if($keywords){
                $cond_and['a.other_apply_project|e.name|h.name|e.phone|h.phone|g.name|j.name|k.name|k.phone'] = ['like','%'. myTrim($keywords) .'%'];
            }
            if($green_channel != -1){
                $cond_and['a.is_green_channel'] = $green_channel;
            }

            // 获取当前用户信息，并判读是否具有会诊权限
            $user_id = $this->getUserId();
            $select = ['b.id as doctor_id, d.role as role'];
            $cond['a.id'] = ['=',$user_id];
            $user_info = D('UserAdmin')->getUserAdmin($select,$cond);
            $user_role = $user_info[0]['role'];

            if($user_role == 1){ // 具有会诊能力, 显示申请方信息
                $select = ['a.id as id, b.id as user_id, e.id as doctor_id, e.name as doctor_name, g.logo as hospital_logo, 
                 e.phone as phone, g.id as hospital_id, g.name as hospital_name, apply_type,apply_project,
                 other_apply_project,is_green_channel,consultation_goal,apply_date,a.status,price,is_charge,
                 a.create_time'];
            } else{ // 不具有会诊能力，显示会诊方信息
                $select = ['a.id as id, b.id as user_id, h.id as doctor_id, h.name as doctor_name,
                 h.phone as phone, j.id as hospital_id, j.name as hospital_name, apply_type,apply_project,
                 other_apply_project,is_green_channel,consultation_goal,apply_date,a.status,price,is_charge,
                 a.create_time'];
            }
            $cond_or['a.source_user_id | c.target_user_id'] = $user_id;
            $apply_info = D('Apply')->getList($select,$cond_or,$cond_and,[]);
            //  如果一个申请同时申请了多名医生，则需要对结果进行合并
            $apply_ids = [];
            $list = [];
            for($i=0;$i<count($apply_info);$i++){
                $apply_id = $apply_info[$i]['id'];
                if(!in_array($apply_id, $apply_ids)){
                    array_push($apply_ids, $apply_id);
                    array_push($list, $apply_info[$i]);
                } else{
                    $index = array_search($apply_id, $apply_ids);
                    $list[$index]['doctor_name'] .= '、'.$apply_info[$i]['doctor_name'];
                    $list[$index]['phone'] .= '、'.$apply_info[$i]['phone'];
                }
            }

            $page = input('post.current_page',0);
            $per_page = input('post.per_page',0);
            //分页时需要获取记录总数，键值为 total
            $ret["role"] = $user_role; // 获取当前医生的角色，是否具有会诊资格
            $ret["total"] = count($list);
            //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
            $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
            $ret['current_page'] = $page;
        }
        $this->jsonReturn($ret);
    }

    /**
     * 新建会诊申请
     */
    public function create(){
        $params = input('post.');
        if(!empty($params)) {
            $data = [];
            $ret = ['error_code' => 0, 'msg' => '新建成功'];
            //申请目标
            $data['apply_date_str'] = input('post.apply_date', '');
            $data['source_user_id'] = $this->getUserId();
            $data['apply_project'] = input('post.apply_project', '');
            $data['apply_type'] =input('post.apply_type', '');
            $data['is_green_channel'] = input('post.is_green_channel', '');

            if (!isset($params['office_ids'])) {
                $hospital_office_ids = [];
            } else{
                $hospital_office_ids = $params['office_ids'];
            }

            $data['is_definite_purpose'] = 0;

            $target_user_ids = [];
            if (!isset($params['doctor_ids'])) {
                if(!empty($hospital_office_ids)){
                    $hospital_office_ids_implode = implode($hospital_office_ids, ',');

                    $select = ['a.id as target_user_id'];
                    $cond_and['c.id'] = ['in', $hospital_office_ids_implode];
                    $user_ids_ret = D('UserAdmin')->getUserAdmin($select, $cond_and);
                    for($i=0;$i<count($user_ids_ret);$i++){
                        array_push($target_user_ids, $user_ids_ret[$i]['target_user_id']);
                    }
                }
            }else{
                $data['is_definite_purpose'] = 1;
                $doctor_ids = $params['doctor_ids'];
                // 将医生ids转化成用户ids
                $doctor_ids_implode = implode($doctor_ids, ',');
                $select = ['a.id as target_user_id'];
                $cond_and['b.id'] = ['in', $doctor_ids_implode];
                $user_ids_ret = D('UserAdmin')->getUserAdmin($select, $cond_and);
                for($i=0;$i<count($user_ids_ret);$i++){
                    array_push($target_user_ids, $user_ids_ret[$i]['target_user_id']);
                }
            }

            $data['consultation_goal'] = input('post.consultation_goal', '');
            $data['other_apply_project'] = input('post.other_apply_project', '');
            $data['target_user_ids'] = $target_user_ids;

            if (!isset($params['patient'])) {
                $patient = [];
            }else{
                $patient = $params['patient'];
            }
            //如果病患不存在，手动输入
            if (!empty($patient) && !$patient['id']) {
                $res = D('Patient')->addData($patient);
                if(!empty($res['errors'])){
                    $ret['error_code'] = 1;
                    $ret['errors'] = $res['errors'];
                    $ret['msg'] = '病人新建失败';
                    $this->jsonReturn($ret);
                }
                $patient_id = D('Patient')->getByIdNum($patient['ID_number']);
                $data['patient_id'] = $patient_id['id'];
            }else {
                $resPatient = D('Patient')->saveData($patient['id'],$patient);
                if(!empty($resPatient['errors'])){
                    $ret['error_code'] = 1;
                    $ret['errors'] = $resPatient['errors'];
                    $ret['msg'] = '病人保存失败';
                }
                $data['patient_id'] = $patient['id'];
            }

            $res = D('Apply')->addData($data);
            if(!empty($res['errors'])){
                $ret['error_code'] = 1;
                $ret['errors'] = $res['errors'];
                $ret['msg'] = '新建失败';
            }
            $this->jsonReturn($ret);
        }
        $select = ['id,name'];
        $cond = ['role' => 1];
        $hospital = D('Hospital')->getHospital($select,$cond);
        $hospital_id = $hospital[0]['id'];
        $hospital_office = D('HospitalOffice')->getList(['hospital_id' => $hospital_id]);
        $office = [];
        for($i=0;$i<count($hospital_office);$i++){
            $office_id = $hospital_office[$i]['office_id'];
            array_push($office, D('Office')->getOffice($select,['id'=>$office_id]));
        }
        $hospital_office_id = $hospital_office[0]['id'];
        $doctor = D('Doctor')->getList(['hospital_office_id' => $hospital_office_id]);

        $select = ['d.name as apply_hospital_name,b.name as apply_doctor_name,b.phone as apply_doctor_phone'];
        $cond = ['a.id' => $this->getUserId()];
        $info = D('UserAdmin')->getUserAdmin($select,$cond);
        if(count($info)>0){
            $apply_info = $info[0];
        } else{
            $apply_info = [];
        }
        $apply_info['date'] = time();
        return view('', ['hospital' => $hospital,'office' => $office, 'doctor' => $doctor,'apply_info'=>$apply_info]);
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
        $id = input('post.id');
        $ret = ['error_code' => 0, 'msg' => ''];

        $select = ['a.id as id, k.id as patient_id, k.ID_number as patient_ID_number,k.name as patient_name, k.gender as patient_gender, 
                 k.age as patient_age, k.phone as patient_phone, k.vision_left as patient_vision_left, 
                 k.vision_right as patient_vision_right, k.pressure_left as patient_pressure_left, 
                 k.pressure_right as patient_pressure_right, k.exam_img as patient_exam_img, 
                 k.exam_img_origin as patient_exam_img_origin, k.eye_photo_left as patient_eye_photo_left,
                 k.eye_photo_left_origin as patient_eye_photo_left_origin, k.eye_photo_right as patient_eye_photo_right,
                 k.eye_photo_right_origin as patient_eye_photo_right_origin, k.ill_type as patient_ill_type, 
                 k.other_ill_type as patient_other_ill_type, k.ill_state as patient_ill_state,
                 k.diagnose_state as patient_diagnose_state, k.files_path as patient_files_path,
                 k.files_path_origin as patient_files_path_origin, e.id as source_doctor_id, e.name as source_doctor_name,
                 e.phone as source_doctor_phone, g.id as source_hospital_id, g.name as source_hospital_name, 
                 h.id as target_doctor_id, h.name as target_doctor_name, h.phone as target_doctor_phone, 
                 j.id as target_hospital_id, j.name as target_hospital_name, l.name as source_office_name,
                 m.name as target_office_name, apply_type,apply_project, other_apply_project,is_green_channel,consultation_result,
                 consultation_goal,apply_date,a.status,price,is_charge,a.create_time, a.update_time'];
        $cond_and['a.id'] =  $id;
        $all_info = D('Apply')->getList($select, [], $cond_and, []);

        $apply_ids = [];
        $data = [];
        for($i=0;$i<count($all_info);$i++){
            $apply_id = $all_info[$i]['id'];
            if(!in_array($apply_id, $apply_ids)){
                array_push($apply_ids, $apply_id);
                array_push($data, $all_info[$i]);
            } else{
                $index = array_search($apply_id, $apply_ids);
                $data[$index]['target_doctor_name'] .= '、'.$all_info[$i]['target_doctor_name'];
                $data[$index]['target_doctor_phone'] .= '、'.$all_info[$i]['target_doctor_phone'];
                if($data[$index]['target_office_name'] != $all_info[$i]['target_office_name']){
                    $data[$index]['target_office_name'] .= '、'.$all_info[$i]['target_office_name'];
                }
            }
        }

        $apply_info = [];
        $patient_info = [];
        $source_hospital_info = [];
        $source_office_info = [];
        $source_doctor_info = [];
        $target_hospital_info = [];
        $target_doctor_info = [];
        $target_office_info = [];
        $data = json_decode($data[0]);
        foreach ($data as $k => $v){
            if(strpos($k, 'patient_') !== false){
                $k = str_replace('patient_', '', $k);
                $patient_info[$k] = $v;
            } else if(strpos($k, 'source_hospital_') !== false){
                $k = str_replace('source_hospital_', '', $k);
                $source_hospital_info[$k] = $v;
            } else if(strpos($k, 'source_office_') !== false){
                $k = str_replace('source_office_', '', $k);
                $source_office_info[$k] = $v;
            } else if(strpos($k, 'source_doctor_') !== false){
                $k = str_replace('source_doctor_', '', $k);
                $source_doctor_info[$k] = $v;
            } else if(strpos($k, 'target_hospital_') !== false){
                $k = str_replace('target_hospital_', '', $k);
                $target_hospital_info[$k] = $v;
            } else if(strpos($k, 'target_office_') !== false){
                $k = str_replace('target_office_', '', $k);
                $target_office_info[$k] = $v;
            } else if(strpos($k, 'target_doctor_') !== false){
                $k = str_replace('target_doctor_', '', $k);
                $target_doctor_info[$k] = $v;
            } else{
                $apply_info[$k] = $v;
            }
        }

        $ret['apply_info'] = $apply_info;
        $ret['patient_info'] = $patient_info;
        $ret['source_hospital_info'] = $source_hospital_info;
        $ret['source_office_info'] = $source_office_info;
        $ret['source_doctor_info'] = $source_doctor_info;
        $ret['target_hospital_info'] = $target_hospital_info;
        $ret['target_doctor_info'] = $target_doctor_info;
        $ret['target_office_info'] = $target_office_info;

        $this->jsonReturn($ret);
    }

    /**
     * 标记为已读
     */
    public function markRead(){
        $ret = ['error_code' => 0, 'msg' => '标记成功'];
        $ids = input('post.ids');

        $target_user_ids = [];
        $user_id = $this->getUserId();
        $select = ['c.target_user_id as target_user_id'];
        $res = D('Apply')->getList($select,['a.id' => ['in', $ids]], [], []);
        for($i=0;$i<count($res);$i++){
            array_push($target_user_ids, $res[$i]['target_user_id']);
        }

        if(in_array($user_id, $target_user_ids)){
            try{
                $res = D('Apply')->markRead(['id' => ['in', $ids]]);
            }catch(MyException $e){
                $ret['error_code'] = 1;
                $ret['msg'] = '标记失败';
            }
        }
        $this->jsonReturn($ret);
    }

    /**
     * 回复申请
     */
    public function reply(){
        $data = input('post.');
        if(!empty($data)){
            $ret = ['error_code' => 0, 'msg' => '回复成功'];
            $id = $data['id'];
            $source_user_id = $data['source_user_id'];
            unset($data['source_user_id']);
            if($data['status'] == 4){
                $data = [];
                $data['id'] = $id;
                $data['consultation_result'] = '很抱歉，您的会诊申请被拒绝！';
                $data['status'] = 4;
            }
            $res = D('Apply')->saveData($id, $data);
            if(!empty($res['errors'])){
                $ret['debug'] = !empty($ret['errors']);
                $ret['error_code'] = 1;
                $ret['errors'] = $res['errors'];
                $ret['msg'] = '回复失败';
                $this->jsonReturn($ret);
            }

            $data = [];
            $data['type'] = 1; // 提醒类
            $data['target_user_id'] = $source_user_id;
            $data['title'] = '#'.$id.'申请有了回复，快来看呀！';
            $data['content'] = '提醒类信息：' . $data['title'];
            $data['operation'] = '查看';
            $data['priority'] = 1;
            $data['status'] = 0;
            $data['url'] = '/Apply/info?id=' . $id; // 跳转链接
            // 添加Inform
            $res_inform = D('Inform')->addData($data);
            if (!empty($res_inform['errors'])) {
                $ret['error_code'] = 1;
                $ret['msg'] = '新建失败';
                $ret['errors'] = $res_inform['errors'];
            }
            $this->jsonReturn($ret);
        }
        $id = input('get.id');
        $info = D('Apply')->getById($id);
        return view('', ['id' => $id, 'status' => $info['status'], 'source_user_id' => $info['source_user_id']]);
    }

    /**
     * 删除会诊申请
     */
    public function remove(){
        $ret = ['error_code' => 0, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            D('Apply')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['error_code'] = 1;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 编辑会诊申请
     */
    public function edit(){
        $id = input('get.id');
        $params = input('post.');
        if(!empty($params)) {
            $data = [];
            $ret = ['error_code' => 0, 'msg' => '保存成功'];

            $data['id'] = input('post.id', '');
            $data['consultation_goal'] = input('post.consultation_goal', '');

            if (!isset($params['patient'])) {
                $patient = [];
            }else{
                $patient = $params['patient'];
            }
            // 更新病患信息
            $resPatient = D('Patient')->saveData($patient['id'],$patient);
            if(!empty($resPatient['errors'])){
                $ret['error_code'] = 1;
                $ret['errors'] = $resPatient['errors'];
                $ret['msg'] = '保存失败';
            }

            $res = D('Apply')->saveData($data['id'], $data);
            if(!empty($res['errors'])){
                $ret['error_code'] = 1;
                $ret['errors'] = $res['errors'];
                $ret['msg'] = '保存失败';
            }
            $this->jsonReturn($ret);
        }
        return view('', ['id' => $id]);
    }


    /**
     * 绿色通道申请
     * @return \think\response\View
     */
    public function channel()
    {
        $select=['id,name'];
        $hospital = D('Hospital')->getHospital($select,[]);
        return view('', ['hospital' => $hospital]);
    }

    /**
     * 绿色通道申请详情
     * @return \think\response\View
     */
    public function channelInfo(){
        $id = input('get.id');
        return view('', ['id' => $id]);
    }

    /**
     * 回复绿色通道申请
     */
    public function channelReply(){
        $data = input('post.');
        if(!empty($data)){
            $ret = ['error_code' => 0, 'msg' => '回复成功'];
            $id = $data['id'];
            $source_user_id = $data['source_user_id'];
            if($data['status'] == 4){
                $data = [];
                $data['id'] = $id;
                $data['consultation_result'] = '很抱歉，您的会诊申请被拒绝！';
                $data['status'] = 4;
            }
            $res = D('Apply')->saveData($id, $data);
            if(!empty($res['errors'])){
                $ret['debug'] = !empty($ret['errors']);
                $ret['error_code'] = 1;
                $ret['errors'] = $res['errors'];
                $ret['msg'] = '回复失败';
                $this->jsonReturn($ret);
            }

            $data = [];
            $data['type'] = 1; // 提醒类
            $data['target_user_id'] = $source_user_id;
            $data['title'] = '#'.$id.'申请有了回复，快来看呀！';
            $data['content'] = '提醒类信息：' . $data['title'];
            $data['operation'] = '查看';
            $data['priority'] = 1;
            $data['status'] = 0;
            $data['url'] = '/Apply/channelInfo?id=' . $id; // 跳转链接
            // 添加Inform
            $res_inform = D('Inform')->addData($data);
            if (!empty($res_inform['errors'])) {
                $ret['error_code'] = 1;
                $ret['msg'] = '新建失败';
                $ret['errors'] = $res_inform['errors'];
            }

            $this->jsonReturn($ret);
        }
        $id = input('get.id');
        $info = D('Apply')->getById($id);
        return view('', ['id' => $id, 'status' => $info['status'], 'source_user_id' => $info['source_user_id']]);
    }

    /**
     * 新建绿色通道申请
     */
    public function channelCreate(){
        $params = input('post.');
        if(!empty($params)) {
            $data = [];
            $ret = ['error_code' => 0, 'msg' => '新建成功'];
            //申请目标
            $data['apply_date_str'] = input('post.apply_date', '');
            $data['source_user_id'] = $this->getUserId();
            $data['apply_project'] = input('post.apply_project', '');
            $data['apply_type'] =input('post.apply_type', '');
            $data['is_green_channel'] = input('post.is_green_channel', '');

            if (!isset($params['office_ids'])) {
                $hospital_office_ids = [];
            } else{
                $hospital_office_ids = $params['office_ids'];
            }

            $data['is_definite_purpose'] = 0;

            $target_user_ids = [];
            if (!isset($params['doctor_ids'])) {
                if(!empty($hospital_office_ids)){
                    $hospital_office_ids_implode = implode($hospital_office_ids, ',');

                    $select = ['a.id as target_user_id'];
                    $cond_and['c.id'] = ['in', $hospital_office_ids_implode];
                    $user_ids_ret = D('UserAdmin')->getUserAdmin($select, $cond_and);
                    for($i=0;$i<count($user_ids_ret);$i++){
                        array_push($target_user_ids, $user_ids_ret[$i]['target_user_id']);
                    }
                }
            }else{
                $data['is_definite_purpose'] = 1;
                $doctor_ids = $params['doctor_ids'];
                // 将医生ids转化成用户ids
                $doctor_ids_implode = implode($doctor_ids, ',');
                $select = ['a.id as target_user_id'];
                $cond_and['b.id'] = ['in', $doctor_ids_implode];
                $user_ids_ret = D('UserAdmin')->getUserAdmin($select, $cond_and);
                for($i=0;$i<count($user_ids_ret);$i++){
                    array_push($target_user_ids, $user_ids_ret[$i]['target_user_id']);
                }
            }

            $data['consultation_goal'] = input('post.consultation_goal', '');
            $data['other_apply_project'] = input('post.other_apply_project', '');
            $data['target_user_ids'] = $target_user_ids;

            if (!isset($params['patient'])) {
                $patient = [];
            }else{
                $patient = $params['patient'];
            }
            //如果病患不存在，手动输入
            if (!empty($patient) && !$patient['id']) {
                $res = D('Patient')->addData($patient);
                if(!empty($res['errors'])){
                    $ret['error_code'] = 1;
                    $ret['errors'] = $res['errors'];
                    $ret['msg'] = '病人新建失败';
                    $this->jsonReturn($ret);
                }
                $patient_id = D('Patient')->getByIdNum($patient['ID_number']);
                $data['patient_id'] = $patient_id['id'];
            }else {
                $resPatient = D('Patient')->saveData($patient['id'],$patient);
                if(!empty($resPatient['errors'])){
                    $ret['error_code'] = 1;
                    $ret['errors'] = $resPatient['errors'];
                    $ret['msg'] = '病人保存失败';
                }
                $data['patient_id'] = $patient['id'];
            }

            $res = D('Apply')->addData($data);
            if(!empty($res['errors'])){
                $ret['error_code'] = 1;
                $ret['errors'] = $res['errors'];
                $ret['msg'] = '新建失败';
            }
            $this->jsonReturn($ret);
        }
        $select = ['id,name'];
        $cond = ['role' => 1];
        $hospital = D('Hospital')->getHospital($select,$cond);
        $hospital_id = $hospital[0]['id'];
        $hospital_office = D('HospitalOffice')->getList(['hospital_id' => $hospital_id]);
        $office = [];
        for($i=0;$i<count($hospital_office);$i++){
            $office_id = $hospital_office[$i]['office_id'];
            array_push($office, D('Office')->getOffice($select,['id'=>$office_id]));
        }
        $hospital_office_id = $hospital_office[0]['id'];
        $doctor = D('Doctor')->getList(['hospital_office_id' => $hospital_office_id]);

        $select = ['d.name as apply_hospital_name,b.name as apply_doctor_name,b.phone as apply_doctor_phone'];
        $cond = ['a.id' => $this->getUserId()];
        $info = D('UserAdmin')->getUserAdmin($select,$cond);
        if(count($info)>0){
            $apply_info = $info[0];
        } else{
            $apply_info = [];
        }
        $apply_info['date'] = time();
        return view('', ['hospital' => $hospital,'office' => $office, 'doctor' => $doctor,'apply_info'=>$apply_info]);
    }

    /**
     * 编辑绿色通道申请
     */
    public function channelEdit(){
        $id = input('get.id');
        $params = input('post.');
        if(!empty($params)) {
            $data = [];
            $ret = ['error_code' => 0, 'msg' => '保存成功'];

            $data['id'] = input('post.id', '');
            $data['consultation_goal'] = input('post.consultation_goal', '');

            if (!isset($params['patient'])) {
                $patient = [];
            }else{
                $patient = $params['patient'];
            }
            // 更新病患信息
            $resPatient = D('Patient')->saveData($patient['id'],$patient);
            if(!empty($resPatient['errors'])){
                $ret['error_code'] = 1;
                $ret['errors'] = $resPatient['errors'];
                $ret['msg'] = '保存失败';
            }

            $res = D('Apply')->saveData($data['id'], $data);
            if(!empty($res['errors'])){
                $ret['error_code'] = 1;
                $ret['errors'] = $res['errors'];
                $ret['msg'] = '保存失败';
            }
            $this->jsonReturn($ret);
        }
        return view('', ['id' => $id]);
    }
}