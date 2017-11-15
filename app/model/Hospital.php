<?php
/**
 * 医院模型
 * Author FeiYu
 * Create 2017.11.5
 */
namespace app\model;

use think\Model;

class Hospital extends Model{
    protected $table = 'consultation_hospital';
    protected $pk = 'id';
    protected $fields = array(
        'id', 'name', 'master', 'logo', 'phone', 'url', 'email', 'address',
        'postcode', 'type', 'level', 'info', 'honor', 'role', 'status',
        'create_time', 'update_time'
    );
    protected $type = [
        'id' => 'integer',
        'role' => 'integer',
        'status' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];

    /**
     * 获取医院列表
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
     * 获取医院信息根据ID
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
     * @param $select
     * @param $cond
     * @return mixed
     */
    public function getHospital($select,$cond){
        if(!isset($cond['status'])){
            $cond['status'] = ['<>', 2];
        }
        $res = $this->field($select)
            ->where($cond)
            ->select();
        return $res;
    }

    /**
     * 添加医院信息
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
            $errors['name'] = '医院名字不能为空';
        }
        if(isset($data['master']) && !$data['master']){
            $errors['master'] = '医院院长不能为空';
        }
        if(isset($data['phone']) && !$data['phone']){
            $errors['phone'] = '联系方式不能为空';
        }
        if(isset($data['address']) && !$data['address']){
            $errors['address'] = '医院地址不能为空';
        }
        if(isset($data['email']) && !$data['email']){
            $errors['email'] = '医院邮箱不能为空';
        }
        if(isset($data['type']) && !$data['type']){
            $errors['type'] = '医院类型不能为空';
        }
        if(isset($data['level']) && !$data['level']){
            $errors['level'] = '医院等级不能为空';
        }
        if(isset($data['role']) && !$data['role']){
            $errors['role'] = '医院角色不能为空';
        }
        return $errors;
    }

    /**
     * 更新医院信息
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

    ////未修改/////

    /**
     * 批量增加医院信息
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
     * 删除医院信息
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