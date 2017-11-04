<?php
/**
 * 任务网站类型
 * Created by PhpStorm.
 * User: acer-pc
 * Date: 2017/10/6
 * Time: 9:04
 */

namespace app\model;

use think\Model;


class TaskMediaType extends Model
{
    protected $table = 'vox_task_media_type';
    protected $fields = array(
        'task_id','website_id','status', 'createtime', 'updatetime'
    );
    protected $type = [
        'task_id' => 'integer',
        'media_type_id' => 'integer',
        'status' => 'integer',
        'createtime' => 'integer',
        'updatetime' => 'integer'
    ];


    /**
     * 添加任务——媒体类型
     * @param $data
     * @return array
     */
    public function addData($data){
        $ret = [];
        $curtime = time();
        $data['createtime'] = $curtime;
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)) {
            if (!isset($data['status']))
                $data['status'] = 1;
            Db('task_media_type')->insertGetId($data);
        }
        return $ret;
    }

    /**
     * 过滤任务——网站类型信息
     * @param $data
     * @return array
     */
    private function filterField($data){
        $errors = [];
        if (isset($data['task_id']) && !$data['task_id']) {
            $errors['task_id'] = '任务编号不能为空';
        }
        if (isset($data['media_type_id']) && !$data['media_type_id']) {
            $errors['media_type_id'] = '采集主题不能为空';
        }
        return $errors;
    }

    /**
     * 更新任务——媒体类型信息
     * {@inheritDoc}
     * @see \think\Model::save()
     */
    public function saveData($id, $data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if (empty($errors)) {
            $this->save($data, ['id' => $id]);
        }
        return $ret;
    }


    /**
     * 根据id获取任务——媒体类型信息
     * @param $id
     * @return mixed
     */
    public function getById($id){
        $res = $this->field('*')
            ->where(['id' => $id])
            ->find();
        return $res;
    }





    ///未修改///
    /**
     * 获取任务——网战类型列表
     * @param array $cond
     * @return mixed
     */
    public function getTaskWebTypeList($cond = []){
        $res = $this->field('id as type_id, name as type_name ')
            ->where("status <> '2'")
            ->select();
        return $res;
    }

}