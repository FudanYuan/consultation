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
            $green_channel = input('post.is_green_channel', 0);
            $cond_and = [];
            $cond_or = [];
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
                $cond_and['a.apply_date'] = strtotime($apply_date);
            }
            if($hospital){
                $cond_and['e.id'] = $hospital;
            }
            if($keywords){
                $cond_or['a.other_apply_project|e.name|c.name|c.phone'] = ['like','%'. myTrim($keywords) .'%'];
            }
            $cond_and['a.is_green_channel'] = $green_channel;
            $user_id = $this->getUserId();
            $select = ['b.id as doctor_id'];
            $cond['a.id'] = ['=',$user_id];
            $user_doctor_id = D('UserAdmin')->getUserAdmin($select,$cond);
            //$cond_and['c.id'] = ['=',$user_doctor_id[0]['doctor_id']];

            $list = D('Apply')->getList($cond_or,$cond_and,[]);
            $page = input('post.current_page',0);
            $per_page = input('post.per_page',0);
            //分页时需要获取记录总数，键值为 total
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
            $data['target_hospital_id'] = input('post.hospital', '');
            $data['is_green_channel'] = input('post.is_green_channel', '');

            if (!isset($params['office_ids'])) {
                $office_ids = [];
            } else{
                $office_ids = $params['office_ids'];
            }

            if (!isset($params['doctor_ids'])) {
                $doctor_ids = [];
                if(!empty($office_ids)){
                    $office_ids = [1];
                    $hospital_id = 1;
                    $office_ids_implode = implode($office_ids, ',');

                    $cond_and['c.id'] = $hospital_id;
                    $cond_and['d.id'] = ['in', $office_ids_implode];
                    $doctor_ids_ret = D('Doctor')->getDoctorList([], $cond_and);
                    for($i=0;$i<count($doctor_ids_ret);$i++){
                        array_push($doctor_ids, $doctor_ids_ret[$i]['id']);
                    }
                }
            }else{
                $doctor_ids = $params['doctor_ids'];
            }
            $data['is_definite_purpose'] = 0;
            if(!empty($office_ids)){
                $data['is_definite_purpose'] = 1;
            }

            $data['target_doctor_ids'] = '-';
            foreach ($doctor_ids as $id){
                $data['target_doctor_ids'] = $data['target_doctor_ids'].$id.'-';
            }

            $data['target_office_ids'] = '-';
            foreach ($office_ids as $id){
                $data['target_office_ids'] = $data['target_office_ids'].$id.'-';
            }

            $data['consultation_goal'] = input('post.consultation_goal', '');
            $data['other_apply_project'] = input('post.other_apply_project', '');

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

        $user_id = $this->getUserId();
        $apply_info = D('Apply')->getById($id);

        $patient_id = $apply_info['patient_id'];
        $source_user_id = $apply_info['source_user_id'];
        // 获取目标医院信息
        $target_hospital_id = $apply_info['target_hospital_id'];
        $target_hospital_info = D('Hospital')->getById($target_hospital_id);

        $ret['target_hospital_info'] = $target_hospital_info;
        $ret['target_doctor_info'] = [];
        $ret['target_office_info'] = [];
        $target_doctor_ids = $apply_info['target_doctor_ids'];
        $target_office_ids = $apply_info['target_office_ids'];
        $array_target_doctor_id = explode('-',$target_doctor_ids);
        for($index=0;$index<count($array_target_doctor_id);$index++) {
            if($array_target_doctor_id[$index] != ''){
                array_push($ret['target_doctor_info'], D('Doctor')->getById((int)$array_target_doctor_id[$index]));
            }
        }
        $array_target_office_id = explode('-',$target_office_ids);
        for($index=0;$index<count($array_target_office_id);$index++) {
            if($array_target_office_id[$index] != ''){
                array_push($ret['target_office_info'], D('Office')->getById((int)$array_target_office_id[$index]));
            }
        }

        $source_user_info = D('UserAdmin')->getById($source_user_id);
        $source_doctor_id = $source_user_info['doctor_id'];
        $source_doctor_info = D('Doctor')->getById($source_doctor_id);
        $source_hospital_office_id = $source_doctor_info['hospital_office_id'];
        $source_hospital_office = D('HospitalOffice')->getById($source_hospital_office_id);
        $source_hospital_id = $source_hospital_office['hospital_id'];
        $source_office_id = $source_hospital_office['office_id'];

        $source_hospital_info = D('Hospital')->getById($source_hospital_id);
        $source_office_info = D('Office')->getById($source_office_id);

        $patient_info = D('Patient')->getById($patient_id);

        $ret['apply_info'] = $apply_info;
        $ret['patient_info'] = $patient_info;
        $ret['source_hospital_info'] = $source_hospital_info;
        $ret['source_office_info'] = $source_office_info;
        $ret['source_doctor_info'] = $source_doctor_info;
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
        $res = D('Apply')->getList(['a.id' => ['in', $ids]], [], []);
        for($i=0;$i<count($res);$i++){
            array_push($target_user_ids, $res[$i]['user_id']);
        }

        if(!in_array($user_id, $target_user_ids)){
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
            }
            $this->jsonReturn($ret);
        }
        $id = input('get.id');
        $status = D('Apply')->getStatusById($id);
        return view('', ['id' => $id, 'status' => $status]);
    }

    /**
     * 删除会诊申请
     */
    public function remove(){
        $ret = ['code' => 1, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            D('Apply')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['code'] = 2;
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
            }
            $this->jsonReturn($ret);
        }
        $id = input('get.id');
        $status = D('Apply')->getStatusById($id);
        return view('', ['id' => $id, 'status' => $status]);
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
            $data['target_hospital_id'] = input('post.hospital', '');
            $data['is_green_channel'] = input('post.is_green_channel', '');
            if (!isset($params['office_ids'])) {
                $office_ids = [];
            } else{
                $office_ids = $params['office_ids'];
            }

            if (!isset($params['doctor_ids'])) {
                $doctor_ids = [];
                if(!empty($office_ids)){
                    $office_ids = [1];
                    $hospital_id = 1;
                    $office_ids_implode = implode($office_ids, ',');

                    $cond_and['c.id'] = $hospital_id;
                    $cond_and['d.id'] = ['in', $office_ids_implode];
                    $doctor_ids_ret = D('Doctor')->getDoctorList([], $cond_and);
                    for($i=0;$i<count($doctor_ids_ret);$i++){
                        array_push($doctor_ids, $doctor_ids_ret[$i]['id']);
                    }
                }
            }else{
                $doctor_ids = $params['doctor_ids'];
            }
            $data['is_definite_purpose'] = 0;
            if(!empty($office_ids)){
                $data['is_definite_purpose'] = 1;
            }

            $data['target_doctor_ids'] = '-';
            foreach ($doctor_ids as $id){
                $data['target_doctor_ids'] = $data['target_doctor_ids'].$id.'-';
            }

            $data['target_office_ids'] = '-';
            foreach ($office_ids as $id){
                $data['target_office_ids'] = $data['target_office_ids'].$id.'-';
            }

            $data['consultation_goal'] = input('post.consultation_goal', '');
            $data['other_apply_project'] = input('post.other_apply_project', '');

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