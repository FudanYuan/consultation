<?php 
/**
 * 标签--控制器
 * author：yzs
 * create：2017.8.15
 */
namespace app\controller;

class Tag extends Common{
	/**
	 * 标签列表
	 * @return \think\response\View
	 */
	public function index(){
		$params = input('get.');
		$section = input('get.section', -1);
		$title = input('get.title');
		$cond = [];
		if($section != -1){
			$cond['section'] = $section;
		}
		if($title){
			$cond['title'] = ['like', '%'.$title.'%'];
		}
		$sections = D('Tag')->getSections();
		$list = D('Tag')->getList($cond);
		return view('', ['list' => $list, 'cond' => $params, 'sections' => $sections]);
	}
	/**
	 * 批量删除
	 */
	public function remove(){
		$ret = ['code' => 1, 'msg' => '成功'];
		$ids = input('get.ids');
		try{
			$res = D('Tag')->remove(['id' => ['in', $ids]]);
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
		$data = input('post.');
		$sections = D('Tag')->getSections();
		if(!empty($data)){
			$res = D('Tag')->addData($data);
			if(!empty($res['errors']))
				return view('', ['errors' => $res['errors'], 'data' => $data, 'sections' => $sections]);
			else{
                $url = PRO_PATH . '/Tag/index';
                return "<script>window.location.href='".$url."'</script>";
			}
		}else{
			return view('', ['sections' => $sections]);
		}
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