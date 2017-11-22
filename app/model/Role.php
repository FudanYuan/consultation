<?php 
/**
 * 角色模型
 * Author yzs
 * Create 2017.8.18
 */
namespace app\model;

use think\Model;
use think\Db;

class Role extends Model{
 	protected $table = 'consultation_role_admin';
 	protected $pk = 'id';
 	protected $fields = array(
 		'id', 'name','remark','status','create_time','update_time'
 	);
 	protected $type = [
 			'id' => 'integer',
 			'status' => 'integer',
            'create_time' => 'integer',
            'update_time' => 'integer'
 		];
 	
 	/**
 	 * 角色列表
 	 * @param array $cond
 	 */
 	public function getList($cond = []){
 		if(!isset($cond['status'])){
            $cond['status'] = 1;
        }
 		return $this->field('id,name,remark,create_time')
            ->where($cond)
            ->select();
 	}

    /**
     * 根据ID获取角色信息
     * @param $role_id
     * @return bool
     */
 	public function getById($role_id){
 		if(!$role_id) return false;
 		$res = $this->field('id,name,remark')
            ->where(['id' => $role_id, 'status' => 1])
            ->find();
 		if(!empty($res)){
 			$actions = Db::table('consultation_role_action_admin')
                ->where(['role_id' => $role_id, 'status' => 1])
                ->column('action_id');
 			$res['actions'] = $actions;
 		}
 		return $res;
 	}

    /**
     * 创建角色
     * @param $data
     * @return bool
     */
 	public function addData($data){
 		$authority = false;
 		if(isset($data['authority']) && !empty($data['authority'])){
 			$authority = json_decode($data['authority'], true);
 			unset($data['authority']);
 		}
        if(!isset($data['status']))
            $data['status'] = 1;
 		$data['create_time'] = $data['update_time'] = $_SERVER['REQUEST_TIME'];
 		Db::startTrans();
 		$flag = true;
 		$res = $this->save($data);
 		if($res && $authority){
 			$role_id = $this->id;
 			$lines = $this->addRoleAction($role_id, $authority);
 			if($lines != count($authority)){
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
     * 编辑角色
     * @param $role_id
     * @param $data
     * @return bool
     */
 	public function saveData($role_id, $data){
 		$authority = false;
 		if(isset($data['authority']) && !empty($data['authority'])){
 			$authority = json_decode($data['authority'], true);
 			unset($data['authority']);
 		}
 		$data['update_time'] = $_SERVER['REQUEST_TIME'];
 		Db::startTrans();
 		$flag = true;
 		$res = $this->save($data, ['id' => $role_id]);
 		if($res){
 			$actions = $this->getRoleActions($role_id);
 			$removes = array_diff($actions, $authority);
 			$adds = array_diff($authority, $actions);
 			if(!empty($removes)){
	 			$res2 = $this->removeRoleActions($role_id, $removes);
	 			if($res2 != count($removes)){
	 				$flag = false;
	 			}
 			}
 			if(!empty($adds)){
	 			$res3 = $this->addRoleAction($role_id, $adds);
	 			if($res3 != count($adds)){
	 				$flag = false;
	 			}
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
     * 删除
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
 	public function remove($cond = []){
 		$res = $this->save(['status' => 2], $cond);
 		if($res === false) throw new MyException('2', '删除失败');
 		return $res;
 	}

    /**
     * 添加角色权限
     * @param $role_id
     * @param $action_ids
     * @return int|string
     */
 	public function addRoleAction($role_id, $action_ids){
 		$data = [];
 		$time = $_SERVER['REQUEST_TIME'];
 		foreach($action_ids as $v){
 			array_push($data, ['action_id' => $v, 'role_id' => $role_id, 'status' => 1, 'create_time' => $time, 'update_time' => $time]);
 		}
 		return Db::table('consultation_role_action_admin')->insertAll($data);
 	}

    /**
     * 获取角色权限列表
     * @param $role_id
     * @return array
     */
 	public function getRoleActions($role_id){
 		return Db::table('consultation_role_action_admin')->where(['role_id' => $role_id, 'status' => 1])->column('action_id');
 	}

    /**
     * 删除角色权限
     * @param $role_id
     * @param $action_ids
     * @return int
     */
 	public function removeRoleActions($role_id, $action_ids){
 		return Db::table('consultation_role_action_admin')->where(['role_id' => $role_id, 'action_id' => ['in', $action_ids]])->delete();
 	}

    /**
     * 根据角色获取操作列表
     * @param $role_id
     * @return array
     */
 	public function getActionsByRoleId($role_id){
 		if(!$role_id) return [];
 		$res = [];
 		$actions = Db::table('consultation_role_action_admin')->alias('a')->field('b.id,b.name,b.tag,b.pid,b.pids,level')
 			->where(['a.role_id' => $role_id, 'a.status' => 1])->join('consultation_action_admin b', 'a.action_id=b.id', 'LEFT')
 			->column('tag');
 		return $actions;
 	}
 }
?>