<?php 
/**
 * 首页--控制器
 * author：yzs
 * create：2017.8.15
 */
namespace app\controller;

use app\model\Data;


class Index extends Common{
	/**
	 * 首页
	 * @return \think\response\View
	 */
	public function index(){
        return view('', []);

	}
	/**
	 * 清除缓存
	 */
	public function clearcache(){
		$ret = ['error_code' => 0, 'msg' => '成功'];
		cache_del(CACHE_NAME);
		$this->jsonReturn($ret);
	}

    private function getSection($secid){
		switch($secid){
            case 1: //研究方向
                $sec = 'ResearchArea';
                break;
            case 2: //科研成果
                $sec = 'Result';
                break;
            case 3: //团队成员
                $sec = 'Member';
                break;
            case 4: //最新动态
                $sec = 'News';
                break;
		}
		return $sec;
	}
}
?>