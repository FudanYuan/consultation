<?php
/**
 * 主题--控制器
 * author：yzs
 * create：2017.8.15
 */
namespace app\controller;


class Theme extends Common{
    public $exportCols = ['t3_id','t3_name','t2_id','t2_name','t1_id','t1_name'];
    public $colsText = ['三级主题id', '三级主题', '二级主题id','二级主题','一级主题id','一级主题'];
    /**
     * 主题列表
     */
    public function index(){
        $params = input('get.');
        $keywords = input('get.keywords', '');
        $order = input('get.sortCol', '');
        if(!$order){
            $params['sortCol'] = 'a.id asc';
        }
        $cond_or = [];
        $cond_and = [];
        if ($keywords) {
            $cond_or['a.name'] = ['like', '%'.$keywords.'%'];
            $cond_or['b.name'] = ['like', '%'.$keywords.'%'];
            $cond_or['c.name'] = ['like', '%'.$keywords.'%'];
        }
        $list = D('Theme')->getT3List($cond_or,$cond_and,$order);
        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '库管理/主题库';
        $log['action_descr'] = '用户查看主题列表';
        D('OperationLog')->addData($log);
        return view('', ['list' => $list, 'cond' => $params]);
    }

    /**
     * 获取3级主题数量
     */
    public function getT3Number(){
        $t3number = D('Theme')->getT3Number();
        return view('',['t3_count' => $t3number]);
    }

    /**
     * 1级主题列表
     */
    public function T1List(){
        $cond_or = [];
        $cond_and = [];
        $order = [];
        $list = D('Theme')->getT1List($cond_or,$cond_and,$order);
        return $list;
    }
    /**
     * 1级主题数量
     */
    public function T1Number(){
        $t1number = D('Theme')->getT1Number();
        return $t1number;
    }
    /**
     * 2级主题列表
     */
    public function T2List(){
        $cond_or = [];
        $cond_and = [];
        $order = [];
        $list = D('Theme')->getT2List($cond_or,$cond_and,$order);
        return $list;
    }

    /**
     * 2级主题数量
     */
    public function T2Number(){
        $t2number = D('Theme')->getT2Number();
        return $t2number;
    }

    /**
     * 主题气泡图
     */
    public function  bubbleList(){
        $data = input('get.');
        $ret = ['errorcode' => 0, 'data' => [], 'msg' => ''];
        $list = D('Theme')->getBubbleList($data);
        $ret['data'] = $list;
        $this->jsonReturn($ret);
    }

    /**
     * 新建一级主题
     */
    public function create_t1(){
        $data = input('post.');
        if(!empty($data)){
            $res = D('Theme')->addTheme_1($data);
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '库管理/主题库';
            $log['action_descr'] = '用户新建一级主题';
            D('OperationLog')->addData($log);
            if(!empty($res['errors']))
                return view('', ['errors' => $res['errors'], 'data' => $data]);
            else{
                $url = PRO_PATH . '/Theme/index';
                return "<script>window.location.href='".$url."'</script>";
            }
        }else{
            return view('', []);
        }
    }

    /**
     * 新建二级主题
     */
    public function create_t2(){
        $data = input('post.');
        $list = $this->T1List();
        if (!empty($data)) {
            $res = D('Theme')->addTheme_2($data);
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '库管理/主题库';
            $log['action_descr'] = '用户新建二级主题';
            D('OperationLog')->addData($log);
            if (!empty($res['errors'])) {
                return view('', ['errors' => $res['errors'], 'data' => $data, 'list' => $list]);
            } else {
                $url = PRO_PATH . '/Theme/index';
                return "<script>window.location.href='" . $url . "'</script>";
            }
        } else{
            return view('', ['list' => $list]);
        }
    }

    /**
     * 新建三级主题
     */
    public function create_t3(){
        $data = input('post.');
        $cond_and = [];
        if(!isset($data['t1_id'])){
            $cond_and['a.t1_id'] = 1;
        } else{
            $cond_and['a.t1_id'] = $data['t1_id'];
        }
        $list1 = $this->T1List();
        $list2 = D('theme')->getT2List([],$cond_and,[]);
        if (!empty($data)) {
            $params = [];
            $params['t2_id'] = $data['t2_id'];
            $params['name'] = $data['name'];
            $res = D('Theme')->addTheme_3($params);
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '库管理/主题库';
            $log['action_descr'] = '用户新建三级主题';
            D('OperationLog')->addData($log);
            if (!empty($res['errors'])) {
                return view('', ['errors' => $res['errors'], 'data' => $data, 'list1' => $list1, 'list2' => $list2]);
            } else {
                $url = PRO_PATH . '/Theme/index';
                return "<script>window.location.href='" . $url . "'</script>";
            }
        }
        else{
            return view('', ['list1' => $list1, 'list2' => $list2]);
        }
    }

    /**
     * 异步获取2级主题
     */
    public function getT2ByT1AJAX(){
        $data = input('get.');
        $ret = ['errorcode' => 0, 'data' => [], 'msg' => ''];
        $ret['data'] = D('Theme')->getT2List([],$data,[]);
        $this->jsonReturn($ret);
    }

    /**
     * 编辑
     */
    public function edit(){
        $getData = input('get');
        $page = input('get.page');
        $id = input('get.id');
        $data = input('post.');
        $cond_and = [];
        $form = D('Theme')->getT3ById($id);
        $cond_and['a.t1_id'] = $form['t1_id'];
        $list2 = D('theme')->getT2List([],$cond_and,[]);
        $list1 = $this->T1List();
        if(!empty($data)){
            $params = [];
            $params['t1_id'] = $data['t1_id'];
            $params['t2_id'] = $data['t2_id'];
            $params['name'] = $data['t3_name'];
            $res = D('Theme')->saveTheme_3($id, $params);
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '库管理/主题库';
            $log['action_descr'] = '用户编辑主题';
            D('OperationLog')->addData($log);
            if(!empty($res['errors']))
                return view('', ['errors' => $res['errors'], 'cond' => $getData, 'data' => $data, 'list1' => $list1, 'list2' => $list2]);
            else{
                $url = PRO_PATH . '/Theme/index?page=' . $page;
                return "<script>window.location.href='".$url."'</script>";
            }
        }else{
            return view('', ['errors' => [], 'cond' => $getData, 'data' => $form, 'list1' => $list1, 'list2' => $list2]);
        }
    }

    /**
     * 批量删除
     */
    public function remove(){
        $ret = ['code' => 1, 'msg' => '成功'];
        $ids = input('get.ids');
        try{
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '库管理/主题库';
            $log['action_descr'] = '用户删除主题';
            D('OperationLog')->addData($log);
            $res = D('Theme')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['code'] = 2;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 主题导出
     */
    public function export(){
        $cond_or = [];
        $cond_and = [];
        $order = [];
        $list = D('Theme')->getT3List([],[],[], -1);
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
        $log['section'] = '库管理/主题库';
        $log['action_descr'] = '用户导出主题';
        D('OperationLog')->addData($log);
        D('Excel')->export($data, 'theme.xls');
    }

    /**
     * 主题导入
     */
    public function import(){
        $params = input('post.');
        $ret[0] = ['code' => 1, 'msg' => '导入成功'];
        $res = D('Excel')->import($params);
//        $ret['data'] = $res;
//        $this->jsonReturn($ret);
        if(!empty($res['errors'])){
            $ret[0]['errors'] = $res['errors'];
            $ret[0]['code'] = 2;
            $ret[0]['msg'] = '导入失败';
        }else{
            $keys = $res['keys'];
            $data = $res['data'];
            $colsDic = array_combine($this->colsText, $this->exportCols);
            $count = 0;
            $i=0;
            foreach ($data as $item){
                $count++;
                $i++;
                $res = D('Theme')->import_theme($item);
                if (!empty($res['errors'])){
                    $ret[$i]['errors'] = $res['errors'];
                    $ret[$i]['code'] = 3;
                    $ret[$i]['msg'] = '导入失败';
                    $count--;
                }
                $ret[$i]['list_t1'] = $res['data_t3'];
            }
            $ret['count'] = $count;

        }
        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '库管理/主题库';
        $log['action_descr'] = '用户导入主题';
        D('OperationLog')->addData($log);
        $this->jsonReturn($ret);
    }
}
?>