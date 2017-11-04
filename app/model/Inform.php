<?php
/**
 * 通知公告模型
 * Author yzs
 * Create 2017.10.26
 */
namespace app\model;

use think\Model;

class Inform extends Model{
    protected $table = 'vox_inform';
    protected $pk = 'id';
    protected $fields = array(
        'id', 'source_user_id','target_user_id', 'content',
        'operation', 'priority','status','createtime','updatetime'
    );
    protected $type = [
        'id' => 'integer',
        'source_user_id' => 'integer',
        'target_user_id' => 'integer',
        'priority' => 'integer',
        'status' => 'integer',
        'createtime' => 'integer',
        'updatetime' => 'integer'
    ];

    /**
     * 获取通知列表
     * @param array $cond
     */
    public function getList($cond = []){
        if(!isset($cond['status'])){
            $cond['status'] = ['<>', 2];
        }
        $res = $this->field('id,source_user_id,target_user_id,title,content,
        operation,priority,status,createtime')
            ->order('priority asc, createtime desc')
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
        operation,priority,status,createtime')
            ->where(['id' => $id])
            ->find();
        return $res;
    }

    /**
     * 更新通知公告
     * {@inheritDoc}
     * @see \think\Model::save()
     */
    public function saveData($id, $data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)){
            $data['updatetime'] = time();
            $this->save($data, ['id' => $id]);
        }
        return $ret;
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
            $this->save($data);
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