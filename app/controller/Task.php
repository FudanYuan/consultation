<?php
/**
 * 任务--控制器
 * author：yzs
 * create：2017.8.15
 */
namespace app\controller;

use app\model\MyException;

class Task extends Common{

    /**
     * 任务首页
     * @return \think\response\View
     */
    public function index(){
        return view('', []);
    }


    /**
     * 获取任务列表
     */
    public function getTaskList(){
        $params = input('post.');
        $ret = ['errorcode' => 0, 'msg' => '成功'];
        if(empty($params)){
            $list = D('Task')->getTaskList([],[],'createtime desc');
            $ret["data"] = $list;
            $this->jsonReturn($ret);
        }
        $task_name = input('post.name','');
        $taskStatus = input('post.taskstatus',-1);
        $order = input('post.sortCol', 'createtime desc');
        $page = input('post.current_page',0);
        $per_page = input('post.per_page',0);
        $cond_and = [];
        $cond_or = [];
        if($taskStatus!=-1){
            switch ($taskStatus){
                case '0':
                    $cond_and['taskstatus'] = ['=' , 0];
                    break;
                case '1':
                    $cond_and['taskstatus'] = ['=' , 1];
                    break;
                case '2':
                    $cond_and['taskstatus'] = ['=' , 2];
                    break;
            }
        }
        $list = D('Task')->getTaskList($cond_or,$cond_and,$order);
        for($i=0;$i<count($list);$i++){
            $curtime = time();
            $begintime = $list[$i]['begintime'];
            $pretime = $list[$i]['pretime'];
            $time = $curtime - $begintime;//计算已耗时间
            if($time < 0){
                $time = 0;
            }
            ///未修改////
            /// 采集进度条逻辑////
            if($curtime>($begintime+$pretime)){
                $progress = 100;
            }else if($curtime<$begintime){
                $progress = 0;
            }else{
                if($time>0){
                    $progress =($time/$pretime)*100;
                }else{
                    $progress = 100;
                }
            }
            $list[$i]['pretime'] = round($pretime/3600,1);
            $list[$i]['progress'] =round($progress,2);
            $list[$i]['time'] = round($time/3600, 1);
            $list[$i]['count'] = number_format($list[$i]['count']);
        }

        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;
        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '舆情采集';
        $log['action_descr'] = '用户查看采集列表';
        D('OperationLog')->addData($log);
        $this->jsonReturn($ret);
    }

    /**
     * 终止
     */
    public function stop(){
        $ret = ['code' => 1, 'msg' => '成功'];
        $ids = input('post.ids');
        try{
            $res = D('Task')->end_task(['id' => ['in', $ids]]);
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '舆情采集';
            $log['action_descr'] = '用户终止采集';
            D('OperationLog')->addData($log);
        }catch(MyException $e){
            $ret['code'] = 2;
            $ret['msg'] = '终止失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 新建
     */
    public function create(){
        $data = input('post.');
        $theme_list = D('Theme')->getT1List([],[],[]);
        for($i = 0; $i < count($theme_list); $i++){
            $cond['b.id'] = ['=',$theme_list[$i]['t1_id']];
            $theme_2_list = D('Theme')->getT2List([],$cond,[]);
            $theme_list[$i]['t1_content'] = $theme_2_list;
        }
        $website_list = D('MediaType')->getMedTypeList();
        if(!empty($data)) {
            $ret = ['code' => 1, 'msg' => '成功'];
            $ret['data'] = $data;
            if (!isset($data['theme'])) {
                $data['theme'] = [];
            }
            if (!isset($data['website'])) {
                $data['website'] = [];
            }
            $data['task_num'] = count($data['theme']);
            // 添加task
            $res_task = D('Task')->addData($data);
            $theme = $data['theme'];
            $website = $data['website'];
            if (!empty($res_task['errors'])) {
                $ret['code'] = 2;
                $ret['msg'] = '新建失败';
                $ret['errors'] = $res_task['errors'];
                $this->jsonReturn($ret);
            } else {
                $log['user_id'] = $this->getUserId();
                $log['IP'] = $this->getUserIp();
                $log['section'] = '舆情采集';
                $log['action_descr'] = '用户新建采集任务';
                D('OperationLog')->addData($log);
                $task_id = $res_task['task_id'];
                // 添加task_theme,
                /**
                 * 这里要修改一下逻辑，根据 匹配逻辑 选择添加的主题级别
                 */
                for ($i = 0; $i < count($theme); $i++) {
                    $theme_3_data = D('Theme')->getT3ByT2id($theme[$i]);
                    $task_theme_data = [];
                    for ($j = 0; $j < count($theme_3_data); $j++) {
                        $task_theme_data['task_id'] = $task_id;
                        $task_theme_data['theme_id'] = $theme_3_data[$j]['t3_id'];
                        D('TaskTheme')->addData($task_theme_data);
                    }
                }
                // 添加task_media_type
                $task_media_data = [];
                for ($i = 0; $i < count($website); $i++) {
                    $task_media_data['task_id'] = $task_id;
                    $task_media_data['media_type_id'] = $website[$i];
                    D('TaskMediaType')->addData($task_media_data);
                }
                $this->jsonReturn($ret);
            }
        }
        return view('', ['theme_list' => $theme_list, 'website_list' => $website_list]);
    }

    /**
     * 编辑
     */
    public function edit(){
        $id = input('get.id');
        $data = input('post.');
        $sections = D('Tag')->getSections();
        if(!empty($data)){
            $res = D('Tag')->saveData($id, $data);
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '舆情采集';
            $log['action_descr'] = '用户编辑采集';
            D('OperationLog')->addData($log);
            if(!empty($res['errors']))
                return view('', ['errors' => $res['errors'], 'data' => $data, 'sections' => $sections]);
            else{
                $url = PRO_PATH . '/Tag/index';
                return "<script>window.location.href='".$url."'</script>";
            }
        }else{
            $data = D('Tag')->getById($id);
            return view('', ['errors' => [], 'data' => $data, 'sections' => $sections]);
        }
    }
}
?>