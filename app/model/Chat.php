<?php
/**
 * 会诊沟通模型
 * Author FeiYu
 * Create 2017.11.5
 */
namespace app\model;

use think\Model;
use think\Db;

class Chat extends Model{
    protected $table = 'consultation_chat';
    protected $pk = 'id';
    protected $fields = array(
        'id', 'apply_id', 'source_user_id', 'type', 'content',
        'content_origin', 'status', 'create_time', 'update_time'
    );
    protected $type = [
        'id' => 'integer',
        'apply_id' => 'integer',
        'source_user_id' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer'
    ];

    /**
     * 获取消息列表
     * @param string $select
     * @param array $cond_or
     * @param array $cond_and
     * @param string $group
     * @return mixed
     */
    public function getList($select='*', $cond_or = [], $cond_and = [], $group = ''){
        if(!isset($cond_and['a.status'])){
            $cond_and['a.status'] = ['<>', 2];
        }
        $res = $this->alias('a')
            ->distinct(true)
            ->field($select)
            ->join('consultation_user_admin b','b.id = a.source_user_id')
            ->join('consultation_doctor c','c.id = b.doctor_id')
            ->join('consultation_hospital_office d','d.id = c.hospital_office_id')
            ->join('consultation_hospital e','e.id = d.hospital_id')
            ->join('consultation_office f','f.id = d.office_id')
            ->join('consultation_chat_user g','g.chat_id = a.id')
            ->where($cond_or)
            ->where($cond_and)
            ->group($group)
            ->select();
        return $res;
    }

    /**
     * 获取聊天记录
     * @param string $select1
     * @param string $select2
     * @param $apply_id
     * @param $user_id
     * @return int
     */
    public function getChatHistory($select1='*', $select2='*', $apply_id, $user_id){
        $res = Db::query('SELECT a.id as id, a.source_user_id as source_user_id,
            a.source_user_id as target_user_id,a.type as type, a.content as content,
            a.content_origin as content_origin, a.create_time as create_time,
            a.status as status, c.id as doctor_id, c.name as doctor_name,
            c.photo as doctor_logo, c.phone as doctor_phone, c.email as doctor_email,
            e.id as hospital_id, e.name as hospital_name, d.id as hospital_office_id,
            f.name as office_name FROM consultation_chat a
            INNER JOIN consultation_user_admin b ON b.id = a.source_user_id
            INNER JOIN consultation_doctor c ON c.id = b.doctor_id
            INNER JOIN consultation_hospital_office d ON d.id = c.hospital_office_id
            INNER JOIN consultation_hospital e ON e.id = d.hospital_id
            INNER JOIN consultation_office f ON f.id = d.office_id
            WHERE a.apply_id = '. $apply_id .
            ' AND a.source_user_id = ' . $user_id .
            ' AND a.status <> 2 UNION
            SELECT g.id as id, a.source_user_id as source_user_id,
            g.target_user_id as target_user_id, a.type as type, a.content as content,
            a.content_origin as content_origin, a.create_time as create_time,
            g.status as status, c.id as doctor_id, c.name as doctor_name,
            c.photo as doctor_logo, c.phone as doctor_phone, c.email as doctor_email,
            e.id as hospital_id, e.name as hospital_name, d.id as hospital_office_id,
            f.name as office_name FROM consultation_chat a
            INNER JOIN consultation_user_admin b ON b.id = a.source_user_id
            INNER JOIN consultation_doctor c ON c.id = b.doctor_id
            INNER JOIN consultation_hospital_office d ON d.id = c.hospital_office_id
            INNER JOIN consultation_hospital e ON e.id = d.hospital_id
            INNER JOIN consultation_office f ON f.id = d.office_id
            INNER JOIN consultation_chat_user g ON g.chat_id = a.id
            WHERE a.apply_id = '. $apply_id .
            ' AND g.target_user_id = ' . $user_id .
            ' AND a.status <> 2 order by consultation_chat.create_time asc');
        return $res;
//        $res = Db::table('consultation_chat')
//            ->field($select1)
//            ->alias('a')
//            ->join('consultation_user_admin b','b.id = a.source_user_id')
//            ->join('consultation_doctor c','c.id = b.doctor_id')
//            ->join('consultation_hospital_office d','d.id = c.hospital_office_id')
//            ->join('consultation_hospital e','e.id = d.hospital_id')
//            ->join('consultation_office f','f.id = d.office_id')
//            ->union(
//                function($query){
//                    $query->table('consultation_chat')
//                        ->alias('a')
//                        ->field('g.id as id, a.source_user_id as source_user_id,
//                        g.target_user_id as target_user_id, a.type as type, a.content as content,
//                        a.content_origin as content_origin, a.create_time as create_time,
//                        g.status as status, c.id as doctor_id, c.name as doctor_name,
//                        c.photo as doctor_logo, c.phone as doctor_phone, c.email as doctor_email,
//                        e.id as hospital_id, e.name as hospital_name, d.id as hospital_office_id,
//                        f.name as office_name')
//                        ->join('consultation_user_admin b','b.id = a.source_user_id')
//                        ->join('consultation_doctor c','c.id = b.doctor_id')
//                        ->join('consultation_hospital_office d','d.id = c.hospital_office_id')
//                        ->join('consultation_hospital e','e.id = d.hospital_id');
//                }
//            )
//            ->select();
//        return $res;
    }

    /**
     * 根据ID获取消息列表
     * @param $id
     * @return mixed
     */
    public function getById($id){
        $res = $this->field('id, apply_id, source_user_id, type, content,
        content_origin, status, create_time, update_time')
            ->where(['id' => $id])
            ->find();
        return $res;
    }

    /**
     * 更新消息列表
     * {@inheritDoc}
     * @see \think\Model::save()
     */
    public function saveData($id, $data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)){
            $data['update_time'] = $_SERVER['REQUEST_TIME'];
            $this->save($data, ['id' => $id]);
        }
        return $ret;
    }

    /**
     * 添加消息列表
     * @param $data
     * @return array
     */
    public function addData($data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)){
            if(!isset($data['status'])){
                $data['status'] = 1;
            }
            $data['create_time'] = $_SERVER['REQUEST_TIME'];

            $target_user_ids = $data['target_user_id'];
            unset($data['target_user_id']);

            Db::startTrans();
            $flag = true;
            $chat_id = Db::table('consultation_chat')->insertGetId($data);
            if($chat_id){
                // to do
                $lines = $this->addChatUser($target_user_ids, $chat_id);
                if($lines != count($target_user_ids)){
                    $errors['msg'] = '添加行数不相等';
                    $flag = false;
                }
            }else{
                $errors['msg'] = '新建失败';
                $flag = false;
            }
            if($flag){
                Db::commit();
            }else{
                Db::rollback();
            }
        }
        return $ret;
    }

    /**
     * 添加聊天对象用户
     * @param $target_user_ids
     * @param $chat_id
     * @return int|string
     */
    public function addChatUser($target_user_ids, $chat_id){
        $data = [];
        $status = 0; // 默认未读
        $time = $_SERVER['REQUEST_TIME'];
        foreach($target_user_ids as $v){
            array_push($data, ['chat_id' => $chat_id, 'target_user_id' => $v, 'status' => $status, 'create_time' => $time]);
        }
        return Db::table('consultation_chat_user')->insertAll($data);
    }

    /**
     * 批量增加消息
     * @param $dataSet
     * @return array
     */
    public function addAllData($dataSet){
        $ret = [];
        foreach ($dataSet as $data) {
            $errors = $this->filterField($data);
            $ret['errors'] = $errors;
            if(!empty($errors)){
                return $ret;
            }
        }
        $ret['result'] = $this->saveAll($dataSet);
        return $ret;
    }

    /**
     * 删除消息列表
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
     * 标记为已读
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public function markRead($cond = []){
        $res = Db::table('consultation_chat_user')
            ->where($cond)
            ->update(['status' => 1, 'update_time' => time()]);
        if($res === false) throw new MyException('2', '标记失败');
        return $res;
    }

    /**
     * 对结果进行过滤
     * @param $data
     * @param $fields
     * @return array
     */
    public function filterResult($data, $fields){
        $ret = [];
        for($i=0;$i<count($data);$i++){
            $temp = [];
            foreach ($data[$i] as $k => $value){
                if(in_array($k, $fields)){
                    $temp[$k] = $data[$i][$k];
                }
            }
            array_push($ret, $temp);
        }
        return $ret;
    }

    /**
     * 过滤必要字段
     * @param $data
     * @return array
     */
    private function filterField($data){
        $ret = [];
        $errors = [];
        if(isset($data['source_user_id']) && !$data['source_user_id']){
            $errors['source_user_id'] = '发送用户不能为空';
        }
        if(isset($data['target_user_id']) && !$data['target_user_id']){
            $errors['target_user_id'] = '接收用户不能为空';
        }
        if(isset($data['type']) && !$data['type']){
            $errors['type'] = '消息类型不能为空';
        }
        if(isset($data['content']) && !$data['content']){
            $errors['content'] = '内容不能为空';
        }

        return $errors;
    }
}
?>