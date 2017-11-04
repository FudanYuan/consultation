<?php
/**
 * 数据分析--控制器
 * Created by shiren.
 * time 2017.10.19
 */
namespace app\controller;

class DataAnalysis extends Common
{
    public $exportCols = [];
    public $colsText = [];

    /**
     * 舆情概况
     * @return \think\response\View
     */
    public function index(){
        $params = input('get.');
        $task_id = input('get.task_id');
        return view('', ['params' => $params]);
    }

    /**
     * 趋势分析
     * @return \think\response\View
     */
    public function trend(){
        $params = input('get.');
        $task_id = input('get.task_id');
        return view('', ['params' => $params]);
    }

    /**
     * 搜索词分析
     * @return \think\response\View
     */
    public function searchwords(){
        $params = input('get.');
        $task_id = input('get.task_id');
        return view('', ['params' => $params]);
    }

    /**
     * 观点分析
     * @return \think\response\View
     */
    public function opinion(){
        $params = input('get.');
        $task_id = input('get.task_id');
        return view('', ['params' => $params]);
    }

    /**
     * 媒体分析
     * @return \think\response\View
     */
    public function media(){
        $params = input('get.');
        $task_id = input('get.task_id');
        return view('', ['params' => $params]);
    }

    /**
     * 传播分析
     * @return \think\response\View
     */
    public function spread(){
        $params = input('get.');
        $task_id = input('get.task_id');
        return view('', ['params' => $params]);
    }

    /**
     * 受众分析
     * @return \think\response\View
     */
    public function audience(){
        $params = input('get.');
        $task_id = input('get.task_id');
        return view('', ['params' => $params]);
    }

    /**
     * 事件分析
     * @return \think\response\View
     */
    public function event(){
        $params = input('get.');
        $task_id = input('get.task_id');
        return view('', ['params' => $params]);
    }

    /**
     * 获取舆情概况
     */
    public function getAnalysisIndex(){
        $params = input('post.');
        $task_id = input('post.task_id', -1);
        $ret = ['errorcode' => 0,'data' => [], 'msg' => ''];
        $cond = [];
        if($task_id == -1){
            $task_id = 3; //这里为测试，实际上要获取task表中最后一条有效数据的id
        }
        $ret['task_id'] = $task_id;
        $index = [];
        $index[0] = ['count' => 12219, 'search' => 1123, 'weibo' => 1212, 'note' => 1999, 'news' => 1231];
        $index[1] = ['count' => 12219, 'search' => 1123, 'weibo' => 1212, 'note' => 1999, 'news' => 1231];
        $index[2] = ['count' => 12219, 'search' => 1123, 'weibo' => 1212, 'note' => 1999, 'news' => 1231];
        $ret['index'] = $index;


        $nature = [];
        $nature_name = ['正面','负面','中立'];
        $i = 0;
        foreach ($nature_name as $nv){
            $cond['nature'] = ['=',$nv];
            $nature_value = D('DataMonitor')->getNatureNum($cond);
            $nature[$i] = ['name' => $nv, 'value' => $nature_value];
            $i++;
        }
        $ret['nature'] = $nature;

        $events = [];
        $events[0] = ['id' => 1, 'name' => '测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试', 'count' => 100];
        $events[1] = ['id' => 2, 'name' => '测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试', 'count' => 100];
        $events[2] = ['id' => 3, 'name' => '测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试', 'count' => 100];
        $events[3] = ['id' => 4, 'name' => '测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试', 'count' => 100];
        $events[4] = ['id' => 5, 'name' => '测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试', 'count' => 100];
        $events[5] = ['id' => 6, 'name' => '测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试', 'count' => 100];
        $events[6] = ['id' => 7, 'name' => '测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试', 'count' => 100];
        $events[7] = ['id' => 8, 'name' => '测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试', 'count' => 100];
        $events[8] = ['id' => 9, 'name' => '测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试', 'count' => 100];
        $events[9] = ['id' => 10, 'name' => '测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试测试测试测试测饿测试测试', 'count' => 100];
        $ret['event'] = $events;
        $this->jsonReturn($ret);

        //        $nature = [];
//        $nature[0] = ['name' => '正面', 'value' => 1200];
//        $nature[1] = ['name' => '中立', 'value' => 120];
//        $nature[2] = ['name' => '负面', 'value' => 120];
    }

    /**
     * 获取舆情内容
     */
    public function getPublicList(){
        $params = input('post.');
        $task_id = input('post.task_id', -1);
        $nature = input('post.nature', 0);
        $ret = ['errorcode' => 0,'data' => [], 'msg' => ''];
        $cond = [];
        if($task_id == -1){
            $task_id = 3; //这里为测试，实际上要获取task表中最后一条有效数据的id
        }
        $ret['task_id'] = $task_id;
        if($nature == 1){
            $cond['nature'] = ['=', '负面'];
        }

        // 查找逻辑， 未实现

        // 测试数据
        $public = [];
        $public[0] = ['id'=>1, 'title'=>'测试测试测试测试测试测试测试测试测试测试测试测试测试测试','nature'=>'正面', 'media_type'=>'微博', 'publishtime' => 289989228];
        $public[1] = ['id'=>2, 'title'=>'测试测试测试测试测试测试测试测试测试测试测试测试测试测试','nature'=>'正面', 'media_type'=>'微博', 'publishtime' => 289989228];
        $public[2] = ['id'=>3, 'title'=>'测试测试测试测试测试测试测试','nature'=>'正面', 'media_type'=>'微博', 'publishtime' => 289989228];
        $public[3] = ['id'=>4, 'title'=>'测试测试测试测试测试测试测试','nature'=>'正面', 'media_type'=>'微博', 'publishtime' => 289989228];
        $public[4] = ['id'=>5, 'title'=>'测试测试测试测试测试测试测试','nature'=>'正面', 'media_type'=>'微博', 'publishtime' => 289989228];
        $public[5] = ['id'=>6, 'title'=>'测试测试测试测试测试测试测试','nature'=>'正面', 'media_type'=>'微博', 'publishtime' => 289989228];
        $public[6] = ['id'=>7, 'title'=>'测试测试测试测试测试测试测试','nature'=>'正面', 'media_type'=>'微博', 'publishtime' => 289989228];
        $public[7] = ['id'=>8, 'title'=>'测试测试测试测试测试测试测试','nature'=>'正面', 'media_type'=>'微博', 'publishtime' => 289989228];
        $public[8] = ['id'=>9, 'title'=>'测试测试测试测试测试测试测试','nature'=>'正面', 'media_type'=>'微博', 'publishtime' => 289989228];
        $public[9] = ['id'=>10, 'title'=>'测试测试测试测试测试测试测试','nature'=>'正面', 'media_type'=>'微博', 'publishtime' => 289989228];

        $ret['data'] = $public;
        $this->jsonReturn($ret);
    }

    /**
     * 获取舆情趋势图
     */
    public function getTrendLine(){
        $params = input('post.');
        $obj = input('post.obj', '');
        $task_id = input('post.task_id', -1);
        $stime = input('post.begintime_str', '');
        $etime = input('post.endtime_str', '');

        $ret = ['errorcode' => 0, 'msg' => ''];
        $cond = [];
        if($task_id == -1){
            $task_id = 3; //这里为测试，实际上要获取task表中最后一条有效数据的id
        }
        $ret['task_id'] = $task_id;
        /**
         * 1. $stime == $etime: 最近24小时(比如现在是12点)
         *    xAixs = [12:00, 13:00, 14:00, 15:00, ···, 00:00, 01:00, 02:00, ···,  09:00, 10:00, 11:00, 12:00];
         * 2.
         */
        /**
         * 参考代码
         *     // 如果开始时间和结束时间相同，则为一天（今天、昨天或自定义某一天）
        if ($days == 0) {
        for ($i = 0; $i <= 23; $i++) {
        $hour = $i;
        if ($i <= 9)
        $hour = '0' . $i;
        $datetime_hour = $beginTime . ' ' . $hour;

        // get time
        $sqlTime = "select sum(time_len) as time from statistic_view where mer_id= {$mer_id} and consume_t like '{$datetime_hour}%'";
        $time = $adminDB->ExecSQL($sqlTime, $conn);
        array_push($return['time'], $time[0]['time'] == null ? 0 : $time[0]['time']);

        // get money
        $sqlMoney = "select sum(cost) as money from statistic_view where mer_id = {$mer_id}  and consume_t like '{$datetime_hour}%'";
        $money = $adminDB->ExecSQL($sqlMoney, $conn);
        array_push($return['money'], $money[0]['money'] == null ? 0 : $money[0]['money']);

        // set xAxis
        array_push($return['xAxis'], ($i <= 9 ? ('0' . $i) : $i) . ':00');
        }

        } else if ($days <= 31) {
        for ($i = 0; $i <= $days; $i++) {
        $datetime = date('Y-m-d', strtotime($beginTime . '+' . $i . 'days'));

        // get time
        $sqlTime = "select sum(time_len) as time from statistic_view where mer_id = {$mer_id}  and consume_t like '{$datetime}%'";
        $time = $adminDB->ExecSQL($sqlTime, $conn);
        array_push($return['time'], $time[0]['time'] == null ? 0 : $time[0]['time']);

        // get money
        $sqlMoney = "select sum(cost) as money from statistic_view where mer_id = {$mer_id}  and consume_t like '{$datetime}%'";
        $money = $adminDB->ExecSQL($sqlMoney, $conn);
        array_push($return['money'], $money[0]['money'] == null ? 0 : $money[0]['money']);

        // set xAxis
        array_push($return['xAxis'], $datetime);
        }
        } else {
        $datetimeBegin = date('m', strtotime($beginTime));
        $datetimeEnd = date('m', strtotime($endTime));
        $months = $datetimeEnd - $datetimeBegin;
        $year = date('Y', strtotime($beginTime));

        for ($i = 0; $i <= $months; $i++) {
        $datetime = $year . '-' . (($datetimeBegin + $i) <= 9 ? '0' . ($datetimeBegin + $i) : $datetimeBegin + $i);
        // get time
        $sqlTime = "select sum(time_len) as time from statistic_view where mer_id = {$mer_id}  and consume_t like '{$datetime}%'";
        $time = $adminDB->ExecSQL($sqlTime, $conn);
        array_push($return['time'], $time[0]['time'] == null ? 0 : $time[0]['time']);

        // get money
        $sqlMoney = "select sum(cost) as money from statistic_view where mer_id='{$mer_id}' and consume_t like '{$datetime}%'";
        $money = $adminDB->ExecSQL($sqlMoney, $conn);
        array_push($return['money'], $money[0]['money'] == null ? 0 : $money[0]['money']);

        array_push($return['xAxis'], $datetime);
        }
        }
         */

        $ret['xAixs'] = ['周一','周二','周三','周四','周五','周六','周日'];
        // 查找逻辑， 未实现

        $trend = [];
        switch ($obj){
            case 'media':{  // media trend
                $trend[0] = ['media_type'=>'微博', 'data' => [120, 132, 101, 134, 90, 230, 210]];
                $trend[1] = ['media_type'=>'微信', 'data' => [120, 132, 101, 134, 90, 230, 210]];
                $trend[2] = ['media_type'=>'新闻', 'data' => [120, 132, 101, 134, 90, 230, 210]];
                $trend[3] = ['media_type'=>'论坛', 'data' => [120, 132, 101, 134, 90, 230, 210]];
                break;
            }
            case 'public':{     // public trend
                $trend[0] = ['type'=>'热点舆情', 'data' => [120, 132, 101, 134, 90, 230, 210]];
                $trend[1] = ['type'=>'健康度', 'data' => [50, 30, 12, 13, 12, 30, 90]];
                break;
            }
        }
        $ret['data'] = $trend;
        $this->jsonReturn($ret);
    }

    /**
     * 获取媒体分布饼形图
     */
    public function getMediaDistrubution(){
        $params = input('post.');
        $task_id = input('post.task_id', -1);

        $ret = ['errorcode' => 0, 'msg' => ''];

        if($task_id == -1){
            $task_id = 3; //这里为测试，实际上要获取task表中最后一条有效数据的id
        }
        $ret['task_id'] = $task_id;

        // 查找逻辑， 未实现

        $trend = [];
        $trend[0] = ['media_type'=>'微博', 'data' => [120, 132, 101, 134, 90, 230, 210]];
        $trend[1] = ['media_type'=>'微信', 'data' => [120, 132, 101, 134, 90, 230, 210]];
        $trend[2] = ['media_type'=>'新闻', 'data' => [120, 132, 101, 134, 90, 230, 210]];
        $trend[3] = ['media_type'=>'论坛', 'data' => [120, 132, 101, 134, 90, 230, 210]];
        $ret['data'] = $trend;
        $this->jsonReturn($ret);
    }
}
