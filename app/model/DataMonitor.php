<?php
/**
 * 数据模型
 * Created by PhpStorm.
 * User: acer-pc
 * Date: 2017/10/4
 * Time: 11:05
 * or c.name like '%$cond% or a.event like '%$cond%'
 * and (b.name like '%$cond%')
 */
namespace app\model;

use think\Model;

class DataMonitor extends Model
{
    protected $table = 'vox_data';
    protected $pk = 'id';
    protected $fields = array(
        'id','theme','task_id','title','content','digest',
        'source','userID','media_type_id','nature','url','relevance','publishtime',
        'similar_num','is_collect','is_warn','status','createtime', 'updatetime');
    protected $type = [
        'id' => 'integer',
        'theme_id'=>'integer',
        'relevance' =>'integer',
        'media_id' => 'integer',
        'task_id'=>'integer',
        'similar_num' => 'integer',
        'is_collect' => 'integer',
        'is_warn' => 'integer',
        'status' => 'integer',
    ];

    /**
     * 获取总舆情量
     */
    public function getDataNumber(){
        $res = $this->field('count(id) as data')
            ->where('status <> 2')
            ->select();
        return $res[0]['data'];
    }

    /**
     * 获取舆情ById
     * @param $id
     * @return mixed
     */
    public function getDataById($id){
        $res = $this->field('*')
            ->where(['id' => $id])
            ->find();
        return $res;
    }

    /**
     * 获取舆情属性数量
     * @param $cond
     * @return mixed
     */
    public function getNatureNum($cond){
        if(!isset($cond['status'])){
            $cond['status'] = ['<>', 2];
        }
        $res = $this->field('count(id) as natureNum')
            ->where($cond)
            ->select();
        return $res[0]['natureNum'];
    }

    /**
     * 获取舆情列表
     * @param $cond_or
     * @param $cond_and
     * @param $order
     * @return mixed
     */
    public function  publicList($cond_or,$cond_and,$order){
        if(!isset($cond_and['a.status'])){
            $cond_and['a.status'] = ['<>', 2];
        }
        $res = $this->alias('a')->field('a.id as id,a.title as title, a.source as source,a.url as url,b.name as media_type,a.nature as nature,
            a.publishtime as publishtime,a.content as content,a.similar_num as similar_num,a.relevance as relevance,a.is_collect as is_collect')
            ->join('vox_media_type b','a.media_type_id = b.id')
            ->where($cond_or)
            ->where($cond_and)
            ->order($order)
            ->select();
        return $res;
//        $res = $this->field('*')->select();
//        for($i=0;$i<count($res);$i++){
//            $this->where('id',$res[$i]['id'])->update(['task_id' =>(($i%12)+1)]);
//        }

    }

    /**
     * 导出数据表
     * @return mixed
     */
    public function getListExport(){
        $res = $this
            ->where('status <> 2')
            ->select();
        for($i = 0;$i<count($res);$i++){
            $res[$i]['publishtime'] = date('Y-m-d H:i:s',$res[$i]['publishtime']);
            $res[$i]['createtime'] = date('Y-m-d H:i:s',$res[$i]['createtime']);
            $res[$i]['updatetime'] = date('Y-m-d H:i:s',$res[$i]['updatetime']);
        }
        return $res;
    }

    /**
     * 过滤舆情信息
     * @param $data
     * @return array
     */
    private function filterField($data){
        $errors = [];
        if (isset($data['theme']) && !$data['theme']) {
            $errors['theme'] = '主题不能为空';
        }
        if (isset($data['url']) && !$data['url']) {
            $errors['url'] = '企业网址不能为空';
        }
        if (isset($data['task_id']) && !$data['task_id']) {
            $errors['task_id'] = '任务不能为空';
        }
        if (isset($data['title']) && !$data['title']) {
            $errors['title'] = '标题不能为空';
        }
        if (isset($data['is_collect']) && !$data['is_collect']) {
            $errors['is_collect'] = '是否收藏不能为空';
        }
        return $errors;
    }

    /**
     * 更新舆情信息
     * @param $id
     * @param $data
     * @return array
     */
    public function saveData($data,$id){
        $ret = [];
            $curTime = time();
            $data['updatetime'] = $curTime;
            $data_save = [];
            $data_save['id'] = $data['id'];
            $data_save['theme'] = $data['theme'];
            $data_save['media_type_id'] = $data['media_type_id'];
            $data_save['task_id'] = $data['task_id'];
            $data_save['title'] = $data['title'];
            $data_save['content'] = $data['content'];
            $data_save['source'] = $data['source'];
            $data_save['nature'] = $data['nature'];
            $data_save['url'] = $data['url'];
            $data_save['relevance'] = $data['relevance'];
            $data_save['publishtime'] = $data['publishtime'];
            $data_save['similar_num'] = $data['similar_num'];
            $data_save['is_collect'] = $data['is_collect'];
            $data_save['is_warn'] = $data['is_warn'];
            $data_save['status'] = $data['status'];
            $data_save['createtime'] = $data['createtime'];
            $data_save['updatetime'] = $data['updatetime'];
            $this->save($data_save, ['id' => $id]);
        return $ret;
    }

    /**
     * 删数据
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public function remove($cond = []){
        $res = $this->save(['status' => 2], $cond);
        if ($res === false) throw new MyException('2', '删除失败');
        return $res;
    }



    ////未修改/////
    /**
     * 数据同比上周增加%比
     */
    public function getPercentNumber(){
        $totalNum = $this->field('count(id) as t_num')
            ->where('status <> 2')
            ->select();
        $lastWeekUpdateNum = $this->field('count(id) as lw_num')
            ->where('status <> 2')
            ->wheretime('createtime','last week')
            ->select();
        $thisWeekUpdateNum = $this->field('count(id) as tw_num')
            ->where('status <> 2')
            ->wheretime('createtime','week')
            ->select();
        $thisYearUpdateNum = $this->field('count(id) as ty_num')
            ->where('status <> 2')
            ->wheretime('createtime','year')
            ->select();

        if($totalNum[0]['t_num']!=0){
            $percent = ($thisWeekUpdateNum[0]['tw_num'] / $totalNum[0]['t_num'])*100;
        }else{
            $percent = 100;
        }
        return $percent;
    }

    /**
     * 获取数据列表--条件
     * @param $cond_or
     * @param $cond_and
     * @param $order
     * @param int $pag
     * @return mixed
     */
    public function getDataCondition($cond_or,$cond_and,$order,$pag = 10){
        if(!isset($cond_and['a.status'])){
            $cond_and['a.status'] = ['<>', 2];
        }
        if($pag == -1){
            $pag = $this->getDataNumber();
        }
        $res = $this->alias('a')->field('a.id as id,e.id as t1_id,d.id as t2_id,c.id as t3_id,
                    e.name as t1_name,d.name as t2_name,c.name as t3_name,b.name as websitetypename,a.url as url,
                    a.task_id as task_id,f.name as task_name,a.createtime as createtime,
                    a.content as content,a.source as source,a.media_type as media_type,
                    a.nature as nature,a.url as url,a.relevance as relevance,a.time as time
                    a.similar_num as similar_num')
            ->join('vox_website_type b','b.id = a.websitetype_id')
            ->join('vox_theme_3 c','c.id = a.theme_3_id')
            ->join('vox_theme_2 d','d.id = c.t2_id')
            ->join('vox_theme_1 e','e.id = d.t1_id')
            ->join('vox_task f','f.id = a.task_id')
            ->whereor($cond_or)
            ->where($cond_and)
            ->order($order)
            ->paginate($pag);
        return $res;
    }

    /**
     * 获取数据table--Company
     * @param $id
     * @return mixed
     */
    public function getTable($id){
        $res = $this->field('table')
            ->where(['id' => $id])
            ->find();
        return $res;
    }
    /**
     * 添加数据
     * @param $data
     * @return array
     */
    public function addData($data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if (empty($errors)) {
            $curTime = time();
            if (isset($data['ispublish']) && $data['ispublish'])
                $data['publishtime'] = $curTime;
            if (!isset($data['status']))
                $data['status'] = 1;
            $this->save($data);
        }
        return $ret;
    }




    /**
     * 根据id获取数据信息
     * @param $id
     * @return mixed
     */
    public function getById($id){
        $res = $this->field('*')
            ->where(['id' => $id])->find();
        return $res;
    }

    /**
     * 数据气泡图
     * @param $cond_or
     * @param $cond_and
     * @param $limit
     * @return mixed
     */
    public function getBubbleData($cond_or,$cond_and,$limit){
        $cond = [];
        if(!isset($cond['a.status'])){
            $cond['a.status'] = ['<>', 2];
        }
        $res = $this->alias('a')->field(
            'a.id as id,c.name as name,count(a.id) as num')
            ->join('tax_theme_3 b','a.theme_3_id = b.id')
            ->join('tax_company c','a.c_id = c.id')
            ->whereor($cond_or)
            ->where($cond_and)
            ->where($cond)
            ->group('c.id')
            ->order('count(a.id) desc')
            ->limit($limit)
            ->select();
        return $res;
    }


    /**
     * 数据柱状图
     * @param array $data
     * @return mixed
     */
    public function getBarData($data=[]){
        $cond_or = [];
        $cond_and = [];
        if(!isset($cond_and['a.status'])){
            $cond_and['a.status'] = ['<>', 2];
        }
        ///数量限制///
        if(empty($data['bar_num_limit'])||(isset($data['bar_num_limit']) && !$data['bar_num_limit'])||$data['bar_num_limit'] == -1){
            $limit = $this->getDataNumber();
        }else{
            $limit = $data['bar_num_limit'];
        }
        ///起止时间限制///
        if(empty($data['begintime_str'])||(isset($data['begintime_str']) && !$data['begintime_str'])){
            $begin_time = 0;
        }else{
            $begin_time = strtotime($data['begintime_str']);
        }
        if(empty($data['endtime_str'])||(isset($data['endtime_str']) && !$data['endtime_str'])){
            $end_time = time();
        }else{
            $end_time = strtotime($data['endtime_str']);
        }
        ///主题限制///
        if(empty($data['bar_theme_limit'])||(isset($data['bar_theme_limit']) && !$data['bar_theme_limit'])||$data['bar_theme_limit']==-1){
        }else{
            $cond_and['b.id'] = ['=', $data['bar_theme_limit']];
        }
        $cond = "$begin_time < a.createtime and a.createtime < $end_time";
        $res = $this->alias('a')->field('a.id as id , c.name as name , count(distinct(b.id)) as num')
            ->join('tax_theme_3 b', 'a.theme_3_id = b.id')
            ->join('tax_company c', 'a.c_id = c.id')
            ->whereor($cond_or)
            ->where($cond_and)
            ->where($cond)
            ->group('c.id')
            ->limit($limit)
            ->order('count(distinct(b.id)) desc')
            ->select();
        return $res;
    }

    /**
     * 获取饼状图
     * @param $data
     * @return mixed
     */
    public function getTypePie($data){
        $cond_and = [];
        $cond_or = [];
        if(isset($data['type'])){
            $cond_and['c.name'] = ['=',$data['type']];
        }
        if (!isset($cond_and['a.status'])) {
            $cond_and['a.status'] = ['<>', 2];
        }
        ///起止时间限制///
        if(empty($data['begintime_str'])||(isset($data['begintime_str']) && !$data['begintime_str'])){
            $begin_time = 0;
        }else{
            $begin_time = strtotime($data['begintime_str']);
        }
        if(empty($data['endtime_str'])||(isset($data['endtime_str']) && !$data['endtime_str'])){
            $end_time = time();
        }else{
            $end_time = strtotime($data['endtime_str']);
        }
        $cond = "$begin_time < a.createtime and a.createtime < $end_time";

        $res = $this->alias('a')->field('b.name as name,count(a.id) as value')
            ->join('tax_theme_3 b', 'a.theme_3_id=b.id')
            ->join('tax_website_type c','a.websitetype_id = c.id')
            ->whereor($cond_or)
            ->where($cond_and)
            ->where($cond)
            ->group('b.id')
            ->order('count(a.id) desc')
            ->limit(15)
            ->select();
        return $res;
    }


    /**
     * 获取主题-网站类型图
     * @param $data
     * @return mixed
     */
    public function getThemePie($data){
        $cond_and = [];
        $cond_or = [];
        $cond_and['b.name'] = ['=' , $data['theme']];
        if (!isset($cond_and['a.status'])) {
            $cond_and['a.status'] = ['<>', 2];
        }
        ///起止时间限制///
        if(empty($data['begintime_str'])||(isset($data['begintime_str']) && !$data['begintime_str'])){
            $begin_time = 0;
        }else{
            $begin_time = strtotime($data['begintime_str']);
        }
        if(empty($data['endtime_str'])||(isset($data['endtime_str']) && !$data['endtime_str'])){
            $end_time = time();
        }else{
            $end_time = strtotime($data['endtime_str']);
        }
        $cond = "$begin_time < a.createtime and a.createtime < $end_time";
        $res = $this->alias('a')->field('c.name as name,count(a.id) as value')
            ->join('tax_theme_3 b', 'a.theme_3_id=b.id')
            ->join('tax_website_type c','a.websitetype_id = c.id')
            ->whereor($cond_or)
            ->where($cond_and)
            ->where($cond)
            ->group('c.id')
            ->order('count(a.id) desc')
            ->limit(15)
            ->se分removelect();
        return $res;
    }

    /**
     * 测试用  添数据
     */
    public function getTypePie2(){
        $list = $this->field('*')->select();
        for($i = 0;$i < count($list);$i++ ){
            $type = ($i%5)+1;
            $this->update(['websitetype_id' => $type,'id' => $list[$i]['id']]);
        }

    }


    /**
     * 将字符串时间转化成时间戳
     * @param unknown $data
     */
    private function timeTostamp_begin(&$data){
        isset($data['begintime_str']) && $data['begintime'] = $data['begintime_str'] ? strtotime($data['begintime_str']) : 0;
    }
    private function timeTostamp_end(&$data){
        isset($data['endtime_str']) && $data['endtime'] = $data['endtime_str'] ? strtotime($data['endtime_str']) : 0;
    }
}