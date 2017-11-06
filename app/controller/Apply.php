<?php
/**
 * 会诊申请--控制器
 * Created by shiren.
 * time 2017.10.19
 */
namespace app\controller;

class Apply extends Common
{
    public $exportCols = [];
    public $colsText = [];

    /**
     * 通知公告
     * @return \think\response\View
     */
    public function index()
    {
        return view('', []);
    }

    /**
     * 获取通知公告列表
     */
    public function getApplyList(){
        $params = input('post.');
        // 获取当前登陆的用户id，根据此id查询表，返回结果
        $user_id = $this->getUserId();
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        $cond['target_user_id'] = ['=', $user_id];

        if(empty($params)){
            $cond['status'] = ['=', 0];
//            $list = D('Apply')->getList($cond);
            $list = [];
            $list[0] = ['id' => 1, 'hospital_id' => 1, 'hospital_logo' => '', 'hospital_name' => '医院甲',
                'doctor_id' => 1, 'doctor_name' => '张三', 'phone' => '135210263021','apply_type' => 1,
                'apply_project' => 1, 'consultation_goal' => '放假啦减肥放假啦', 'apply_date' => 1509871680, 'status' => 1,
                'price' => 1000, 'is_charge' => 0, 'create_time' =>  1509871680
            ];
            $list[1] = ['id' => 2, 'hospital_id' => 2, 'hospital_logo' => '', 'hospital_name' => '医院乙',
                'doctor_id' => 1, 'doctor_name' => '张三', 'phone' => '135210263021', 'apply_type' => 1,
                'apply_project' => 1, 'consultation_goal' => '放假啦减肥放假啦', 'apply_date' => 1509871680, 'status' => 1,
                'price' => 1000, 'is_charge' => 0, 'create_time' =>  1509871680
            ];
            for($i=0;$i<count($list);$i++){
                $list[$i]['time'] = formatTime($list[$i]['create_time']);
                $list[$i]['consultation_goal'] = formatText($list[$i]['consultation_goal'], 10);
            }
            $ret["total"] = count($list);
            $ret["data"] = $list;
            $this->jsonReturn($ret);
        }
        else{
            //$list = D('Apply')->getList($cond);
            $list = [];
            $list[0] = ['id' => 1, 'hospital_id' => 1, 'hospital_name' => '医院甲',
                'doctor_id' => 1, 'doctor_name' => '张三', 'phone' => '135210263021','apply_type' => 1,
                'apply_project' => 1, 'consultation_goal' => '放假啦减肥放假啦', 'apply_date' => 1509871680, 'status' => 1,
                'price' => 1000, 'is_charge' => 0, 'create_time' =>  1509871680
            ];
            $list[1] = ['id' => 2, 'hospital_id' => 2, 'hospital_name' => '医院乙',
                'doctor_id' => 1, 'doctor_name' => '张三', 'phone' => '135210263021', 'apply_type' => 1,
                'apply_project' => 1, 'consultation_goal' => '放假啦减肥放假啦', 'apply_date' => 1509871680, 'status' => 1,
                'price' => 1000, 'is_charge' => 0, 'create_time' =>  1509871680
            ];
            $page = input('post.current_page',0);
            $per_page = input('post.per_page',0);
            //分页时需要获取记录总数，键值为 total
            $ret["total"] = count($list);
            //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
            $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
            $ret['current_page'] = $page;
        }
        $this->jsonReturn($ret);
    }

    /**
     * 删除公告
     */
    public function remove(){
        $ret = ['code' => 1, 'msg' => '删除成功'];
        $ids = input('post.ids');
        try{
            $res = D('Apply')->remove(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['code'] = 2;
            $ret['msg'] = '删除失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 标为已读
     */
    public function markRead(){
        $ret = ['code' => 1, 'msg' => '标记成功'];
        $ids = input('post.ids');
        try{
            $res = D('Apply')->markRead(['id' => ['in', $ids]]);
        }catch(MyException $e){
            $ret['code'] = 2;
            $ret['msg'] = '标记失败';
        }
        $this->jsonReturn($ret);
    }

    /**
     * 新建会诊申请
     */
    public function create(){
        $params = input('post.');
        /**
         * {"target_user_ids":[],
         * hospital_name: '',
         * office_name: '',
         * "apply_type":"2",
         * "doctor_name":"faf",
         * "patient_ID_number":"faf",
         * "patient_name":"faf",
         * "patient_gender":"2",
         * "patient_age":"fafaf",
         * "patient_phone":"faf",
         * "illness_state":"afaf",
         * "diagnose_state":"afaf",
         * "consultation_goal":"faf",
         * "other_apply":"afaf",
         * "apply_date":"",
         * "apply_doctor_name":"fafa"}
         */
        $cond = [];
        if(!empty($params)) {
            $data = [];
            $ret = ['error_code' => 0, 'msg' => '新建成功'];

            $data['patient_id'] = $params;
            $data['delivery_user_id'] = $this->getUserId();
        }

        $office = [];
        $office[0] = ['id' => 1, 'name' => '骨科'];
        $office[1] = ['id' => 2, 'name' => '眼科'];
        return view('', ['office' => $office]);
    }


}