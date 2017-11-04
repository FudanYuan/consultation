<?php
/**
 * 舆情--控制器
 * Created by PhpStorm.
 * User: acer-pc
 * Date: 2017/10/5
 * Time: 0:45
 */
namespace app\controller;

class DataMonitor extends Common{
    public $exportCols = ['id','theme','task_id','title','content','digest',
        'source','userID','media_type_id','nature','url','relevance','publishtime','similar_num','is_collect','is_warn','status','createtime', 'updatetime'];
    public $colsText = ['序号', '主题','任务编号','标题','内容','概述','来源','用户ID','媒体类型id','舆情属性','网址','关联度','发表时间','相似文章数','是否收藏','是否预警'];

    /**
     * 数据总览
     * @return \think\response\View
     */
    public function index(){
        $task = D('Task')->getTaskList([],[],'createtime desc');
        $media_type = D('MediaType')->getMedTypeList();
        return view('', ['task'=>$task, 'area' => [], 'media_type' => $media_type]);
    }

    /**
     * 舆情预警
     * @return \think\response\View
     */
    public function collect(){
        return view('', []);
    }

    /**
     * 舆情预警
     * @return \think\response\View
     */
    public function warn(){
        return view('', []);
    }

    /**
     * 舆情设置
     * @return \think\response\View
     */
    public function warn_config(){
        return view('', []);
    }

    /**
     * 获取全部舆情
     */
    public function getPublicList(){
        $params = input('post.');
        $relevance = input('post.relevance', -1);
        $nature = input('post.nature',-1);
        $area = input('post.area',-1);
        $media_type = input('post.media_type',-1);
        $keywords = input('post.keywords', '');
        $stime = input('post.begintime_str', '');
        $etime = input('post.endtime_str', '');
        $order = input('post.sortCol', 'publishtime');
        $is_collect = input('post.is_collect',-1);
        $is_warn = input('post.is_warn',-1);
        $page = input('post.current_page',0);
        $per_page = input('post.per_page',0);
        $task_id = input('post.task',-1);
        $cond_and = [];
        $cond_or = [];
        if($keywords){
            $cond_or['b.name|a.content|a.source|a.nature|a.url|a.digest|a.userID|a.title'] = ['like','%'.$keywords.'%'];
        }
        if($stime && $etime){
            $cond_and['publishtime'] = ['between', [strtotime($stime), strtotime($etime)]];
        }
        else if(!$stime && $etime){
            $cond_and['publishtime'] = ['between', [0, strtotime($etime)]];
        }
        else if($stime && !$etime){
            $cond_and['publishtime'] = ['between', [strtotime($stime), time()]];
        }
        if($is_collect != -1){
            $cond_and['is_collect'] = ['=',1];
        }
        if($is_warn != -1){
            $cond_and['is_warn'] = ['=',1];
        }
        if($nature != -1){
            if($nature == 0){
                $nature_select = '正面';
            }else if($nature == 1){
                $nature_select = '中立';
            }else{
                $nature_select = '负面';
            }
            $cond_and['nature'] = ['=', $nature_select];
        }
        if($relevance != -1){
            $order = ['relevance desc'];
        }
        if($area!=-1){
            $cond_and['area'] = ['=',$area];
        }
        if($media_type != -1){
            $cond_and['b.id'] = ['=',$media_type];
        }
        if($task_id != -1){
            $cond_and['a.task_id'] = ['=',$task_id];
        }
        $ret = ['errorcode' => 0, 'data' => [], 'params' => $params, 'msg' => ""];
        $list = D('DataMonitor')->publicList($cond_or,$cond_and,$order);
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;
        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '实时舆情/全部舆情';
        $log['action_descr'] = '用户查看舆情';
        D('OperationLog')->addData($log);
        $this->jsonReturn($ret);
    }

    /**
     * 取消／收藏舆情
     */
    public function doCollect(){
        $params = input('post.');
        $id = input('post.id', -1);
        $isCollected = input('post.is_collect');
        $ret = ['errorcode' => 0, 'msg' => '','id' => $id,'isCollected' => $isCollected];
        // 收藏逻辑
        if($id != '-1'){
            $data = D('DataMonitor')->getDataById($id);
            if(!empty($data)) {
                if ($isCollected == 1) {
                    $data['is_collect'] = 0;
                    $log['user_id'] = $this->getUserId();
                    $log['IP'] = $this->getUserIp();
                    $log['section'] = '实时舆情/全部舆情';
                    $log['action_descr'] = '用户取消收藏';
                    D('OperationLog')->addData($log);
                } else {
                    $data['is_collect'] = 1;
                    $log['user_id'] = $this->getUserId();
                    $log['IP'] = $this->getUserIp();
                    $log['section'] = '实时舆情/全部舆情';
                    $log['action_descr'] = '用户收藏舆情';
                    D('OperationLog')->addData($log);
                }
                $ret['data'] = $data;
                D('DataMonitor')->saveData($data, $id);
            }
        }
        $this->jsonReturn($ret);
    }

    /**
     * 编辑舆情，人工研判
     */
    public function edit(){
        $params = input('post.');
        $id = input('post.id', -1);
        $nature = input('post.nature', '');
        $relevance = input('post.relevance', '');
        $ret = ['errorcode' => 0, 'msg' => '修改成功','nature' =>$nature,'relevance'=>$relevance];
        // 编辑逻辑
        if($id != '-1'){
            // 修改成功，msg为 '编辑成功'，否则 '编辑失败'
            $data = D('DataMonitor')->getDataById($id);
            if($nature){
                $data['nature'] = $nature;
            }
            if($relevance){
                $data['relevance'] = $relevance;
            }
            D('DataMonitor')->saveData($data,$id);
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '实时舆情/全部舆情';
            $log['action_descr'] = '用户编辑舆情';
            D('OperationLog')->addData($log);
        }
        $this->jsonReturn($ret);
    }

    /**
     * 删除舆情
     */
    public function remove(){
        $ret = ['code' => 1, 'msg' => '成功'];
        $ids = input('get.ids');
        try{
            // 重写/model/DataMonitor的remove函数即可
            $res = D('DataMonitor')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['code'] = 2;
            $ret['msg'] = '删除失败';
        }
        if($ret['code'] == 1){
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '实时舆情/全部舆情';
            $log['action_descr'] = '用户删除舆情';
            D('OperationLog')->addData($log);
        }
        $this->jsonReturn($ret);
    }


    /**
     * 获取预警关键词列表
     */
    public function getKeywordsConfig(){
        $ret = ['errorcode' => 0, 'msg' => ''];
        // 查询结果，
        // 逻辑： 先判断关键词预警是否开启，若开启，获取关键词列表，否则返回数据为空
        $data = D('KeywordWarn')->getKeywordList();
        $total = count($data);
        if($total) {
            $keyword = $data[$total - 1]['keyword'];
            $nature = $data[$total - 1]['nature'];
            $media_type = $data[$total - 1]['media_type'];
            $status = $data[$total - 1]['status'];
        } else {
            $keyword = '';
            $nature = '';
            $media_type = '';
            $status = 1;
        }
        $list = explode('-', $keyword);
        //去除空字段
        $i =0;
        foreach ($list as $v){
            if(empty($v)){
                unset($list[$i]);
            }
            $i++;
        }
        $nature_warn = [];
        if(strpos($nature,'正面'))
            $nature_warn['正面'] = 1;
        else
            $nature_warn['正面'] = 0;
        if(strpos($nature,'中立'))
            $nature_warn['中立'] = 1;
        else
            $nature_warn['中立'] = 0;
        if(strpos($nature,'负面'))
            $nature_warn['负面'] = 1;
        else
            $nature_warn['负面'] = 0;
        $media_warn = [];
        if(strpos($media_type,'微信'))
            $media_warn['微信'] = 1;
        else
            $media_warn['微信'] = 0;
        if(strpos($media_type,'新闻'))
            $media_warn['新闻'] = 1;
        else
            $media_warn['新闻'] = 0;
        if(strpos($media_type,'微博'))
            $media_warn['微博'] = 1;
        else
            $media_warn['微博'] = 0;
        if($status == 1){
            $switch_warn = 1;
        }else{
            $switch_warn = 0;
        }
        $ret['switch'] = $switch_warn;
        $ret['nature'] = $nature_warn;
        $ret['media'] = $media_warn;
        $ret['keywords'] = $list;

        $this->jsonReturn($ret);
        //$list = ['测试1', '测试2', '测试3', '测试4', '测试5', '测试6'];
        /**
         * nature: "{'正面':0,'中立':0, '负面':0}"
         * media: "{'类型1':0, '类型2':0}"
         */
        //$ret['nature'] = ['正面' => 0, '中立' => 1, '负面' => 1];
        //$ret['media'] = ['微信' => 1, '新闻' => 0, '微博' => 1];
    }

    /**
     * 保存关键词配置
     */
    public function setKeywordsConfig(){

        /**
         * 参数说明：
         * keywordsSwitch boolean
         * keywords array      |
         * # nature array      | ==> 此三项的元素均为字符串
         * # media array       |
         * 加 '#' 的三项要注意，需要将其构造为下面这种格式：
         * nature： ['正面' => 1, '中立' => 1, '负面' => 1];
         * media：['微信' => 1, '新闻' => 0, '微博' => 1];
         * 对于nature来说比较简单，但对于media，就要读取media数据表读取所有的媒体类型，
         * 然后根据传入的数据构造如上数据。
         * 注：要对参数进行检测，返回error信息，
         * keywords 不能为空，
         * nature 至少选择一项
         * media 至少选择一项
         * $ret['error'], 例如$ret['error'] = ['keywords' => '关键词不能为空']
         */
        $params = input('post.');
        $keywordsSwitch = $params['keywordsSwitch'];
        if(!isset($params['keywords'])){
            $keywords = [];
        } else {
            $keywords = $params['keywords'];
        }
        if(!isset($params['nature'])){
            $nature = [];
        } else {
            $nature = $params['nature'];
        }
        if(!isset($params['media'])){
            $media = [];
        } else {
            $media = $params['media'];
        }
        $ret = ['errorcode' => 0, 'msg' => '','params' => $params];
        // 更新预警设置逻辑
        // code here
        if($keywordsSwitch == 'true') {
            if(!empty($nature)&&!empty($keywords&&!empty($media))) {
                $data = [];
                $keyword_warn = '';
                foreach ($keywords as $keyword) {
                    $keyword_warn = $keyword_warn . $keyword . '-';
                }
                $data['keyword'] = $keyword_warn;
                $nature_warn = '-';
                foreach ($nature as $n) {
                    $nature_warn = $nature_warn . $n . '-';
                }
                $data['nature'] = $nature_warn;
                $media_warn = '-';
                foreach ($media as $m) {
                    $media_warn = $media_warn . $m . '-';
                }
                $data['media_type'] = $media_warn;
                $res = D('KeywordWarn')->addData($data);
                if (!empty($res['errors'])) {
                    $ret = ['errorcode' => 1, 'msg' => $res['errors']];
                }
                $log['user_id'] = $this->getUserId();
                $log['IP'] = $this->getUserIp();
                $log['section'] = '舆情预警/预警设置';
                $log['action_descr'] = '用户添加预警词';
                D('OperationLog')->addData($log);
            }else{
                $list = D('KeywordWarn')->getKeywordList();
                $total = count($list);
                if($total){
                    $data = $list[$total - 1];
                    $data['status'] = 1;
                    $ret['data'] = $data;
                    $res = D('KeywordWarn')->saveData($data['id'],$data);
                    if (!empty($res['errors'])) {
                        $ret = ['errorcode' => 1, 'msg' => '关闭失败'];
                    } else{
                        $ret = ['errorcode' => 0, 'msg' => '关闭成功'];
                    }
                    $log['user_id'] = $this->getUserId();
                    $log['IP'] = $this->getUserIp();
                    $log['section'] = '舆情预警/预警设置';
                    $log['action_descr'] = '用户打开预警';
                    D('OperationLog')->addData($log);
                }
            }
        }
        if($keywordsSwitch == 'false'){
            $list = D('KeywordWarn')->getKeywordList();
            $total = count($list);
            if($total){
                $data = $list[$total - 1];
                $data['status'] = 2;
                $ret['data'] = $data;
                $res = D('KeywordWarn')->saveData($data['id'],$data);
                if (!empty($res['errors'])) {
                    $ret = ['errorcode' => 1, 'msg' => '关闭失败'];
                } else{
                    $ret = ['errorcode' => 0, 'msg' => '关闭成功'];
                }
                $log['user_id'] = $this->getUserId();
                $log['IP'] = $this->getUserIp();
                $log['section'] = '舆情预警/预警设置';
                $log['action_descr'] = '用户关闭预警';
                D('OperationLog')->addData($log);
            }

        }
        $this->jsonReturn($ret);
    }


    /**
     * 获取警戒线配置
     */
    public function getThresholdConfig(){
        $page = input('post.current_page', 0);
        $per_page = input('post.per_page', 0);
        /**
         * status: 1 预警中； 2 关闭； 3 删除
         */
        $ret = ['errorcode' => 0, 'msg' => ''];
        // 查询结果
        $list = D('ThresholdWarn')->getWarnList([],[],[]);
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret['current_page'] = $page;
        $ret['list'] = array_slice($list, ($page-1)*$per_page, $per_page);
        // 任务列表
        $task_list = D('Task')->getTaskList([],[],[]);
        $tasks = [];
        foreach ($task_list as $task){
            array_push($tasks,$task['name']);
        }
        $ret['tasks'] = $tasks;
        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '舆情预警/预警设置';
        $log['action_descr'] = '用户查看警戒线列表';
        D('OperationLog')->addData($log);
        $this->jsonReturn($ret);
//        $ret['tasks'] = ['测试1', '生态环境', '测试3', '测试4', '测试5', '测试6'];
//        $list = [];
//        $list[0] = ['id' => 1, 'task' => '生态环境', 'dayAllCount' => 10, 'dayNegativeCount' => 10, 'status' => 1];
//        $list[1] = ['id' => 2, 'task' => '生态环境', 'dayAllCount' => 10, 'dayNegativeCount' => 10, 'status' => 1];
//        $list[2] = ['id' => 3, 'task' => '生态环境', 'dayAllCount' => 10, 'dayNegativeCount' => 10, 'status' => 2];
    }

    /**
     * 删除警戒线预警
     */
    public function removeThresholdConfig(){
        $ret = ['code' => 1, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            D('ThresholdWarn')->remove($ids);
        }catch(MyException $e){
            $ret['code'] = 2;
            $ret['msg'] = '删除失败';
        }
        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '舆情预警/预警设置';
        $log['action_descr'] = '用户删除警戒线';
        D('OperationLog')->addData($log);
        $this->jsonReturn($ret);
    }


    /**
     * 保存关键词配置
     */
    public function createThresholdConfig(){
        /**
         * 参数默认：
         * task ''
         * dayAllCount -1
         * dayNegativeCount -1
         * 参数过滤：
         * task '' 请选择类目名称
         * dayAllCount -1 请设置每日舆情总量
         * dayNegativeCount -1 请设置每日负面舆情
         */
        $params = input('post.');
        $task = input('post.task', '');
        $dayAllCount = input('post.dayAllCount',-1);
        $dayNegativeCount = input('post.dayNegativeCount',-1);
        $ret = ['errorcode' => 0, 'msg' => '','params'=>$params];
        // 添加预警设置逻辑
        // code here
        $data['day_all_count'] = $dayAllCount;
        $data['day_negative_count'] = $dayNegativeCount;
        $task_id = D('Task')->getTaskIdByName($task);
        if($task_id){
            $data['task_id'] = $task_id['id'];
        }else{
            $data['task_id'] = '';
        }
        $res = D('ThresholdWarn')->addData($data);
        if(!empty($res['errors'])){
            $ret = ['errorcode' => 2, 'msg' => $res['errors']];
        }
        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '舆情预警/预警设置';
        $log['action_descr'] = '用户添加警戒线';
        D('OperationLog')->addData($log);
        $this->jsonReturn($ret);
    }

    /**
     * 编辑警戒线预警配置
     */
    public function saveThresholdConfig(){
        /**
         * 参数默认：
         * dayAllCount -1
         * dayNegativeCount -1
         * 参数过滤：
         * dayAllCount -1 请设置每日舆情总量
         * dayNegativeCount -1 请设置每日负面舆情
         */
        $params = input('post.');
        $id = input('post.id');
        $status = input('post.status','');
        $task = input('post.task', '');
        $dayAllCount = input('post.dayAllCount', -1);
        $dayNegativeCount = input('post.dayNegativeCount', -1);
        $ret = ['errorcode' => 0, 'msg' => ''];
        // 编辑预警设置逻辑
        // code here
        if($status){
            $data['status'] = $status;
        }
        $task_id = '';
        if($task){
            $task_id  = D('Task')->getTaskIdByName($task);
            $data['task_id'] = $task_id['id'];
        }
        if($dayAllCount != -1){
            $data['day_all_count'] = $dayAllCount;
        }
        if($dayNegativeCount != -1){
            $data['day_negative_count'] = $dayNegativeCount;
        }
        $res = D('ThresholdWarn')->saveData($id,$data);
        if(!empty($res['errors'])){
            $ret = ['errorcode' => 2, 'msg' => $res['errors']];
        }
        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '舆情预警/预警设置';
        $log['action_descr'] = '用户编辑警戒线';
        D('OperationLog')->addData($log);
        $this->jsonReturn($ret);
    }

    /**
     * 数据导出
     */
    public function export(){
        $list = D('DataMonitor')->getListExport();
        $data = [];
        // 匹配键值
        array_push($data, $this->exportCols);
        foreach ($list as $value) {
            $temp = [];
            foreach ($this->exportCols as $key => $k){
                array_push($temp, $value[$k]);
            }
            array_push($data, $temp);
        }
        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '实时预警/全部舆情';
        $log['action_descr'] = '用户导出数据';
        D('OperationLog')->addData($log);
        D('Excel')->export($data, 'dataMonitor.xls');
    }

    ///////////// 未修改 ///////////
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
