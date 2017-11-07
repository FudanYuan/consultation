<?php
/**
 * 患者模型
 * Author yzs
 * Create 2017.10.26
 */
namespace app\model;

use think\Model;

class Patient extends Model{
    protected $table = 'consultation_patient';
    protected $pk = 'id';
    protected $fields = array(
        'id', 'name', 'ID_number', 'gender', 'age', 'occupation', 'phone', 'email',
        'birthplace', 'address', 'work_unit', 'postcode', 'height', 'weight',
        'vision_left', 'vision_right', 'pressure_left', 'pressure_right', 'exam_img',
        'exam_img_origin', 'eye_photo_left', 'eye_photo_left_origin', 'eye_photo_right',
        'eye_photo_right_origin', 'ill_type', 'ill_state', 'diagnose_state',
        'files_path', 'files_path_origin', 'in_hospital_time', 'narrator',
        'main_narrate', 'present_ill_history', 'past_history', 'system_retrospect',
        'personal_history', 'physical_exam_record', 'status', 'create_time', 'update_time'
    );
    protected $type = [
        'id' => 'integer',
        'in_hospital_time' =>'integer',
        'record_time' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];

    public function getPatientByIdNum($Id_Num){
        $res = $this->field('id as patient_id,name as patient_name,age as patient_name,
                    phone as patient_phone,ill_state as patient_illness_state,
                    diagnose_state,gender as patient_gender,ill_type as patient_eyes_type,
                    vision_left as  patient_vision_left,vision_right as patient_vision_right,
                    pressure_left as patient_pressure_left,pressure_right as patient_pressure_right')
            ->where(['ID_number' => $Id_Num])
            ->find();
        return $res;
    }

    /**
     * 添加患者信息
     * @param $data
     * @return array
     */
    public function addData($data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)){
            $data['status'] = 1;
            $data['create_time'] = time();
            $this->save($data);
        }
        return $ret;
    }
    /**
     * 过滤必要字段
     * @param $data
     * @return array
     */
    private function filterField($data){
        $errors = [];
        if(isset($data['name']) && !$data['name']){
            $errors['name'] = '名字不能为空';
        }
        if(isset($data['ID_number']) && !$data['ID_number']){
            $errors['ID_number'] = '身份证号不能为空';
        }
        if(isset($data['age']) && !$data['age']){
            $errors['age'] = '年龄不能为空';
        }
        if(isset($data['phone']) && !$data['phone']){
            $errors['phone'] = '电话不能为空';
        }
        if(isset($data['ill_state']) && !$data['ill_state']){
            $errors['ill_state'] = '病情简介不能为空';
        }
        if(isset($data['ill_type']) && !$data['ill_type']){
            $errors['ill_type'] = '病情类型不能为空';
        }
        if(isset($data['gender']) && !$data['gender']){
            $errors['gender'] = '性别不能为空';
        }
        return $errors;
    }



    ///////未修改///////
    /**
     * 获取患者列表
     * @param array $cond
     */
    public function getList($cond = []){
        if(!isset($cond['status'])){
            $cond['status'] = ['<>', 2];
        }
        $res = $this->field('*')
            ->order('create_time desc')
            ->where($cond)
            ->select();
        return $res;
    }

    /**
     * 通过ID获取
     * @param $id
     * @return mixed
     */
    public function getById($id){
        $res = $this->field('*')
            ->where(['id' => $id])
            ->find();
        return $res;
    }

    ///////未修改///////
    /**
     * 更新患者信息
     * {@inheritDoc}
     * @see \think\Model::save()
     */
    public function saveData($id, $data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)){
            $data['update_time'] = time();
            $this->save($data, ['id' => $id]);
        }
        return $ret;
    }


    /**
     * 批量增加患者信息
     * @param $dataSet
     * @return array
     */
    public function addAllData($dataSet){
        $ret = [];
        foreach ($dataSet as $data) {
            $errors = $this->filterField($data);
            $ret['errors'] = $errors;
            if(!empty($errors)){
                return $ret;
            }
        }
        $ret['result'] = $this->saveAll($dataSet);
        return $ret;
    }

    /**
     * 删除患者信息
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public function remove($cond = []){
        $res = $this->save(['status' => 2], $cond);
        if($res === false) throw new MyException('2', '删除失败');
        return $res;
    }


}
?>