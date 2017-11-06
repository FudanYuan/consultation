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
        'id', 'name', 'ID_number', 'gender', 'age', 'occupation', 'phone',
        'email', 'birthplace', 'addrss', 'work_unit', 'postcode', 'height',
        'weight', 'vision_left', 'vision_right', 'pressure_left', 'pressure_right',
        'eye_photo_left', 'eye_photo_right', 'ill_type', 'ill_state', 
        'diagnose_state', 'files_path', 'in_hospital_time', 'narrator', 
        'main_narrate', 'present_ill_history', 'past_history', 'system_retrospect',
        'personal_history', 'physical_exam_record', 'status', 'create_time', 'update_time'
    );
    protected $type = [
        'id' => 'integer',
        'patient_id' => 'integer',
        'delivery_user_id' => 'integer',
        'apply_date' => 'integer',
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
        $res = $this->field('id, name, ID_number, gender, age, occupation, phone,
        email, birthplace, addrss, work_unit, postcode, height,
        weight, vision_left, vision_right, pressure_left, pressure_right,
        eye_photo_left, eye_photo_right, ill_type, ill_state, 
        diagnose_state, files_path, in_hospital_time, narrator, 
        main_narrate, present_ill_history, past_history, system_retrospect,
        personal_history, physical_exam_record, create_time')
            ->order('create_time desc')
            ->where($cond)
            ->select();
        return $res;
    }

    /**
     * 根据ID获取患者信息
     * @param $id
     * @return mixed
     */
    public function getById($id){
        $res = $this->field('id, name, ID_number, gender, age, occupation, phone,
        email, birthplace, addrss, work_unit, postcode, height,
        weight, vision_left, vision_right, pressure_left, pressure_right,
        eye_photo_left, eye_photo_right, ill_type, ill_state, 
        diagnose_state, files_path, in_hospital_time, narrator, 
        main_narrate, present_ill_history, past_history, system_retrospect,
        personal_history, physical_exam_record, create_time')
            ->where(['id' => $id])
            ->find();
        return $res;
    }

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
     * 添加患者信息
     * @param $data
     * @return array
     */
    public function addData($data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)){
            $this->save($data);
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

    /**
     * 过滤必要字段
     * @param $data
     * @return array
     */
    private function filterField($data){
        $ret = [];
        $errors = [];
        if(isset($data['source_user_id']) && !$data['source_user_id']){
            $errors['source_user_id'] = '发送用户不能为空';
        }
        if(isset($data['target_user_id']) && !$data['target_user_id']){
            $errors['target_user_id'] = '接收用户不能为空';
        }
        if(isset($data['title']) && !$data['title']){
            $errors['title'] = '标题不能为空';
        }
        if(isset($data['content']) && !$data['content']){
            $errors['content'] = '内容不能为空';
        }
        if(isset($data['operation']) && !$data['operation']){
            $errors['operation'] = '操作不能为空';
        }
        if(isset($data['priority']) && !$data['priority']){
            $errors['priority'] = '优先级不能为空';
        }
        return $errors;
    }
}
?>