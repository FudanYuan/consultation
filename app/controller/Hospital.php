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
            $cond['name'] = ['like', '%' . myTrim($name) . '%'];
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
        $ret = ['error_code' => 0, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            $res = D('Hospital')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['error_code'] = 1;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 新建
     */
    public function create(){
        $params = input('post.');
        if(!empty($params)) {
            $ret = ['error_code' => 0, 'msg' => '新建成功'];
            $Role = input('post.hospital_role');
            if($Role == '不可会诊医院'){
                $params['role'] = 2;
            }elseif ($Role == '可会诊医院'){
                $params['role'] = 1;
            }
            $res = D('Hospital')->addData($params);
            if(!empty($res['errors'])) {
                $ret['error_code'] = 1;
                $ret['msg'] = '新建失败';
                $ret['errors'] = $res['errors'];
            }
            $this->jsonReturn($ret);
        }
        return view('', []);
    }

    /**
     * 编辑医院信息
     * @return \think\response\View
     */
    public function edit(){
        $id = input('get.id');
        $params = input('post.');
        $hospital = D('Hospital')->getById($id);
        if(!empty($params)){
            $ret = ['error_code' => 0, 'msg' =>'编辑成功'];
            $Role = input('post.hospital_role');
            if($Role == '不可会诊医院'){
                $params['role'] = 2;
            }elseif ($Role == '可会诊医院'){
                $params['role'] = 1;
            }
            $hospital_id = $params['hospital_id'];
            unset($params['hospital_id']);
            $res = D('Hospital')->saveData($hospital_id,$params);
            if(!empty($res['errors'])) {
                $ret['error_code'] = 1;
                $ret['msg'] = '编辑失败';
                $ret['errors'] = $res['errors'];
            }
            $this->jsonReturn($ret);
        }else{
            return view('',['Hospital' => $hospital]);
        }
    }

    /**
     *获取医院信息
     */
    public function getHospitalInfo(){
        $id = input('post.id');
        $select = ['*'];
        $cond['id'] = ['=',$id];
        $res = D('Hospital')->getHospital($select,$cond);
        $ret = [
            'error_code' => 0,
            'msg' => '',
            'info' => $res[0]
        ];

        $this->jsonReturn($ret);
    }

    /**
     * 获取医院信息
     */
    function info(){
        $id = input('get.id');
         return view('', ['id' => $id]);
    }

    /**
     * 获取医院对应科室
     */
    public function getOfficeByHospitalId(){
        $id = input('post.id');
        $select = ['a.id,a.name'];
        $cond = ['b.hospital_id' => $id];
        $ret = D('Office')->getOfficeByHospital($select,$cond);
        $this->jsonReturn($ret);
    }

}