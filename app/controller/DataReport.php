<?php
/**
 * 舆情报告--控制器
 * Created by shiren.
 * time 2017.10.19
 */
namespace app\controller;

class DataReport extends Common
{
    public $exportCols = [];
    public $colsText = [];


    /**
     * 舆情报告
     * @return \think\response\View
     */
    public function index(){
        return view('', []);
    }


}