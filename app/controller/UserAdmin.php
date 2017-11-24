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
            $code = $data['code'];
            unset($data['code']);
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
			if(!$this->check_verify($code)){
                $ret = ['error_code' => 1, 'msg' => '验证码错误'];
            }
			$this->jsonReturn($ret);
		}
		return view('', []);
	}

    /**
     * 用户信息
     * @return \think\response\View
     */
    public function account()
    {
        $user_id = $this->getUserId();
        $doctor = D('UserAdmin')->getById($user_id);
        $doctor_id = $doctor['doctor_id'];
        $data = D('Doctor')->getById($doctor_id);
        $data['logo'] = $doctor['logo'];
        return view('', ['data' => $data]);
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
            $office_info = D('Office')->getById($office_id);
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
            $username = input('post.username', '');
            if($username){
                $cond['username'] = ['=', $username];
                $res = D('UserAdmin')->getList($cond);
                if(!empty($res)){
                    $ret['error_code'] = 1;
                    $ret['msg'] = '用户已存在';
                    $this->jsonReturn($ret);
                }
            }

			$res = D('UserAdmin')->addData($data);
			if(!$res){
				$ret['error_code'] = 1;
				$ret['msg'] = '创建用户失败';
                $this->jsonReturn($ret);
			}

            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '用户设置';
            $log['action_descr'] = '新建用户-' . $data['username'];
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
		$data = input('post.');
		if(!empty($data)){
			$ret = ['error_code' => 0, 'msg' => '编辑用户成功'];
			$res = D('UserAdmin')->saveData($data['id'], $data);
			if(!$res){
				$ret['error_code'] = 1;
				$ret['msg'] = '编辑用户失败';
                $this->jsonReturn($ret);
			}

            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '用户设置';
            $log['action_descr'] = '编辑用户-' . $data['username'];
            D('OperationLog')->addData($log);

            $this->jsonReturn($ret);
		}

		$id = input('get.id');
		$data = D('UserAdmin')->getById($id);
		$roles = D('Role')->getList();
        $doctors = D('Doctor')->getList(['status' => 3]);
		return view('', ['data' => $data, 'roles' => $roles, 'doctors' => $doctors]);
	}
	/**
	 * 批量删除
	 */
	public function remove(){
		$ret = ['error_code' => 0, 'msg' => '删除成功'];
		$ids = input('post.ids');
		try{
			$res = D('UserAdmin')->remove(['id' => ['in', $ids]]);
		}catch(MyException $e){
			$ret['error_code'] = 1;
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
     * 获取用户名称
     */
	public function getUserName(){
        $user_id = $this->getUserId();
        $ret = ['error_code' => 0, 'msg' => ''];
        $user_info = D('UserAdmin')->getById($user_id);
        $ret['username'] = $user_info['username'];
        $ret['logo'] = $user_info['logo'];
        $ret['user_id'] = $user_info['id'];
        $ret['doctor_id'] = $user_info['doctor_id'];
        $this->jsonReturn($ret);
    }

    /**
     * 修改账户信息
     */
    public function editAccount(){
        $data = input('post.');
        if(!empty($data)){
            $ret = ['error_code' => 0, 'msg' => '编辑账户成功'];
            $logo = input('post.logo', '');
            if($logo != ''){
                $ret['res'] = $logo;
                $user_id = $this->getUserId();
                $res = D('UserAdmin')->saveData($user_id, ['logo' => $logo]);
                if(!$res){
                    $ret['error_code'] = 1;
                    $ret['msg'] = '修改失败';
                    $this->jsonReturn($ret);
                }
            }

            unset($data['logo']);
            $res = D('Doctor')->saveData($data['id'], $data);

            if(!empty($res['errors'])){
                $ret['error_code'] = 1;
                $ret['errors'] = $res['errors'];
                $ret['msg'] = '编辑账户失败';
                $this->jsonReturn($ret);
            }

            $log['user_id'] = $this->getUserId();
            $log['IP'] = $this->getUserIp();
            $log['section'] = '账户设置';
            $log['action_descr'] = '编辑账户';
            D('OperationLog')->addData($log);

            $this->jsonReturn($ret);
        }
    }

    /**
     * 修改密码
     */
    public function editPass(){
        $data = input('post.');
        $ret = ['error_code' => 0, 'msg' => '修改成功'];
        $oldPwd = input('post.pass');
        $newPwd = input('post.new_password');
        $confirmPwd = input('post.confirm_password');
        $user_id = $this->getUserId();
        $res = D('UserAdmin')->checkPass($user_id, $oldPwd);
        if(!empty($res['errors']) || $newPwd != $confirmPwd){
            $ret['error_code'] = 1;
            $ret['msg'] = '修改失败';
            $ret['errors'] = $res['errors'];
            $this->jsonReturn($ret);
        }
        $res = D('UserAdmin')->saveData($user_id, ['pass' => $confirmPwd]);
        if(!$res){
            $ret['error_code'] = 1;
            $ret['msg'] = '修改失败';
            $this->jsonReturn($ret);
        }
        $this->jsonReturn($ret);
    }

    /**
     * 检测输入的验证码是否正确，$code为用户输入的验证码字符串，$id多个验证码标识
     * @param $code
     * @param string $id
     * @return mixed
     */
    private function check_verify($code, $id = ''){
        $captcha = new \think\captcha\Captcha;
        return $captcha->check($code, $id);
    }
}
?>