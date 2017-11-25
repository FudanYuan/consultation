<?php
/**
 * 会诊申请模型
 * Author yzs
 * Create 2017.10.26
 */
namespace app\model;

use think\Model;
use think\Db;

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
     * @param string $select
     * @param array $cond_or
     * @param array $cond_and
     * @param array $order
     * @return mixed
     */
    public function getList($select='*',$cond_or=[],$cond_and=[],$order=[]){
        if(!isset($cond_and['a.status'])){
            $cond_and['a.status'] = ['<>', 0];
        }
        $res = $this->alias('a')->field($select)
            ->join('consultation_user_admin b','b.id = a.source_user_id')
            ->join('consultation_apply_user c','c.apply_id = a.id')
            ->join('consultation_user_admin d','d.id = c.target_user_id')
            ->join('consultation_doctor e','e.id = b.doctor_id')
            ->join('consultation_hospital_office f','f.id = e.hospital_office_id')
            ->join('consultation_hospital g','g.id = f.hospital_id')
            ->join('consultation_doctor h','h.id = d.doctor_id')
            ->join('consultation_hospital_office i','i.id = h.hospital_office_id')
            ->join('consultation_hospital j','j.id = i.hospital_id')
            ->join('consultation_patient k','k.id = a.patient_id')
            ->join('consultation_office l','l.id = f.office_id')
            ->join('consultation_office m','m.id = i.office_id')
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
            $data['create_time'] = $_SERVER['REQUEST_TIME'];

            $target_user_ids = $data['target_user_ids'];
            unset($data['target_user_ids']);

            Db::startTrans();
            $flag = true;
            $apply_id = Db::table('consultation_apply')->insertGetId($data);
            if($apply_id){
                // to do
                $lines = $this->addApplyUser($target_user_ids, $apply_id);
                if($lines != count($target_user_ids)){
                    $errors['msg'] = '添加行数不相等';
                    $flag = false;
                }
            }else{
                $errors['msg'] = '申请新建失败';
                $flag = false;
            }
            if($flag){
                Db::commit();
            }else{
                Db::rollback();
            }
        }
        return $ret;
    }

    /**
     * 添加角色权限
     * @param $target_user_ids
     * @param $apply_id
     * @return int|string
     */
    public function addApplyUser($target_user_ids, $apply_id){
        $data = [];
        $time = $_SERVER['REQUEST_TIME'];
        foreach($target_user_ids as $v){
            array_push($data, ['apply_id' => $apply_id, 'target_user_id' => $v, 'status' => 1, 'create_time' => $time, 'update_time' => $time]);
        }
        return Db::table('consultation_apply_user')->insertAll($data);
    }

    /**
     * 批量增加会诊申请
     * @param $dataSet
     * @return array
     */
    public function addAllData($dataSet){
        $ret = [];
        foreach ($dataSet as &$data) {
            $errors = $this->filterField($data);
            $ret['errors'] = $errors;
            if(!empty($errors)){
                return $ret;
            }
            $data['create_time'] = $_SERVER['REQUEST_TIME'];
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
                $data['update_time'] = $_SERVER['REQUEST_TIME'];
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
        $res = $this->save(['status' => 2, 'update_time' => $_SERVER['REQUEST_TIME']], $cond);
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
            $errors['consultation_goal'] = '会诊要求及目的不能为空';
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
        if(isset($data['other_apply_project']) && $data['apply_project'] == 4 && !$data['other_apply_project']){
            $errors['other_apply_project'] = '其他申请类型不能为空';
        }
        if(isset($data['consultation_result']) && !$data['consultation_result']){
            $errors['consultation_result'] = '会诊单位意见不能为空';
        }
        if(isset($data['update_time']) && !$data['update_time']){
            $errors['update_time'] = '会诊时间不能为空';
        }
        if(isset($data['price']) && !$data['price']){
            $errors['price'] = '会诊费用不能为空';
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