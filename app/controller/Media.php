<?php
/**
 * 网站库--控制器
 * author：yzs
 * create：2017.8.15
 */
namespace app\controller;

class Media extends Common
{
    public $exportCols = ['id','name','type_id', 'type_name','url'];
    public $colsText = ['序号', '媒体名称', '媒体类型id','媒体类型名字','网址'];
    /**
     * 网站列表
     * @return \think\response\View
     */
    public function index(){
        $params = input('get.');
        $keywords = input('get.keywords', '');
        $type_id = input('get.type_id', -1);
        $order = input('get.sortCol', '');
        if(!$order){
            $params['sortCol'] = 'a.id asc';
        }
        $cond_or = [];
        $cond_and = [];
        if($type_id != -1){
            $cond_and['a.id'] = $type_id;
        }
        if($keywords){
            $cond_or['a.name'] = ['like', '%' . $keywords . '%'];
            $cond_or['a.url'] = ['like', '%' . $keywords . '%'];
            $cond_or['b.name'] = ['like', '%' . $keywords . '%'];
        }
        $type_list = D('MediaType')->getMedTypeList();
        $list = D('Media')->getMedList($cond_or, $cond_and, $order);
        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '库管理/媒体库';
        $log['action_descr'] = '用户查看媒体库';
        D('OperationLog')->addData($log);
        return view('', ['list' => $list, 'typeList' => $type_list, 'type_id' => $type_id, 'cond' => $params]);
    }

    /**
     * 批量删除
     */
    public function remove()
    {
        $ret = ['code' => 1, 'msg' => '成功'];
        $ids = input('get.ids');
        try {
            D('Media')->remove(['id' => ['in', $ids]]);
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '库管理/媒体库';
            $log['action_descr'] = '用户删除媒体';
            D('OperationLog')->addData($log);
        } catch (MyException $e) {
            $ret['code'] = 2;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 增加媒体
     * @return string|\think\response\View
     */
    public function create_url(){
        $data = input('post.');
        $typeList = D('MediaType')->getMedTypeList();
        if (!empty($data)) {
            $res = D('Media')->addData($data);
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '库管理/媒体库';
            $log['action_descr'] = '用户新增媒体';
            D('OperationLog')->addData($log);
            if (!empty($res['errors']))
                return view('', ['errors' => $res['errors'], 'data' => $data,'typeList'=>$typeList]);
            else {
                $url = PRO_PATH . '/Media/index';
                return "<script>window.location.href='" . $url . "'</script>";
            }
        }else{
            return view('',['typeList'=>$typeList]);
        }
    }

    /**
     * 增加媒体类型
     * @return string|\think\response\View
     */
    public function create_type(){
        $data = input('post.');
        if (!empty($data)) {
            $res = D('MediaType')->addData($data);
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '库管理/媒体库';
            $log['action_descr'] = '用户新增媒体类型';
            D('OperationLog')->addData($log);
            if (!empty($res['errors']))
                return view('', ['errors' => $res['errors'], 'data' => $data]);
            else {
                $url = PRO_PATH . '/Media/index';
                return "<script>window.location.href='" . $url . "'</script>";
            }
        }else{
            return view('', ['errors' => [], 'data' => $data]);
        }
    }
    /**
     * 编辑网站
     */
    public function edit(){
        $id = input('get.id');
        $data = input('post.');
        $form = D('Media')->getById($id);
        $typeList = D('MediaType')->getMedTypeList();
        if (!empty($data)) {
            $res = D('Media')->saveData($id, $data);
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '库管理/媒体库';
            $log['action_descr'] = '用户编辑媒体';
            D('OperationLog')->addData($log);
            if (!empty($res['errors']))
                return view('', ['errors' => $res['errors'], 'data' => $data,'typeList'=>$typeList]);
            else {
                $url = PRO_PATH . '/Media/index';
                return "<script>window.location.href='" . $url . "'</script>";
            }
        } else {
            return view('', ['errors' => [], 'data' => $form,'typeList'=>$typeList]);
        }
    }

    /**
     * 网站类型饼形图
     */
    public function typePie(){
        $data = input('get.');
        if(isset($data['theme'])){
            $list = D('DataMonitor')->getThemePie($data);
        }else{
            $list = D('Media')->getTypePie($data);
        }
        $ret = ['errorcode' => 0, 'data' => [], 'msg' => ''];
        $ret['data'] = $list;
        $this->jsonReturn($ret);
    }

    /**
     * 网站导出
     */
    public function export(){
        $cond_or = [];
        $cond_and = [];
        $order = [];
        $list = D('Media')->getMedList($cond_or,$cond_and,$order,-1);
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
        $log['section'] = '库管理/媒体库';
        $log['action_descr'] = '用户导出媒体数据';
        D('OperationLog')->addData($log);
        D('Excel')->export($data, 'Media.xls');
    }
    /**
     * 主题导入
     */
    public function import(){
        $params = input('post.');
        //$file = input('post.file', '');
        $ret[0] = ['code' => 1, 'msg' => '导入成功'];
        $res = D('Excel')->import($params);
        if(!empty($res['errors'])){
            $ret[0]['errors'] = $res['errors'];
            $ret[0]['code'] = 2;
            $ret[0]['msg'] = '导入失败';
        }else{
            $data = $res['data'];
            array_combine($this->colsText, $this->exportCols);
            $count = 0;
            $i=0;
            foreach ($data as $item){
                $count++;
                $i++;
                $res = D('Media')->import_theme($item);
                if (!empty($res['errors'])){
                    $ret[$i]['errors'] = $res['errors'];
                    $ret[$i]['code'] = 3;
                    $ret[$i]['msg'] = '导入失败';
                    $count--;
                }
            }
            $ret['count'] = $count;
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '库管理/媒体库';
            $log['action_descr'] = '用户导入媒体数据';
            D('OperationLog')->addData($log);
        }
        $this->jsonReturn($ret);
    }
}

?>
