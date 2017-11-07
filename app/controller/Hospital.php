<?php
/**
 * 医院信息--控制器
 * Created by shiren.
 * time 2017.10.19
 */
namespace app\controller;

class Hospital extends Common
{
    public $exportCols = [];
    public $colsText = [];

    /**
     * 医院信息
     * @return \think\response\View
     */
    public function index()
    {
        return view('', []);
    }

    /**
     * 获取医院信息列表
     */
    public function getHospitalList(){
        $params = input('post.');
        $name = input('name', '');
        $cond = [];
        if($name != ''){
            $cond['name'] = ['like', '%' . $name . '%'];
        }

        // 地域筛选
        $prov = input('prov', '不限');
        $address = '';
        if($prov != '不限'){
            $address .= '%' . $prov . '%';
            if(isset($params['city'])){
                $address .= '%' . $params['city'] . '%';
                if(isset($params['county'])){
                    $address .= '%' . $params['county'] . '%';
                }
            }
        }
        if($address != ''){
            $cond['address'] = ['like', $address];
        }

        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        $list = D('Hospital')->getList($cond);
        $page = input('post.current_page',0);
        $per_page = input('post.per_page',0);
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;
        $ret["address"] = $cond;
        $this->jsonReturn($ret);
    }

    /**
     * 删除医院信息
     */
    public function remove(){
        $ret = ['code' => 1, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            $res = D('Hospital')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['code'] = 2;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 新建
     */
    public function create(){
        $params = input('post.');
        $cond = [];
        $cond['id'] = ['<>', $this->getUserId()];
        $target_users = D('UserAdmin')->getList($cond);
        if(!empty($params)) {
            $data = [];
            $ret = ['code' => 1, 'msg' => '新建成功'];
            $title = input('post.title', '');
            $priority = input('post.priority', '');
            if (!isset($params['target_user_ids'])) {
                $params['target_user_ids'] = [];
            }
            if (!isset($params['content'])){
                $params['content'] = '';
            }

            $data['source_user_id'] = $this->getUserId();
            $data['title'] = $title;
            $data['content'] = $params['content'];
            $data['operation'] = '查看';
            $data['priority'] = (int)$priority;
            $data['status'] = 0;

            $dataSet = [];
            if(!empty($params['target_user_ids'])){
                for($i=0;$i<count($params['target_user_ids']);$i++){
                    $data['target_user_id'] = (int)$params['target_user_ids'][$i];
                    array_push($dataSet, $data);
                }
                // 添加Apply
                $res_apply = D('Apply')->addAllData($dataSet);
                if (!empty($res_apply['errors'])) {
                    $ret['code'] = 2;
                    $ret['msg'] = '新建失败';
                    $ret['errors'] = $res_apply['errors'];
                    $this->jsonReturn($ret);
                }
                $log['user_id'] = $this->getUserId();
                $log['IP'] = $this->getUserIp();
                $log['section'] = '医院信息';
                $log['action_descr'] = '添加医院信息';
                D('OperationLog')->addData($log);
                $this->jsonReturn($ret);
            }
            else{
                $data['target_user_id'] = '';
                // 添加Apply
                $res_apply = D('Apply')->addData($data);
                if (!empty($res_apply['errors'])) {
                    $ret['code'] = 2;
                    $ret['msg'] = '新建失败';
                    $ret['errors'] = $res_apply['errors'];
                }
                $this->jsonReturn($ret);
            }

        }
        $office = [];
        $office[0] = ['id' => 1, 'name' => '骨科'];
        $office[1] = ['id' => 2, 'name' => '眼科'];
        return view('', ['office' => $office]);
    }


    /**
     * 获取医院信息
     */
    function info(){
        $id = input('get.id');
        return view('', ['id' => $id]);
    }

    /**
     * 获取医院详情
     */
    public function getHospitalInfo(){
        $id = input('post.id');
        $ret = ['error_code' => 0, 'msg' => ''];
        $list = D('Hospital')->getById($id);
        $ret['info'] = $list;
        $this->jsonReturn($ret);
    }
}