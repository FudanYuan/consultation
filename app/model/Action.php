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
 	protected $table = 'consultation_action_admin';
 	protected $pk = 'id';
 	protected $fields = array(
 		'id', 'name','tag','pid','pids','level','status','create_time','update_time'
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


    /**
     * 新建权限
     * @param $data
     * @return array
     */
 	public function addData($data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)){
            $data['status'] = 1;
            $data['create_time'] = time();
            $this->save($data);
        }
        return $ret;
 	}

    /**
     * 批量增加权限
     * @param $dataSet
     * @return array
     */
    public function addAllData($dataSet){
        $ret = [];
        $ret['error'] = [];
        foreach ($dataSet as &$data) {
            $errors = $this->filterField($data);
            if(!empty($errors)){
                $ret['errors'] = $errors;
                return $ret;
            }
            unset($data['id']);
            $data['status'] = 1;
            $data['create_time'] = time();
        }
        try{
            $ret['result'] = $this->saveAll($dataSet);
        } catch(MyException $e){
            $ret['exception'] = $e;
        }
        return $ret;
    }


    /**
     * 删除操作权限
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public function remove($cond = []){
        $res = Db::execute('truncate table consultation_action_admin');
        if($res === false) throw new MyException('1', '删除失败');
        return $res;
    }

    /**
     * 过滤必要字段
     * @param $data
     * @return array
     */
    private function filterField($data){
        $errors = [];
        if(isset($data['id']) && !$data['id']){
            $errors['id'] = 'id不能为空';
        }
        if(isset($data['name']) && !$data['name']){
            $errors['name'] = '名称不能为空';
        }
        if(isset($data['tag']) && !$data['tag']){
            $errors['tag'] = '备注不能为空';
        }
        if(isset($data['level']) && !$data['level']){
            $errors['level'] = '层次不能为空';
        }
        if(isset($data['pids']) && $data['pids'] == ''){
            $errors['pids'] = '父节点ids不能为空';
        }
        return $errors;
    }
 }
?>