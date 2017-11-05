<?php
/**
 * 操作日志--控制器
 * Created by shiren.
 * time 2017.10.19
 */
namespace app\controller;

class OperationLog extends Common
{
    public $exportCols = [];
    public $colsText = [];

    /**
     * 操作日志
     * @return \think\response\View
     */
    public function index()
    {
        return view('', []);
    }

    /**
     *
     */
    public function getLogList(){
        $params = input('post.');
        // 获取当前登陆的用户id，根据此id查询表，返回结果
        $user_id = $this->getUserId();
        $page = input('post.current_page',0);
        $per_page = input('post.per_page',0);
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        $cond['user_id'] = ['=', $user_id];
        $list = D('OperationLog')->getList();
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;
        $this->jsonReturn($ret);
    }
}