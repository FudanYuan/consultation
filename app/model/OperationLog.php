<?php
/**
 * 日志模型
 * Author yzs
 * Create 2017.10.26
 */
namespace app\model;

use think\Model;

class OperationLog extends Model{
    protected $table = 'consultation_operation_log';
    protected $pk = 'id';
    protected $fields = array(
        'id', 'user_id','section', 'IP', 'action_descr', 'status','create_time','update_time'
    );
    protected $type = [
        'id' => 'integer',
        'user_id' => 'integer',
        'status' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];

    /**
     * 获取日志列表
     * @param array $cond
     */
    public function getList($cond = []){
        if(!isset($cond['status'])){
            $cond['status'] = ['<>', 2];
        }
        $res = $this->field('user_id,section,IP,action_descr,create_time')
            ->order('create_time desc')
            ->where($cond)
            ->select();
        return $res;
    }

    /**
     * 添加日志
     * @param $data
     * @return array
     */
    public function addData($data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)){
            $data['create_time'] = time();
            if(!isset($data['status']))
                $data['status'] = 1;
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
        $ret = [];
        $errors = [];
        if(isset($data['user_id']) && !$data['user_id']){
            $errors['user_id'] = '用户不能为空';
        }
        if(isset($data['IP']) && !$data['IP']){
            $errors['IP'] = 'IP不能为空';
        }
        if(isset($data['section']) && !$data['section']){
            $errors['section'] = '操作模块不能为空';
        }
        if(isset($data['action_descr']) && !$data['action_descr']){
            $errors['action_descr'] = '操作事项不能为空';
        }
        return $errors;
    }
}
?>