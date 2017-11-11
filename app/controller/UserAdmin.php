<?php 
/**
 * 管理员账户-控制器
 * author：yzs
 * create：2017.8.15
 */
namespace app\controller;

use app\model\MyException;

class UserAdmin extends Common{
	/**
	 * 后台登录
	 */
	public function login(){
		$data = input('post.');
		if(!empty($data)){
			$ret = ['error_code' => 0, 'msg' => '登陆成功'];
			try{
				D('UserAdmin')->dologin($data);
                $log['user_id'] = $this->getUserId();
                $log['IP'] = $this->getUserIp();
                $log['section'] = '用户登录／用户退出';
                $log['action_descr'] = '用户登录';
                D('OperationLog')->addData($log);
			}catch(MyException $e){
				$ret['error_code'] = 1;
				$ret['msg'] = $e->getMessage();
			}catch(\Exception $e){
				$ret['error_code'] = 1;
				$ret['msg'] = $e->getMessage();
			}
			$this->jsonReturn($ret);
		}
		return view('', []);
	}


    /**
     * 修改密码
     * @return \think\response\View
     */
    public function changePwd()
    {
        return view('', []);
    }

    /**
     * 用户信息
     * @return \think\response\View
     */
    public function account()
    {
        return view('', []);
    }


    /**
	 * 登出
	 */
	public function dologout(){
		$ret = ['error_code' => 0, 'data' => [], 'msg' => ''];
		try{
			$token = session('token');
			if(!$token) $token = input('request.token');
			if(!$token) throw new MyException('token不能空');
            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '用户登录／用户退出';
            $log['action_descr'] = '用户退出';
            D('OperationLog')->addData($log);
            D('UserAdmin')->logout($token);
		}catch(MyException $e){
			$ret['error_code'] = 1;
			$ret['msg'] = $e->getMessage();
		}catch(\Exception $e){
			$ret['error_code'] = 1;
			$ret['msg'] = '系统异常';
			$ret['msg'] = $e->getMessage();
		}
		$this->jsonReturn($ret);
	}

	/**
	 * 管理员列表
	 * @return \think\response\View
	 */
	public function index(){
		return view('', []);
	}

    /**
     * 获取用户列表
     */
	public function getUserList(){
        $params = input('post.');
        $status = input('post.status', -1);
        $username = input('post.username', '');
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
        $cond = [];
        if($status != -1){
            $cond['status'] = $status;
        }
        if($username){
            $cond['username'] = ['like', '%'.$username.'%'];
        }
        $list = D('UserAdmin')->getList($cond);
        foreach ($list as &$item){
            $doctor_id = $item['doctor_id'];
            $doctor_info = D('Doctor')->getById($doctor_id);
            $item['doctor_id'] = $doctor_id;
            $item['doctor_name'] = $doctor_info['name'];
            $item['doctor_phone'] = $doctor_info['phone'];
            $item['doctor_email'] = $doctor_info['email'];
            $hospital_office_id = $doctor_info['hospital_office_id'];
            $hospital_office_info = D('HospitalOffice')->getById($hospital_office_id);
            $hospital_id = $hospital_office_info['hospital_id'];
            $office_id = $hospital_office_info['office_id'];
            $hospital_info = D('Hospital')->getById($hospital_id);
            $office_info = D('office')->getById($office_id);
            $item['hospital_id'] = $hospital_id;
            $item['hospital_name'] = $hospital_info['name'];

            $item['office_id'] = $hospital_id;
            $item['office_name'] = $office_info['name'];
        }

        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '用户设置';
        $log['action_descr'] = '查看用户列表';
        //D('OperationLog')->addData($log);

        $page = input('post.current_page', 0);
        $per_page = input('post.per_page', 0);
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;
        $ret['params'] = $params;
        $this->jsonReturn($ret);
    }

    /**
     * 检验用户的合法性
     */
    public function verify(){
        $params = input('post.');
        $username = input('post.username', '');
        $ret = ['valid' => 1];
        if($username){
            $cond['username'] = ['=', $username];
            $res = D('UserAdmin')->getList($cond);
            if(!empty($res)){
                $ret['valid'] = 0;
            }
        }
        $this->jsonReturn($ret);
    }
	/**
	 * 新建管理员账号
	 */
	public function create(){
		$data = input('post.');
		if(!empty($data)){
			$ret = ['error_code' => 0, 'msg' => '创建用户成功'];
			$res = D('UserAdmin')->addData($data);
			if(!$res){
				$ret['error_code'] = 1;
				$ret['msg'] = '创建用户失败';
			}

            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '用户设置';
            $log['action_descr'] = '新建用户-' . $data['id'];
            D('OperationLog')->addData($log);

            $this->jsonReturn($ret);
		}
		$roles = D('Role')->getList();
        $doctors = D('Doctor')->getList(['status' => 1]);
		return view('', ['roles' => $roles, 'doctors' => $doctors]);
	}
	/**
	 * 编辑账号
	 */
	public function edit(){
		$data = array_filter(input('post.'));
		if(!empty($data)){
			$ret = ['error_code' => 0, 'msg' => ''];
			$res = D('UserAdmin')->saveData($data['id'], $data);
			if(!$res){
				$ret['error_code'] = 1;
				$ret['msg'] = '编辑用户失败';
			}

            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '用户设置';
            $log['action_descr'] = '编辑用户-' . $data['id'];
            D('OperationLog')->addData($log);

            $this->jsonReturn($ret);
		}

		$id = input('get.id');
		$data = D('UserAdmin')->getById($id);
		$roles = D('Role')->getList();
		return view('', ['data' => $data, 'roles' => $roles]);
	}
	/**
	 * 批量删除
	 */
	public function remove(){
		$ret = ['code' => 1, 'msg' => '删除成功'];
		$ids = input('post.ids');
		try{
			$res = D('UserAdmin')->remove(['id' => ['in', $ids]]);
		}catch(MyException $e){
			$ret['code'] = 2;
			$ret['msg'] = '删除失败';
		}
        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '用户设置';
        $log['action_descr'] = '删除用户' . $ids;
        D('OperationLog')->addData($log);

        $this->jsonReturn($ret);
	}

	/**
	 * 角色列表
	 */
	public function roles(){
        $list = D('Role')->getList();
		return view('', ['list' => $list]);
	}

	public function getRolesList(){
        $params = input('post.');
        $ret = ['error_code' => 0, 'data' => [], 'msg' => ""];
//        $user_id = $this->getUserId();
        $cond = [];
//        $cond['hospital_id'] = ['=', $user_id];
        $list = D('Role')->getList();
        $page = input('post.current_page',0);
        $per_page = input('post.per_page',0);
        //分页时需要获取记录总数，键值为 total
        $ret["total"] = count($list);
        //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
        $ret["data"] = array_slice($list, ($page-1)*$per_page, $per_page);
        $ret['current_page'] = $page;

        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '角色设置';
        $log['action_descr'] = '查看角色列表';
        //D('OperationLog')->addData($log);

        $this->jsonReturn($ret);
    }
	/**
	 * 新建角色
	 */
	public function roleCreate(){
		$data = input('post.');
		if(!empty($data)){
			$ret = ['error_code' => 0, 'msg' => ''];
			$res = D('Role')->addData($data);
			if(!$res){
				$ret['error_code'] = 1;
				$ret['msg'] = '创建角色失败';
			}

            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '角色设置';
            $log['action_descr'] = '新建角色-' . $data['id'];
            D('OperationLog')->addData($log);

			$this->jsonReturn($ret);
		}
		return view('', []);
	}
	/**
	 * 编辑角色
	 */
	public function roleEdit(){
		$data = input('post.');
		if(!empty($data)){
			$ret = ['error_code' => 0, 'msg' => '编辑角色成功'];
            $res = D('Role')->saveData($data['id'], $data);
            $ret['res'] = $res;
			if(!$res){
				$ret['error_code'] = 1;
				$ret['msg'] = '编辑角色失败';
			}

            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '角色设置';
            $log['action_descr'] = '编辑角色-' . $data['id'];
            D('OperationLog')->addData($log);

            $this->jsonReturn($ret);
		}
		$role_id = input('get.id');
		$role = D('Role')->getById($role_id);
		return view('', ['role' => $role]);
	}
	/**
	 * 批量删除
	 */
	public function roleRemove(){
		$ret = ['code' => 1, 'msg' => '成功'];
		$ids = input('post.ids');
		try{
			$res = D('Role')->remove(['id' => ['in', $ids]]);
		}catch(MyException $e){
			$ret['code'] = 2;
			$ret['msg'] = '删除失败';
		}

        $log['user_id'] = $this->getUserId();
        $log['IP'] = $this->getUserIp();
        $log['section'] = '角色设置';
        $log['action_descr'] = '删除角色-' . $ids;
        D('OperationLog')->addData($log);

        $this->jsonReturn($ret);
	}

    /**
     * 获取用户名称
     */
	public function getUserName(){
        $user_id = $this->getUserId();
        $ret = ['error_code' => 0, 'msg' => ''];
        $ret['username'] = D('UserAdmin')->getById($user_id)['username'];
        $this->jsonReturn($ret);
    }
}
?>