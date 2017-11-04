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
 	protected $table = 'vox_role_admin';
 	protected $pk = 'id';
 	protected $fields = array(
 		'id', 'name','remark','status','createtime','updatetime'
 	);
 	protected $type = [
 			'id' => 'integer',
 			'status' => 'integer'
 		];
 	
 	/**
 	 * 角色列表
 	 * @param array $cond
 	 */
 	public function getList($cond = []){
 		$cond['status'] = 1;
 		return $this->field('id,name,remark,createtime')->where($cond)->paginate(10);
 	}
 	/**
 	 * 获取角色列表
 	 */
 	public function getRoleList(){
 		return $this->field('id,name')->where('status', 1)->select();
 	}

    /**
     * 根据ID获取角色信息
     * @param $roleid
     * @return bool
     */
 	public function getById($roleid){
 		if(!$roleid) return false;
 		$res = $this->field('id,name,remark')->where(['id' => $roleid, 'status' => 1])->find();
 		if(!empty($res)){
 			$actions = Db::table('vox_role_action_admin')->where(['roleid' => $roleid, 'status' => 1])->column('actionid');
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
 		$data['createtime'] = $data['updatetime'] = $_SERVER['REQUEST_TIME'];
 		Db::startTrans();
 		$flag = true;
 		$res = $this->save($data);
 		if($res && $authority){
 			$roleid = $this->id;
 			$lines = $this->addRoleAction($roleid, $authority);
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
     * @param $roleid
     * @param $data
     * @return bool
     */
 	public function saveData($roleid, $data){
 		$authority = false;
 		if(isset($data['authority']) && !empty($data['authority'])){
 			$authority = json_decode($data['authority'], true);
 			unset($data['authority']);
 		}
 		$data['updatetime'] = $_SERVER['REQUEST_TIME'];
 		Db::startTrans();
 		$flag = true;
 		$res = $this->save($data, ['id' => $roleid]);
 		if($res){
 			$actions = $this->getRoleActions($roleid);
 			$removes = array_diff($actions, $authority);
 			$adds = array_diff($authority, $actions);
 			if(!empty($removes)){
	 			$res2 = $this->removeRoleActions($roleid, $removes);
	 			if($res2 != count($removes)){
	 				$flag = false;
	 			}
 			}
 			if(!empty($adds)){
	 			$res3 = $this->addRoleAction($roleid, $adds);
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
 	 */
 	public function addRoleAction($roleid, $actionids){
 		$data = [];
 		$time = $_SERVER['REQUEST_TIME'];
 		foreach($actionids as $v){
 			array_push($data, ['actionid' => $v, 'roleid' => $roleid, 'status' => 1, 'createtime' => $time, 'updatetime' => $time]);
 		}
 		return Db::table('tax_role_action_admin')->insertAll($data);
 	}
 	/**
 	 * 获取角色权限列表
 	 */
 	public function getRoleActions($roleid){
 		return Db::table('tax_role_action_admin')->where(['roleid' => $roleid, 'status' => 1])->column('actionid');
 	}

    /**
     * 删除角色权限
     * @param $roleid
     * @param $actionids
     * @return int
     */
 	public function removeRoleActions($roleid, $actionids){
 		return Db::table('tax_role_action_admin')->where(['roleid' => $roleid, 'actionid' => ['in', $actionids]])->delete();
 	}
 	/**
 	 * 根据角色获取操作列表
 	 */
 	public function getActionsByRoleId($roleid){
 		if(!$roleid) return [];
 		$res = [];
 		$actions = Db::table('tax_role_action_admin')->alias('a')->field('b.id,b.name,b.tag,b.pid,b.pids,level')
 			->where(['a.roleid' => $roleid, 'a.status' => 1])->join('tax_action_admin b', 'a.actionid=b.id', 'LEFT')
 			->column('tag');
 		return $actions;
 	}
 }
?>