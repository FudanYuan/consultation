<?php
/**
 * 关键词预警模型
 * Created by PhpStorm.
 * User: acer-pc
 * Date: 2017/10/6
 * Time: 9:04
 */

namespace app\model;

use think\Model;


class KeywordWarn extends Model
{
    protected $table = 'vox_keyword_warn';
    protected $pk = 'id';
    protected $fields = array(
        'id','keyword','nature','media_type','status', 'createtime', 'updatetime'
    );
    protected $type = [
        'id' => 'integer',
        'status' => 'integer',
        'createtime' => 'integer',
        'updatetime' => 'integer'
    ];

    /**
     * 获取预警条件数量
     * @return mixed
     */
    public function getKeywordNumber(){
        $res = $this->field('count(id) as keyword_num')->select();
        return $res[0]['keyword_num'];
    }

    /**
     *  获取预警条件列表
     * @return mixed
     */
    public function getKeywordList(){
        $res = $this->field('*')
            ->order('id ')
            ->select();
        return $res;
    }

    /**
     * 添加预警条件
     * @param $data
     * @return array
     */
    public function addData($data){
        $ret = [];
        $curtime = time();
        $data['createtime'] = $curtime;
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if (empty($errors)) {
            if (!isset($data['status']))
                $data['status'] = 1;
            $this->save($data);
        }
        return $ret;
    }
    /**
     * 过滤预警条件信息
     * @param $data
     * @return array
     */
    private function filterField($data){
        $errors = [];

        if (isset($data['keyword']) && !$data['keyword']) {
            $errors['keyword'] = '预警词不能为空';
        }
        if ($data['nature'] == '-') {
            $errors['nature'] = '预警属性不能为空';
        }
        if ($data['media_type'] == '-') {
            $errors['media_type'] = '预警媒体不能为空';
        }
        return $errors;
    }

    /**
     * 更新预警条件信息
     * {@inheritDoc}
     * @see \think\Model::save()
     */
    public function saveData($id, $cond){
        $ret = [];
        $errors = $this->filterField($cond);
        $data = $this->unsetOtherField($cond);
        $ret['errors'] = $errors;
        if (empty($errors)) {
            $curTime = time();
            $data['updatetime'] = $curTime;
            $this->save($data, ['id' => $id]);
        }
        return $ret;
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

    ////未修改/////
    /**
     * 根据id获取网站类型信息
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
     * 获取本月网战类型增加数量
     */
    public function getPercentNumber(){
        $totalNum = $this->field('count(id) as t_num')
            ->select();
        $lastWeekUpdateNum = $this->field('count(id) as lw_num')
            ->wheretime('createtime','last week')
            ->select();
        $thisWeekUpdateNum = $this->field('count(id) as tw_num')
            ->wheretime('createtime','week')
            ->select();
        $thisYearUpdateNum = $this->field('count(id) as ty_num')
            ->wheretime('createtime','year')
            ->select();
        $thisMonthUpdateNum = $this->field('count(id) as tm_num')
            ->wheretime('createtime','month')
            ->select();
        $percent = $thisMonthUpdateNum[0]['tm_num'];
        return $percent;
    }

}