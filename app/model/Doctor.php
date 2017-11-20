<?php
/**
 * 医生模型
 * Author FeiYu
 * Create 2017.11.5
 */
namespace app\model;

use think\Model;

class Doctor extends Model{
    protected $table = 'consultation_doctor';
    protected $pk = 'id';
    protected $fields = array(
        'id', 'hospital_office_id', 'name', 'photo', 'gender', 'age',
        'position', 'phone', 'email', 'address', 'postcode', 'info',
        'honor', 'remark', 'status', 'create_time', 'update_time'
    );
    protected $type = [
        'id' => 'integer',
        'hospital_office_id' => 'integer',
        'office_id' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];


    public function getList($cond = []){
        if(!isset($cond['status'])){
            $cond['status'] = ['<>', 2];
        }
        $res = $this->field('*')
            ->where($cond)
            ->select();
        return $res;
    }
    /**
     * 获取医生列表
     * @param $cond_and
     * @param $cond_or
     * @param $order
     * @return mixed
     */
    public function getDoctorList($cond_or=[],$cond_and=[],$order=[]){
        if(!isset($cond_and['a.status'])){
            $cond_and['a.status'] = ['<>', 2];
        }
        $res = $this->alias('a')->field('a.id,c.id as hospital_id,c.name as hospital_name,
            d.id as office_id,d.name as office_name,a.name as name,a.photo as photo, a.position as position,
            a.phone as phone,a.email as email,a.address as address')
            ->join('consultation_hospital_office b','b.id = a.hospital_office_id')
            ->join('consultation_hospital c','c.id = b.hospital_id')
            ->join('consultation_office d','d.id = b.office_id')
            ->where($cond_or)
            ->where($cond_and)
            ->order($order)
            ->select();
        return $res;
    }

    /**
     * 通过id获取医生信息
     * @param $id
     * @return mixed
     */
    public function getById($id){
        $res = $this->field('*')
            ->where(['id' => $id])
            ->find();
        return $res;
    }

    public function getDoctor($select,$cond){
        $res = $this->field($select)
            ->where($cond)
            ->select();
        return $res;
    }

    /**
     * 添加医生信息
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
     * 更新医生信息
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
     * 过滤必要字段
     * @param $data
     * @return array
     */
    private function filterField($data){
        $errors = [];
        if(isset($data['name']) && !$data['name']){
            $errors['name'] = '名字不能为空';
        }
        if(isset($data['gender']) && !$data['gender']){
            $errors['gender'] = '性别不能为空';
        }
        if(isset($data['age']) && !$data['age']){
            $errors['age'] = '年龄不能为空';
        }
        if(isset($data['position']) && !$data['position']){
            $errors['position'] = '职称不能为空';
        }
        if(isset($data['phone']) && !$data['phone']){
            $errors['phone'] = '电话不能为空';
        }
        if(isset($data['email']) && !$data['email']){
            $errors['email'] = '邮箱不能为空';
        }
        return $errors;
    }
    //////未修改/////

    /**
     * 批量增加医生信息
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
     * 删除医生信息
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