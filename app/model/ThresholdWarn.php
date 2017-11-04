<?php
/**
 * 警戒线设置模型
 * Created by PhpStorm.
 * User: acer-pc
 * Date: 2017/10/4
 * Time: 11:25
 */

namespace app\model;

use think\Model;


class ThresholdWarn extends Model
{
    protected $table = 'vox_threshold_warn';
    protected $pk = 'id';
    protected $fields = array(
        'id', 'task_id','day_all_count','day_negative_count','status','createtime','updatetime'
    );
    protected $type = [
        'id' => 'integer',
        'task_id' => 'integer',
        'day_all_count ' => 'integer',
        'day_negative_count' => 'integer',
        'status' => 'integer',
        'createtime' => 'integer',
        'updatetime' => 'integer'
    ];

    /**
     * 获取警戒线列表
     * @param $cond_or
     * @param $cond_and
     * @param $order
     * @return mixed
     */
    public function getWarnList($cond_or,$cond_and,$order){
        $res = $this->alias('a')->field('a.id as id,b.name as task,
            a.day_all_count as dayAllCount,a.day_negative_count as dayNegativeCount,
            a.status as status')
            ->join('vox_task b','a.task_id = b.id')
            ->where($cond_or)
            ->where($cond_and)
            ->order($order)
            ->select();
        return $res;
    }

    /**
     * 删除警戒线
     * @param $id
     * @return mixed
     * @throws MyException
     */
    public function remove($id){
        $res = $this->where('id',$id)->delete();
        if ($res === false) throw new MyException('2', '删除失败');
        return $res;
    }

    /**
     * 添加新警戒线
     * @param $data
     * @return array
     */
    public function addData($data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if (empty($errors)) {
            $data['createtime'] = time();
            $data['status'] = 1;
            $this->save($data);
        }
        return $ret;
    }

    /**
     * 更新警戒线信息
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
     * 过滤必要字段
     * @param $data
     * @return array
     */
    private function filterField($data)
    {
        $ret = [];
        $errors = [];
        if (isset($data['task_id']) && empty($data['task_id'])) {
            $errors['task'] = '预警任务不能为空';
        }
        if(isset($data['day_all_count'])&&$data['day_all_count']<0){
            $errors['dayAllCount'] = '预警总数不能负数';
        }
        if(isset($data['day_negative_count'])&&$data['day_negative_count']<0){
            $errors['dayNegativeCount'] = '负面预警数不能为负数';
        }
        return $errors;
    }

    ///未修改////
    /**
     * 获取总任务量
     */
    public function getTaskNumber()
    {
        $res = $this->field('count(id) as task')->select();
        return $res[0]['task'];
    }

    /**
     * 获取任务已完成数量
     */
    public function getCompletedNum()
    {
        $res = $this->field('count(id) as com_num')
            ->where('taskstatus = 2')
            ->select();
        return $res[0]['com_num'];
    }

    /**
     * 获取已完成任务所占百分比
     */
    public function getPercentCompleted()
    {
        $TotalNum = $this->field('count(id) as t_num')
            ->select();
        $CompletedNum = $this->field('count(id) as com_num')
            ->where('taskstatus = 2')
            ->select();
        if ($TotalNum[0]['t_num']) {
            $percent = ($CompletedNum[0]['com_num'] / $TotalNum[0]['t_num']) * 100;
        } else {
            $percent = 0;
        }
        return 0;
    }

    /**
     * 获取正在执行的任务数量
     */
    public function getTodealNum()
    {
        $res = $this->field('count(id) as to_num')
            ->where('taskstatus = 0 or taskstatus = 1')
            ->select();
    }


    /**
     * 继续任务
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public  function go_on($cond = []){
        $res = $this->save(['taskstatus' => 0], $cond);
        if ($res === false) throw new MyException('2', '继续失败');
        return $res;
    }

    /**
     * 结束任务
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public function end_task($cond = []){
        $res = $this->save(['taskstatus' => 2], $cond);
        if ($res === false) throw new MyException('2', '结束失败');
        return $res;
    }

    /**
     * 中断任务
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public function break_off($cond = []){
        $res = $this->save(['taskstatus' => 1], $cond);
        if ($res === false) throw new MyException('2', '中断失败');
        return $res;
    }

    ////////// 添加 //////////




    /**
     * 添加任务操作
     * @param $data
     * @return int|string
     */
    private function  save_1($data){
        $insert_data = ['loop'=>$data['loop'],'begintime' => strtotime($data['begintime_str']),'status' => $data['status']
            ,'createtime' => $data['createtime'],'taskstatus' => 0];
        $res = $this->insertGetId($insert_data);
        return $res;
    }



    /**
     * 清除非数据库字段
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
     * 将字符串时间转化成时间戳
     * @param $data
     */
    private function timeTostamp(&$data)
    {
        isset($data['begintime_str']) && $data['begintime'] = $data['begintime_str'] ? strtotime($data['begintime_str']) : 0;
        isset($data['endtime_str']) && $data['endtime'] = $data['endtime_str'] ? strtotime($data['endtime_str']) : 0;
    }
}