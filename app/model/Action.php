<?php 
/**
 * 操作模型
 * Author yzs
 * Create 2017.8.18
 */
namespace app\model;

use think\Model;
use think\Db;

class Action extends Model{
 	protected $table = 'vox_action_admin';
 	protected $pk = 'id';
 	protected $fields = array(
 		'id', 'name','tag','pid','pids','level','status','createtime','updatetime'
 	);
 	protected $type = [
 			'id' => 'integer',
 			'pid' => 'integer',
 			'level' => 'integer',
 			'status' => 'integer'
 		];
 	const ROLE_ACTIONS = 'admin_role_actions';
 	
 	/**
 	 * 获取格式化后的操作列表
 	 */
 	public function getActions(){
 		$data = [];
 		$actions = $this->field('id,name,tag,pid,pids,level')
            ->where('status', 1)
            ->select();
 		return $actions;
 	}
 }
?>