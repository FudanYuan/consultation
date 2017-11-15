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
        'id', 'apply_id','source_user_id','target_user_id','words_info', 'files_info',
        'time','status','create_time','update_time'
    );
    protected $type = [
        'id' => 'integer',
        'apply_id' => 'integer',
        'source_user_id' => 'integer',
        'target_user_id' => 'integer',
        'time' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];

    /**
     * 获取消息列表
     * @param array $cond
     */
    public function getList($cond = []){
        if(!isset($cond['status'])){
            $cond['status'] = ['<>', 2];
        }
        $res = $this->field('id,apply_id,source_user_id,target_user_id,words_info, 
        files_info,time,status,create_time,update_time')
            ->order('create_time desc')
            ->where($cond)
            ->select();
        return $res;
    }

    /**
     * 根据ID获取消息列表
     * @param $id
     * @return mixed
     */
    public function getById($id){
        $res = $this->field('id,apply_id,source_user_id,target_user_id,words_info, 
        files_info,time,status,create_time,update_time')
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
     * 批量增加消息列表
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
        $res = $this->save(['status' => 1], $cond);
        if($res === false) throw new MyException('2', '标记失败');
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
        return $errors;
    }
}
?>