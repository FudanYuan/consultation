<?php
/**
 * 会诊沟通模型
 * Author FeiYu
 * Create 2017.11.5
 */
namespace app\model;

use think\Model;

class Chat extends Model{
    protected $table = 'consultation_chat';
    protected $pk = 'id';
    protected $fields = array(
        'id', 'apply_id', 'source_user_id', 'target_user_id', 'type', 'content',
        'content_origin', 'status', 'create_time', 'update_time'
    );
    protected $type = [
        'id' => 'integer',
        'apply_id' => 'integer',
        'source_user_id' => 'integer',
        'target_user_id' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];

    /**
     * 获取消息列表
     * @param array $cond
     * @return mixed
     */
    public function getList($cond = []){
        if(!isset($cond_and['status'])){
            $cond_and['status'] = ['<>', 2];
        }
        $res = $this->field('*')
            ->where($cond)
            ->select();
        return $res;
    }

    /**
     * 获取用户列表
     * @param string $select
     * @param array $cond_or
     * @param array $cond_and
     * @param string $group
     * @return mixed
     */
    public function getUserList($select = '*',$cond_or = [],$cond_and = [], $group = ''){
        if(!isset($cond_and['a.status'])){
            $cond_and['a.status'] = ['<>', 2];
        }
        $res = $this->alias('a')->field($select)
            ->join('consultation_apply b','b.id = a.apply_id')
            ->join('consultation_user_admin c','c.id = a.source_user_id')
            ->join('consultation_apply_user d','d.apply_id = b.id')
            ->join('consultation_user_admin e','e.id = d.target_user_id')
            ->join('consultation_doctor f','f.id = c.doctor_id')
            ->join('consultation_hospital_office g','g.id = f.hospital_office_id')
            ->join('consultation_hospital h','h.id = g.hospital_id')
            ->join('consultation_office i','i.id = g.office_id')
            ->join('consultation_doctor j','j.id = e.doctor_id')
            ->join('consultation_hospital_office k','k.id = j.hospital_office_id')
            ->join('consultation_hospital l','l.id = k.hospital_id')
            ->join('consultation_office m','m.id = k.office_id')
            ->join('consultation_patient n','n.id = b.patient_id')
            ->where($cond_and)
            ->where($cond_or)
            ->group($group)
            ->select();
        return $res;
    }

    /**
     * 根据ID获取消息列表
     * @param $id
     * @return mixed
     */
    public function getById($id){
        $res = $this->field('id, apply_id, source_user_id, target_user_id, type, content,
        content_origin, status, create_time, update_time')
            ->where(['id' => $id])
            ->find();
        return $res;
    }

    /**
     * 更新消息列表
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
     * 添加消息列表
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
     * 批量增加消息
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
     * 删除消息列表
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
        $res = $this->save(['status' => 1, 'update_time' => time()], $cond);
        if($res === false) throw new MyException('2', '标记失败');
        return $res;
    }

    /**
     * 对结果进行过滤
     * @param $data
     * @param $fields
     * @return array
     */
    public function filterResult($data, $fields){
        $ret = [];
        for($i=0;$i<count($data);$i++){
            $temp = [];
            foreach ($data[$i] as $k => $value){
                if(in_array($k, $fields)){
                    $temp[$k] = $data[$i][$k];
                }
            }
            array_push($ret, $temp);
        }
        return $ret;
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
        if(isset($data['type']) && !$data['type']){
            $errors['type'] = '消息类型不能为空';
        }
        if(isset($data['content']) && !$data['content']){
            $errors['content'] = '内容不能为空';
        }

        return $errors;
    }
}
?>