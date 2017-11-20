<?php
/**
 * 管理员账户模型
 * Author yzs
 * Create 2017.8.15
 */
namespace app\model;

use think\Model;
use think\Db;
use think\Debug;

class UserAdmin extends Model{
    protected $table = 'consultation_user_admin';
    protected $pk = 'id';
    protected $fields = array(
        'id', 'doctor_id', 'username','pass','role_id','remark','status','login_time','create_time','update_time'
    );
    protected $type = [
        'id' => 'integer',
        'doctor_id' => 'integer',
        'role_id' => 'integer',
        'status' => 'integer'
    ];
    const USER_TOKEN = 'admin_user_token';
    const TOKEN_USER = 'admin_token_user';

    /**
     * 账号列表
     * @param array $cond
     */
    public function getList($cond = []){
        if(!isset($cond['status'])){
            $cond['status'] = ['<>', 2];
        }
        return $this->field('id,doctor_id,logo,username,status,create_time,login_time,remark')
            ->where($cond)
            ->select();
    }

    /**
     * 根据ID获取账号
     * @param $id
     * @return mixed
     */
    public function getById($id){
        return $this->field('id,doctor_id,logo,username,pass,role_id,remark,status')
            ->where('id', $id)
            ->find();
    }

    /**
     * 根据医生ID获取用户
     * @param $doctor_id
     * @return mixed
     */
    public function getUserByDoctorId($doctor_id){
        return $this->field('id,doctor_id,logo,username,pass,status,role_id')
            ->where(['doctor_id' => $doctor_id, 'status' => ['<>', 2]])
            ->find();
    }

    /**
     * 根据token获取用户
     * @param $token
     * @return array|mixed
     */
    public function getUserByToken($token){
        if(!$token) return [];
        return json_decode(cache_hash_hget(self::TOKEN_USER, $token), true);
    }

    /**
     * 根据用户名获取用户
     * @param $username
     * @return mixed
     */
    public function getUserByUsername($username){
        return $this->field('id,doctor_id,logo,username,pass,status,role_id')
            ->where(['username' => $username, 'status' => ['<>', 2]])
            ->find();
    }


    /**
     * 根据id获取医生ID
     * @param $id
     * @return mixed
     */
    public function getDoctorIdById($id){
        $res = $this->field('doctor_id')
            ->where(['id' => $id])
            ->find();
        return $res;
    }

    /**
     * 关联表
     * @param $select
     * @param $cond
     * @return mixed
     */
    public function getUserAdmin($select,$cond){
        $res = $this->alias('a')->field($select)
            ->join('consultation_doctor b','b.id = a.doctor_id')
            ->join('consultation_hospital_office c','c.id = b.hospital_office_id')
            ->join('consultation_hospital d','d.id = c.hospital_id')
            ->join('consultation_office e','e.id = c.office_id')
            ->where($cond)
            ->select();
        return $res;
    }


    /**
     * 创建管理员用户
     * @param $data
     * @return false|int
     */
    public function addData($data){
        if(!isset($data['status']))
            $data['status'] = 1;
        $data['create_time'] = $data['update_time'] = $_SERVER['REQUEST_TIME'];
        if(isset($data['pass']) && $data['pass']){
            $data['pass'] = md5($data['pass']);
        }
        Db::startTrans();
        $flag = true;
        $res = $this->save($data);
        if($res){
            $res = Db::table('consultation_doctor')->where(['id' => $data['doctor_id']])->update(['status' => 3]);
            if(!$res){
                $flag = false;
            }
        }else{
            $flag = false;
        }
        if($flag){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }
    }

    /**
     * 编辑管理员用户
     * @param $id
     * @param $data
     * @return false|int
     */
    public function saveData($id, $data){
        $data['update_time'] = $_SERVER['REQUEST_TIME'];
        if(isset($data['pass']) && $data['pass']) $data['pass'] = md5($data['pass']);
        return $this->save($data, ['id' => $id]);
    }

    /**
     * 删除
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public function remove($cond = []){
        Db::startTrans();
        $flag = true;
        $res = $this->save(['status' => 2], $cond);

        if($res){
            $cond_doctor = [];
            foreach ($cond['id'] as $item){
                if($item != 'in'){
                    $doctor = $this->getDoctorIdById((int)$item);
                    $doctor_id = $doctor['doctor_id'];
                    array_push($cond_doctor, $doctor_id);
                }
            }
            $res = Db::table('consultation_doctor')->where(['id' => ['in', $cond_doctor]])->update(['status' => 1]);
            if(!$res){
                $flag = false;
            }
        }else{
            $flag = false;
        }
        if($flag){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            throw new MyException('2', '删除失败');
        }
    }

    /**
     * 用户登录
     * @param $data
     * @throws MyException
     */
    public function dologin($data){
        if(empty($data['username'])) throw new MyException('用户名不能为空');
        $user = $this->getUserByUsername($data['username']);
        if(empty($user)) throw new MyException('用户不存在');
        if($user['status'] == 3) throw new MyException('用户已被禁用');
        if(md5($data['pass']) != $user['pass']) throw new MyException('密码错误');
        $this->recordLogin($user);
    }

    /**
     * 登出
     * @param $token
     */
    public function logout($token){
        $this->recordLogout($token);
    }
    private function recordLogout($token){
        if(!$token) return;
        $user = json_decode(cache_hash_hget(self::TOKEN_USER, $token), true);
        if(!empty($user)){
            cache_hdel(self::TOKEN_USER, $token);
            $tokens = json_decode(cache_hash_hget(self::USER_TOKEN, $user['id']), true);
            if(!empty($tokens)){
                $k = array_search($token, $tokens);
                if(!is_null($k)){
                    unset($tokens[$k]);
                    cache_hash_hset(self::USER_TOKEN, $token, json_encode($tokens));
                }
            }
        }
        session('token', null);
    }

    /**
     * 登录记录
     * @param $user
     * @throws MyException
     */
    private function recordLogin($user){
        $token = $this->generateToken($user['id']);
        //存储用户-token
        $tokens = json_decode(cache_hash_hget(self::USER_TOKEN, $user['id']), true);
        if(empty($tokens)){
            $tokens = [];
        }
        array_push($tokens, $token);
        cache_hash_hset(self::USER_TOKEN, $user['id'], json_encode($tokens));
        //存储token-用户
        $data = [
            'id' => $user['id'],
            'create_time' => $_SERVER['REQUEST_TIME'],
            'username'  => $user['username'],
            'role_id' => $user['role_id']
        ];
        cache_hash_hset(self::TOKEN_USER, $token, json_encode($data));
        $res = $this->save(['login_time' => $_SERVER['REQUEST_TIME'], 'update_time' => $_SERVER['REQUEST_TIME']], ['id' => $user['id']]);
        if(!$res) throw new MyException('登录失败');
        session('token', $token);
    }

    /**
     * 生成Token
     * @param $id
     * @return string
     * @throws \Exception
     */
    private function generateToken($id){
        if(!$id) throw new \Exception('创建token失败');
        $rand = $_SERVER['REQUEST_TIME'].rand(0, 1000);
        return md5($id.$rand);
    }

    /**
     * 检查密码是否正确
     * @param $user_id
     * @param $pass
     * @return bool
     */
    public function checkPass($user_id, $pass){
        $res['errors'] = [];
        $user = $this->getById($user_id);
        if(md5($pass) != $user['pass']){
            $res['errors'] = ['pass' => '密码错误'];
        }
        return $res;
    }
}
?>