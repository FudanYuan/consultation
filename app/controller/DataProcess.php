<?php
/**
 * 数据处理--控制器
 * Created by PhpStorm.
 * User: acer-pc
 * Date: 2017/10/5
 * Time: 0:45
 */
namespace app\controller;

class DataProcess extends Common{

    /**
     * 数据清洗
     * @return mixed
     */
    public function clean(){
        $ret = ['theme_list' => [], 'list' => [],'params' => []];
        return view('', $ret);
    }

    /**
     *  数据清洗
     * @return mixed
     */
    public function update_backup(){
        $ret = ['theme_list' => [], 'list' => [],'params' => []];
        return view('', $ret);
    }

    /**
     * 公司数据列表
     */
    public function info(){
        $params = input('get.');
        $c_name = input('get.c_name', '');
        $theme2_id = input('get.theme_id',-1);
        $tag_id = input('get.tag_id',-1);
        $keywords = input('get.keywords', '');
        $order = input('get.sortCol', 'a.id');
        $stime = input('get.begintime_str', '');
        $etime = input('get.endtime_str', '');
        $cond_and = [];
        $cond_or = [];
        if(!$order) {
            $params['sortCol'] = 'a.id asc';
        }
        if($c_name){
            $cond_and['b.name'] = ['like','%'.$c_name.'%'];
        }
        if($keywords){
            $cond_or['b.name'] = ['like','%'.$keywords.'%'];
            $cond_or['c.name'] = ['like','%'.$keywords.'%'];
            $cond_or['d.name'] = ['like','%'.$keywords.'%'];
            $cond_or['e.name'] = ['like','%'.$keywords.'%'];
            $cond_or['a.url']  = ['like','%'.$keywords.'%'];
            $cond_or['a.event'] =['like','%'.$keywords.'%'];
        }
        if($theme2_id != -1){
            $theme3_list = D('Theme')->getT3ByT2id($theme2_id);
            $theme3_ids = [];
            for($i = 0; $i < count($theme3_list); $i++){
                array_push($theme3_ids, $theme3_list[$i]['t3_id']);
            }
            $cond_and['c.id'] = ['in', $theme3_ids];
        }
        if($tag_id != -1){
            $cond = D('Tag')->getCompanyTag($tag_id);
            $cond_and = array_merge($cond_and,$cond);
        }

        if($stime && $etime){
            $cond_and['a.createtime'] = ['between', [strtotime($stime), strtotime($etime)]];
        }
        else if(!$stime && $etime){
            $cond_and['a.createtime'] = ['between', [0, strtotime($etime)]];
        }
        else if($stime && !$etime){
            $cond_and['a.createtime'] = ['between', [strtotime($stime), time()]];
        }

        $tags = D('Tag')->getList(['section' => 5]);
        $data = D('DataMonitor')->getDataCondition($cond_or,$cond_and,$order);

        $theme_list = D('Theme')->getT1List([],[],[]);
        for($i = 0; $i < count($theme_list); $i++){
            $cond['b.id'] = ['=',$theme_list[$i]['t1_id']];
            $theme_2_list = D('Theme')->getT2List([],$cond,[]);
            $theme_list[$i]['t1_content'] = $theme_2_list;
        }

        $ret = ['theme_list' => $theme_list, 'list' => $data, 'tags' => $tags,'cond' => $params];
        return view('', $ret);
    }


    /**
     * 获取数据量
     */
    public function datamonitor_Number(){
        $data_number = D('DataMonitor')->getDataNumber();
        return view('',['data_count' => $data_number]);
    }

    /**
     * 添加数据
     */
    public function datamonitor_create(){
        $data = input('post.');
        if (!empty($data)) {
            $res = D('DataMonitor')->addData($data);
            if (!empty($res['errors'])) {
                return view('', ['errors' => $res['errors'], 'data' => $data]);
            } else {
                $url = PRO_PATH . '/DataMonitor/index';
                return "<script>window.location.href='" . $url . "'</script>";
            }
        }
    }

    /**
     * 编辑数据信息
     */
    public function datamonitor_edit(){
        $id = input('get.id');
        $data = input('post.');
        if (!empty($data)) {
            $res = D('DataMonitor')->saveData($id, $data);
            if (!empty($res['errors'])) {
                return view('', ['errors' => $res['errors'], 'data' => $data]);
            } else {
                $url = PRO_PATH . '/DataMonitor/index';
                return "<script>window.location.href='" . $url . "'</script>";
            }
        } else {
            $data = D('DataMonitor')->getById($id);
            return view('', ['errors' => [], 'data' => $data]);
        }
    }

    /**
     * 数据气泡图
     */
    public function  getBubbleData(){
        $data = input('get.');
        $ret = ['errorcode' => 0, 'data' => [], 'msg' => ''];
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
        if(empty($data['bubble_num_limit'])||(isset($data['bubble_num_limit']) && !$data['bubble_num_limit'])){
            $limit = D('DataMonitor')->getDataNumber();
        }else {
            if ($data['bubble_num_limit'] == -1) {
                $limit = D('DataMonitor')->getDataNumber();
            } else {
                $limit = $data['bubble_num_limit'];
            }
        }
        $cond = "$begin_time < a.createtime and a.createtime < $end_time";
        $list = D('DataMonitor')->getBubbleData([],$cond,$limit);
        $ret['data'] = $list;
        $this->jsonReturn($ret);
    }

    /**
     * 数据柱状图
     */
    public function getBarData(){
        $data = input('get.');
        $ret = ['errorcode' => 0, 'data' => [], 'msg' => ''];
        $list = D('DataMonitor')->getBarData($data);
        $ret['data'] = $list;
        $this->jsonReturn($ret);
    }


    /**
     * 数据删除
     */
    public function datamonitor_remove(){
        $ret = ['code' => 1, 'msg' => '成功'];
        $ids = input('get.ids');
        try {
            $res = D('DataMonitor')->remove(['id' => ['in', $ids]]);
        } catch (MyException $e) {
            $ret['code'] = 2;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 统计网站类型与主题的关系
     */
    public function websiteThemePie(){
        $data = input('get.');
        $ret = ['errorcode' => 0, 'data' => [], 'msg' => ''];
        $list = D('DataMonitor')->getTypePie($data);
        $ret['data'] = $list;
        $this->jsonReturn($ret);
    }
}