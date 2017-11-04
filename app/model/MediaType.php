<?php
/**
 * 网站类型模型
 * Created by PhpStorm.
 * User: acer-pc
 * Date: 2017/10/6
 * Time: 9:04
 */

namespace app\model;

use think\Model;


class MediaType extends Model
{
    protected $table = 'vox_media_type';
    protected $pk = 'id';
    protected $fields = array(
        'id','name','status', 'createtime', 'updatetime'
    );
    protected $type = [
        'id' => 'integer',
        'type_id' => 'integer',
        'status' => 'integer',
        'createtime' => 'integer',
        'updatetime' => 'integer'
    ];

    /**
     * 获取网战类型数量
     * @return mixed
     */
    public function getMedTypeNumber(){
        $res = $this->field('count(id) as type_num')->select();
        return $res[0]['url'];
    }


    /**
     * 获取网战类型列表
     * @param array $cond
     * @return mixed
     */
    public function getMedTypeList($cond = []){
        $res = $this->field('id as type_id, name as type_name ')
            ->where("status <> '2'")
            ->select();
        return $res;
    }

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




    /**
     * 更新网站类型信息
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
     * 添加网站类型
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
     * 过滤网站类型信息
     * @param $data
     * @return array
     */
    private function filterField($data){
        $errors = [];
        if (isset($data['name']) && !$data['name']) {
            $errors['name'] = '网站类型名字不能为空';
        }else{
            $cond_and = [];
            $cond_and['status'] = ['<>', 2];
            $cond_and['name'] = ['=',$data['name']];
            $list = $this->field('*')
                ->where($cond_and)
                ->find();
            if(!empty($list)){
                $errors['name'] = '网站类型不能重复';
            }
        }
        return $errors;
    }
}