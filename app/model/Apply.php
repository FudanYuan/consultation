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
        'apply_date','consultation_result','is_green_channel','price','is_charge',
        'status','create_time','update_time'
    );
    protected $type = [
        'id' => 'integer',
        'patient_id' => 'integer',
        'source_user_id' => 'integer',
        'target_hospital_id' => 'integer',
        'is_green_channel' => 'integer',
        'apply_date' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];

    private $strField = ['apply_date', 'create_time', 'update_time'];

    /**
     * 获取申请信息列表
     * @param $cond_and
     * @param $cond_or
     * @param $order
     * @return mixed
     */
    public function getList($cond_and=[],$cond_or=[],$order=[]){
        if(!isset($cond_and['a.status'])){
            $cond_and['a.status'] = ['<>', 0];
        }
        $res = $this->alias('a')->field('a.id as id,b.id as user_id, e.id as hospital_id,e.logo as hospital_logo,
                e.name as hospital_name,c.id as doctor_id,c.name as doctor_name,
                c.phone as phone,apply_type,apply_project,other_apply_project,is_green_channel,
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
     * 获取状态
     * @param $id
     * @return mixed
     */
    public function getStatusById($id){
        $res = $this->field('status')
            ->where(['id' => $id])
            ->find();
        return $res;
    }

    /**
     * 添加会诊申请
     * @param $data
     * @return array
     */
    public function addData($data){
        $ret = [];
        $this->timeTostamp($data);
        $this->unsetOhterField($data);
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
     * 批量增加会诊申请
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
     * 更新状态
     * @param array $cond
     * @param $status
     * @return false|int
     * @throws MyException
     */
    public function UpdateStatus($cond = [], $status){
        $res = $this->save(['status' => $status], $cond);
        if($res === false) throw new MyException('1', '标记失败');
        return $res;
    }

    /**
     *  更新会诊申请
     * @param $id
     * @param $data
     * @return array
     */
    public function saveData($id, $data){
        $ret = [];
        $this->timeTostamp($data);
        $this->unsetOhterField($data);
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)){
            if(!isset($data['update_time'])){
                $data['update_time'] = time();
            }
            $this->save($data, ['id' => $id]);
        }
        return $ret;
    }

    /**
     * 标记为已读
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public function markRead($cond = []){
        $res = $this->save(['status' => 2, 'update_time' => time()], $cond);
        if($res === false) throw new MyException('2', '标记失败');
        return $res;
    }

    /**
     * 删除会诊申请
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public function remove($cond = []){
        $res = $this->save(['status' => 0], $cond);
        if($res === false) throw new MyException('1', '删除失败');
        return $res;
    }

    /**
     * 过滤数据库不需要的字符串字段
     * @param $data
     */
    private function unsetOhterField(&$data)
    {
        foreach ($this->strField as $v) {
            $str = $v . '_str';
            if (isset($data[$str])) unset($data[$str]);
        }
    }

    /**
     * 转时间戳
     * @param $data
     */
    private function timeTostamp(&$data)
    {
        isset($data['apply_date_str']) && $data['apply_date'] = $data['apply_date_str'] ?
            strtotime($data['apply_date_str']) : 0;
        isset($data['update_time_str']) && $data['update_time'] = $data['update_time_str'] ?
            strtotime($data['update_time_str']) : 0;
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
        if(isset($data['target_office_ids']) && $data['target_office_ids'] == '-'){
            $errors['target_office_id'] = '申请科室不能为空';
        }
        if(isset($data['apply_project']) && !$data['apply_project']){
            $errors['apply_project'] = '申请会诊类型不能为空';
        }
        return $errors;
    }


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
}
?>