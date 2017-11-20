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
        'eye_photo_right_origin', 'ill_type', 'other_ill_type', 'ill_state', 'diagnose_state',
        'files_path', 'files_path_origin', 'in_hospital_time', 'narrator', 'main_narrate',
        'present_ill_history', 'past_history', 'system_retrospect', 'personal_history',
        'physical_exam_record', 'status', 'create_time', 'update_time'
    );
    protected $type = [
        'id' => 'integer',
        'record_time' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];


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

    /**
     * 根据身份证号获取
     * @param $Id_Num
     * @return mixed
     */
    public function getByIdNum($Id_Num){
        $res = $this->field('id, name, ID_number, gender, age,phone,vision_left,
                vision_right, pressure_left, pressure_right, exam_img, exam_img_origin,
                eye_photo_left, eye_photo_left_origin, eye_photo_right, eye_photo_right_origin,
                ill_type, other_ill_type, ill_state, diagnose_state, files_path, files_path_origin')
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
        $errors = $this->filterField($data,false);
        $ret['errors'] = $errors;
        if(empty($errors)){
            $data['status'] = 1;
            $data['create_time'] = time();
            $this->save($data);
        }
        return $ret;
    }

    public function save_1($data){
        $res = Db('consultation_patient')->insertGetId($data);
        return $res;
    }

    /**
     * 过滤必要字段
     * @param $data
     * @param $editFlag
     * @return array
     */
    private function filterField($data,$editFlag){
        $errors = [];
        if(isset($data['name']) && !$data['name']){
            $errors['name'] = '名字不能为空';
        }
        if(isset($data['ID_number']) && !$data['ID_number']){
            $errors['ID_number'] = '身份证号不能为空';
        }else if(!$editFlag){
            $ret = $this->getByIdNum($data['ID_number']);
            if(!empty($ret)){
                $errors['ID_number'] = '此人已存在';
            }
        }
        if(isset($data['gender']) && !$data['gender']){
            $errors['gender'] = '性别不能为空';
        }
        if(isset($data['age']) && !$data['age']){
            $errors['age'] = '年龄不能为空';
        }
        if(isset($data['phone']) && !$data['phone']){
            $errors['phone'] = '电话不能为空';
        }
        if(isset($data['ill_state']) && !$data['ill_state']){
            $errors['ill_state'] = '简要病情不能为空';
        }
        if(isset($data['ill_type']) && !$data['ill_type']){
            $errors['ill_type'] = '病情类型不能为空';
        }
        if(isset($data['diagnose_state']) && !$data['diagnose_state']){
            $errors['diagnose_state'] = '诊疗情况不能为空';
        }
        if(isset($data['diagnose_state']) && !$data['diagnose_state']){
            $errors['diagnose_state'] = '诊疗情况不能为空';
        }
        if(isset($data['other_ill_type']) && $data['ill_type'] == 5 && !$data['other_ill_type']){
            $errors['other_ill_type'] = '其他病情详情不能为空';
        }
        return $errors;
    }

    /**
     * 更新患者信息
     * {@inheritDoc}
     * @see \think\Model::save()
     */
    public function saveData($id, $data){
        $ret = [];
        $errors = $this->filterField($data,true);
        $ret['errors'] = $errors;
        if(empty($errors)){
            $data['update_time'] = time();
            $this->save($data, ['id' => $id]);
        }
        return $ret;
    }
    ///////未修改///////

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