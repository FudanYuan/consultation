<?php
/**
 * 会诊申请模型
 * Author yzs
 * Create 2017.10.26
 */
namespace app\model;

use think\Model;

class Apply extends Model{
    protected $table = 'consultation_apply';
    protected $pk = 'id';
    protected $fields = array(
        'id', 'patient_id','source_user_id', 'apply_type',
        'is_definite_purpose','target_hospital_id',
        'target_office_ids','target_doctor_ids',
        'consultation_goal', 'apply_project','other_apply_project',
        'apply_date','consultation_result','price','is_charge',
        'status','create_time','update_time'
    );
    protected $type = [
        'id' => 'integer',
        'patient_id' => 'integer',
        'source_user_id' => 'integer',
        'target_hospital_id' => 'integer',
        'apply_date' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];


    /**
     * 获取申请信息列表
     * @param $cond_and
     * @param $cond_or
     * @param $order
     * @return mixed
     */
    public function applyList($cond_and,$cond_or,$order){
        if(!isset($cond_and['a.status'])){
            $cond_and['a.status'] = ['<>', 0];
        }
        $res = $this->alias('a')->field('a.id as id,e.id as hospital_id,e.logo as hospital_logo,
                e.name as hospital_name,c.id as doctor_id,c.name as doctor_name,
                c.phone as phone,apply_type,apply_project,other_apply_project,
                consultation_goal,apply_date,a.status,price,is_charge,a.create_time')
                ->join('user_admin b','b.id = a.source_user_id')
                ->join('consultation_doctor c','c.id = b.doctor_id')
                ->join('consultation_hospital_office d','d.id = c.hospital_office_id')
                ->join('consultation_hospital e','e.id = d.hospital_id')
                ->where($cond_and)
                ->where($cond_or)
                ->order($order)
                ->select();
        return $res;
//        $res = $this->view('apply','id,source_user_id,
//                apply_type,consultation_goal,apply_date,status,price,
//                is_charge,create_time')
//                ->where($cond_and)
//                ->select();
    }

    /**
     * 添加通知公告
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
        if(isset($data['patient_id']) && !$data['patient_id']){
            $errors['patient_id'] = '病患不能为空';
        }
        if(isset($data['source_user_id']) && !$data['source_user_id']){
            $errors['source_user_id'] = '发送用户不能为空';
        }
        if(isset($data['apply_type']) && !$data['apply_type']){
            $errors['apply_type'] = '申请类型不能为空';
        }
        if(isset($data['consultation_goal']) && !$data['consultation_goal']){
            $errors['consultation_goal'] = '诊疗目的不能为空';
        }
        if(isset($data['apply_date']) && !$data['apply_date']){
            $errors['apply_date'] = '申请时间不能为空';
        }
        if(isset($data['target_hospital_id']) && !$data['target_hospital_id']){
            $errors['target_hospital_id'] = '申请医院不能为空';
        }
        if(isset($data['target_office_id']) && !$data['target_office_id']){
            $errors['target_office_id'] = '申请科室不能为空';
        }
        return $errors;
    }

    ///////未修改///////////

    /**
     * 去除非表字段
     * @param $data
     * @return array
     */
    public function unsetOtherField($data){
        $list = [];
        foreach ($this->fields as $v){
            $list[$v] = $data[$v];
        }
        return $list;
    }


    /**
     * 获取通知列表
     * @param array $cond
     */
    public function getList($cond = []){
        if(!isset($cond['status'])){
            $cond['status'] = ['<>', 2];
        }
        $res = $this->field('id,source_user_id,target_user_id,title,content,
        operation,priority,status,create_time')
            ->order('priority asc, create_time desc')
            ->where($cond)
            ->select();
        return $res;
    }

    /**
     * 根据ID获取通知公告
     * @param $id
     * @return mixed
     */
    public function getById($id){
        $res = $this->field('id,source_user_id,target_user_id,title,content,
        operation,priority,status,create_time')
            ->where(['id' => $id])
            ->find();
        return $res;
    }

    /**
     *  更新通知公告
     * @param $id
     * @param $data
     * @return array
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
     * 批量增加通知公告
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
     * 删除通知公告
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
     * 标记为已读
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public function markRead($cond = []){
        $res = $this->save(['status' => 1], $cond);
        if($res === false) throw new MyException('2', '标记失败');
        return $res;
    }


}
?>