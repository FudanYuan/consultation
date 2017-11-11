<?php
/**
 * 医生信息--控制器
 * Created by shiren.
 * time 2017.10.19
 */
namespace app\controller;

class Doctor extends Common
{
    public $exportCols = [];
    public $colsText = [];

    /**
     * 医生信息
     * @return \think\response\View
     */
    public function index()
    {
        return view('', []);
    }

    /**
     * 获取医生信息列表
     */
    public function getDoctorList(){
        $params = input('post.');
        // 获取当前登陆的用户id，根据此id查询表，返回结果
        $user_id = $this->getUserId();
        $cond['target_user_id'] = ['=', $user_id];
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        $list = D('Doctor')->getList([],[],[]);
        $page = input('post.current_page',0);
        $per_page = input('post.per_page',0);
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;
        $this->jsonReturn($ret);
    }

    /**
     * 删除医生信息
     */
    public function remove(){
        $ret = ['code' => 1, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            $res = D('Doctor')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['code'] = 2;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 新建医生信息
     */
    public function create(){
        $params = input('post.');
        $cond = [];
        $cond['id'] = ['<>', $this->getUserId()];

        if(!empty($params)) {
            $ret = ['error_code' => 0, 'msg' => '新建成功'];
            $data['name'] = input('post.doctor_name');
            $data['photo'] = input('post.doctor_photo');
            $photo_origin = input('post.doctor_photo_origin');
            $data['gender'] = input('post.hospital_gender');
            $data['age'] = input('post.hospital_age');
            $data['position'] = input('post.hospital_position');
            $data['phone'] = input('post.doctor_phone');
            $data['email'] = input('post.doctor_email');
            $data['address'] = input('post.doctor_address');
            $data['postcode'] = input('post.postcode');
            $data['info'] = input('post.doctor_info');
            $data['honor'] = input('post.doctor_honor');
            $data['remark'] = input('post.doctor_remark');
            $res = D('Doctor')->addData($data);
            if(!empty($res['errors'])) {
                $ret['error_code'] = 2;
                $ret['msg'] = '新建失败';
                $ret['errors'] = $res['errors'];
            }
            $this->jsonReturn($ret);
        }
        $select = ['id,name'];
        $hospital = D('Hospital')->getHospital($select,[]);

        $office = D('Office')->getOffice($select,[]);

        return view('', ['office' => $office,'hospital' =>$hospital]);
    }


    /**
     * 获取医生信息
     */
    function info(){
        $id = input('get.id');
        return view('', ['id' => $id]);
    }

    /**
     * 获取医生详情
     */
    public function getDoctorInfo(){
        $id = input('post.id');
        $ret = ['error_code' => 0, 'msg' => ''];
        $list = D('Doctor')->getById($id);
        $hospital_office_id = $list['hospital_office_id'];
        $hospital_office = D('HospitalOffice')->getById($hospital_office_id);
        $hospital_id = $hospital_office['hospital_id'];
        $office_id = $hospital_office['office_id'];
        $hospital_info = D('Hospital')->getById($hospital_id);
        $office_info = D('Office')->getById($office_id);
        $ret['info'] = $list;
        $ret['hospital'] = ['name' => $hospital_info['name']];
        $ret['office'] = ['name' => $office_info['name']];
        $this->jsonReturn($ret);
    }

}