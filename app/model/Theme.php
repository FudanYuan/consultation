<?php
/**
 * 主题模型
 * Author yzs
 * Create 2017.8.16
 */
namespace app\model;

use think\Model;


class Theme extends Model
{
    protected $table = 'vox_theme_3';
    protected $pk = 'id';
    protected $fields = array(
        'id', 't2_id', 'name', 'status', 'createtime', 'updatetime'
    );
    protected $type = [
        'id' => 'integer',
        't2_id' => 'integer',
        'status' => 'integer',
        'createtime' => 'integer',
        'updatetime' => 'integer'
    ];

    /**
     * 1级主题列表
     * @param $cond_or
     * @param $cond_and
     * @param $order
     * @return mixed
     */
    public function getT1List($cond_or, $cond_and, $order)
    {
        if (!isset($cond['status'])) {
            $cond['status'] = ['<>', 2];
        }
        $res = DB('theme_1')->field('id as t1_id,name as t1_name')
            ->whereOr($cond_or)
            ->where($cond_and)
            ->order($order)
            ->select();
        return $res;
    }

    public function getT1ById($id)
    {
        $res = DB('theme_1')->field('id as t1_id,name as t1_name')
            ->where(['id' => $id])
            ->find();
        return $res;
    }

    public function getT1ByName($name)
    {
        $res = DB('theme_1')->field('id as t1_id,name as t1_name')
            ->where(['name' => $name])
            ->find();
        return $res;
    }

    /**
     * 获取1级主题数量
     */
    public function getT1Number()
    {
        $res = Db('theme_1')->field('count(id) as t1_count')
            ->where('status <> 2')
            ->select();
        return $res[0]['t1_count'];
    }

    /**
     * 2级主题列表
     * @param $cond_or
     * @param $cond_and
     * @param $order
     * @return mixed
     */
    public function getT2List($cond_or, $cond_and, $order)
    {
        if (!isset($cond_and['a.status'])) {
            $cond_and['a.status'] = ['<>', 2];
            $cond_and['b.status'] = ['<>', 2];
        }
        $res = DB('theme_2')->alias('a')->field('a.id as t2_id,a.name as t2_name,b.name as t1_name')
            ->join('theme_1 b', 'b.id=a.t1_id')
            ->whereor($cond_or)
            ->where($cond_and)
            ->order($order)
            ->select();
        return $res;
    }

    public function getT2ById($id)
    {
        $res = DB('theme_2')->alias('a')->field('a.id as t2_id,a.name as t2_name,b.name as t1_name')
            ->join('theme_1 b', 'b.id=a.t1_id')
            ->where(['a.id' => $id])
            ->find();
        return $res;
    }

    public function getT2ByName($name)
    {
        $res = DB('theme_2')->alias('a')->field('a.id as t2_id,a.name as t2_name,b.name as t1_name')
            ->join('theme_1 b', 'b.id=a.t1_id')
            ->where(['a.name' => $name])
            ->find();
        return $res;
    }

    /**
     * 获取2级主题数量
     */
    public function getT2Number()
    {
        $res = Db('theme_2')->field('count(id) as t2_count')
            ->where('status <> 2')
            ->select();
        return $res[0]['t2_count'];
    }

    /**
     *  3级主题列表
     * @param $cond_or
     * @param $cond_and
     * @param $order
     * @param $pag
     * @return mixed
     */
    public function getT3List($cond_or, $cond_and, $order, $pag = 10)
    {
        if (!isset($cond_and['a.status'])) {
            $cond_and['a.status'] = ['<>', 2];
            $cond_and['b.status'] = ['<>', 2];
            $cond_and['c.status'] = ['<>', 2];
        }
        if ($pag == -1) {
            $pag = $this->getT3Number();
        }
        $res = $this->alias('a')->field(
            'a.id as t3_id,a.name as t3_name,a.t2_id as t2_id,
             b.name as t2_name,b.t1_id as t1_id,c.name as t1_name')
            ->join('vox_theme_2 b', 'a.t2_id=b.id')
            ->join('vox_theme_1 c', 'b.t1_id=c.id')
            ->whereOr($cond_or)
            ->where($cond_and)
            ->order($order)
            ->paginate($pag);
        return $res;
    }

    /**
     *  3级主题列表
     * @param $cond_or
     * @param $cond_and
     * @param $order
     * @return mixed
     */
    public function getT3List_Export($cond_or = [], $cond_and = [], $order = [])
    {
        if (!isset($cond_and['a.status'])) {
            $cond_and['a.status'] = ['<>', 2];
            $cond_and['b.status'] = ['<>', 2];
            $cond_and['c.status'] = ['<>', 2];
        }
        $res = $this->alias('a')->field(
            'a.id as t3_id,a.name as t3_name,a.t2_id as t2_id,
             b.name as t2_name,b.t1_id as t1_id,c.name as t1_name')
            ->join('vox_theme_2 b', 'a.t2_id=b.id')
            ->join('vox_theme_1 c', 'b.t1_id=c.id')
            ->whereOr($cond_or)
            ->where($cond_and)
            ->order($order)
            ->select();
        return $res;
    }

    /**
     * 查找3级主题byid
     * @param $id
     * @return mixed
     */
    public function getT3ById($id)
    {
        $res = $this->alias('a')->field('a.id as t3_id,a.name as t3_name,a.t2_id as t2_id,
            b.name as t2_name,b.t1_id as t1_id,c.name as t1_name')
            ->join('vox_theme_2 b', 'a.t2_id=b.id')
            ->join('vox_theme_1 c', 'b.t1_id=c.id')
            ->where(['a.id' => $id])
            ->find();
        return $res;
    }

    /**
     * 查找3级主题byname
     * @param $name
     * @return mixed
     */
    public function getT3ByName($name)
    {
        $res = $this->alias('a')->field('a.id as t3_id,a.name as t3_name,a.t2_id as t2_id,
            b.name as t2_name,b.t1_id as t1_id,c.name as t1_name')
            ->join('vox_theme_2 b', 'a.t2_id=b.id')
            ->join('vox_theme_1 c', 'b.t1_id=c.id')
            ->where(['a.name' => $name])
            ->find();
        return $res;
    }

    /**
     * 查找3级主题byt2_id
     * @param $id
     * @return mixed
     */
    public function getT3ByT2id($id)
    {
        $res = $this->alias('a')->field('a.id as t3_id,a.name as t3_name,a.t2_id as t2_id,
            b.name as t2_name')
            ->join('vox_theme_2 b', 'a.t2_id=b.id')
            ->where(['b.id' => $id])
            ->select();
        return $res;
    }

    /**
     * 3级主题列表---data专用
     * @param array $cond
     * @return mixed
     */
    public function getT3List_data($cond = [])
    {
        $res = $this->field('id,name')
            ->where('status <> 2')
            ->select();
        return $res;
    }

    /**
     *   获取3级主题数量
     */
    public function getT3Number()
    {
        $res = $this->field('count(id) as t3_count')
            ->where('status <> 2')
            ->select();
        return $res[0]['t3_count'];
    }

    /**
     * 主题气泡图
     * @param array $data
     * @return mixed
     */
    public function getBubbleList($data = [])
    {
        $cond_and = [];
        $cond_or = [];
        if (!isset($cond_and['a.status'])) {
            $cond_and['a.status'] = ['<>', 2];
        }
        ///数量限制///
        if (empty($data['limit']) || (isset($data['limit']) && !$data['limit']) || $data['limit'] == -1) {
            $limit = $this->getT3Number();
        } else {
            $limit = $data['limit'];
        }
        ///起止时间限制///
        if (empty($data['begintime_str']) || (isset($data['begintime_str']) && !$data['begintime_str'])) {
            $begin_time = 0;
        } else {
            $begin_time = strtotime($data['begintime_str']);
        }
        if (empty($data['endtime_str']) || (isset($data['endtime_str']) && !$data['endtime_str'])) {
            $end_time = time();
        } else {
            $end_time = strtotime($data['endtime_str']);
        }
        $cond = "$begin_time < a.createtime and a.createtime < $end_time";
        $res = $this->alias('a')->field(
            'a.id as t3_id,a.name as t3_name,a.t2_id,b.name as t2_name,b.t1_id,
             c.name as t1_name')
            ->join('vox_theme_2 b', 'a.t2_id=b.id')
            ->join('vox_theme_1 c', 'b.t1_id=c.id')
            ->whereor($cond_or)
            ->where($cond_and)
            ->where($cond)
            ->limit($limit)
            ->select();
        return $res;
    }

    /**
     * 获取本月主题增加数量
     */
    public function getPercentNumber()
    {
        $totalNum = $this->field('count(id) as t_num')
            ->where('status <> 2')
            ->select();
        $lastWeekUpdateNum = $this->field('count(id) as lw_num')
            ->wheretime('createtime', 'last week')
            ->where('status <> 2')
            ->select();
        $thisWeekUpdateNum = $this->field('count(id) as tw_num')
            ->wheretime('createtime', 'week')
            ->where('status <> 2')
            ->select();
        $thisYearUpdateNum = $this->field('count(id) as ty_num')
            ->where('status <> 2')
            ->wheretime('createtime', 'year')
            ->select();
        $thisMonthUpdateNum = $this->field('count(id) as tm_num')
            ->where('status <> 2')
            ->wheretime('createtime', 'month')
            ->select();
        $percent = $thisMonthUpdateNum[0]['tm_num'];
        return $percent;
    }

    /**
     * 添加三级主题
     * @param $data
     * @return array
     */
    public function addTheme_3($data)
    {
        $ret = [];
        $curTime = time();
        $data['createtime'] = $curTime;
        $errors = $this->filterField_3($data, false);
        $ret['errors'] = $errors;
        if (empty($errors)) {
            unset($data['t1_id']);
            if (!isset($data['status']))
                $data['status'] = 1;
            $this->save($data);
        }
        return $ret;
    }

    /**
     *  添加2级主题
     * @param $data
     * @return array
     */
    public function addTheme_2($data)
    {
        $ret = [];
        $curTime = time();
        $data['createtime'] = $curTime;
        $errors = $this->filterField_2($data, false);
        $ret['errors'] = $errors;
        if (empty($errors)) {
            if (!isset($data['status']))
                $data['status'] = 1;
            $this->save_2($data);
        }
        return $ret;
    }

    /**
     * 添加1级主题
     * @param $data
     * @return array
     */
    public function addTheme_1($data)
    {
        $ret = [];
        $curTime = time();
        $data['createtime'] = $curTime;
        $errors = $this->filterField_1($data, false);
        $ret['errors'] = $errors;
        if (empty($errors)) {
            if (!isset($data['status']))
                $data['status'] = 1;
            $this->save_1($data);
        }
        return $ret;
    }

    ///更新主题信息///
    /**
     * 更新3级主题信息
     * {@inheritDoc}
     * @see \think\Model::save()
     */
    public function saveTheme_3($id, $data)
    {
        $ret = [];
        $curTime = time();
        $data['updatetime'] = $curTime;
        $errors = $this->filterField_3($data, true);
        $ret['errors'] = $errors;
        if (empty($errors)) {
            unset($data['t1_id']);
            $this->save($data, ['id' => $id]);
        }
        return $ret;
    }

    /**
     * 更新2级主题信息
     * @param $id
     * @param $data
     * @return array
     */
    public function saveTheme_2($id, $data)
    {
        $ret = [];
        $curTime = time();
        $data['updatetime'] = $curTime;
        $errors = $this->filterField_2($data, true);
        $ret['errors'] = $errors;
        if (empty($errors)) {
            $this->update_2($id, $data);
        }
        return $ret;
    }

    /**
     * 更新1级主题信息
     * @param $id
     * @param $data
     * @return array
     */
    public function saveTheme_1($id, $data)
    {
        $ret = [];
        $curTime = time();
        $data['updatetime'] = $curTime;
        $errors = $this->filterField_1($data, true);
        $ret['errors'] = $errors;
        if (empty($errors)) {
            $this->update_1($id, $data);
        }
        return $ret;
    }

    ///过滤///
    /**
     * 过滤3级主题信息
     * @param $data
     * @param $isUpdate
     * @return array
     */
    private function filterField_3($data, $isUpdate)
    {
        $errors = [];
        if (isset($data['t2_id']) && $data['t2_id'] == '-1') {
            $errors['t2_id'] = '2级主题不能为空';
        } else if (isset($data['t2_id']) && $data['t2_id']) {
            $list = $this->getT2ById($data['t2_id']);
            if (empty($list)) {
                $errors['t1_id'] = '1级主题不存在';
            }
        }
        if (isset($data['name']) && !$data['name']) {
            $errors['name'] = '主题名不能为空';
        } else if (isset($data['name']) && $data['name']) {
            if (!$isUpdate) {
                $cond_and = [];
                $cond_and['status'] = ['<>', 2];
                $cond_and['name'] = ['=', $data['name']];
                $list = $this->field('*')
                    ->where($cond_and)
                    ->find();
                if (!empty($list)) {
                    $errors['name'] = '3级主题名字不能重复';
                }
            }
        }
        return $errors;
    }

    /**
     * 过滤2级主题信息
     * @param $data
     * @param $isUpdate
     * @return array
     */
    private function filterField_2($data, $isUpdate)
    {
        $errors = [];
        if (isset($data['name']) && !$data['name']) {
            $errors['name'] = '2级主题名不能为空';
        } else if (isset($data['name']) && $data['name']) {
            if (!$isUpdate) {
                $cond_and = [];
                $cond_and['status'] = ['<>', 2];
                $cond_and['name'] = ['=', $data['name']];
                $list = Db('theme_2')->field('*')
                    ->where($cond_and)
                    ->find();
                if (!empty($list)) {
                    $errors['name'] = '2级主题名字不能重复';
                }
            }
        }
        if (isset($data['t1_id']) && $data['t1_id'] == '-1') {
            $errors['t1_id'] = '1级主题不能为空';
        } else if (isset($data['t1_id']) && $data['t1_id']) {
            $list = $this->getT1ById($data['t1_id']);
            if (empty($list)) {
                $errors['t1_id'] = '1级主题不存在';
            }
        }
        return $errors;
    }

    /**
     * 过滤1级主题信息
     * @param $data
     * @param $isUpdate
     * @return array
     */
    private function filterField_1($data, $isUpdate)
    {
        $errors = [];
        if (isset($data['name']) && !$data['name']) {
            $errors['name'] = '1级主题名不能为空';
        } else if (isset($data['name']) && $data['name']) {
            if (!$isUpdate) {
                $cond_and = [];
                $cond_and['status'] = ['<>', 2];
                $cond_and['name'] = ['=', $data['name']];
                $list = Db('theme_1')->field('*')
                    ->where($cond_and)
                    ->find();
                if (!empty($list)) {
                    $errors['name'] = '1级主题名字不能重复';
                }
            }
        }
        return $errors;
    }

    ///添加操作///

    /**
     * 添加二级主题操作
     * @param $data
     * @return int|string
     */
    private function save_2($data)
    {
        $insert_data = ['t1_id' => $data['t1_id'], 'name' => $data['name'], 'status' => $data['status']
            , 'createtime' => $data['createtime']];
        $res = Db('theme_2')->insertGetId($insert_data);
        return $res;
    }

    /**
     * 添加一级主题操作
     * @param $data
     * @return int|string
     */
    private function save_1($data)
    {
        $insert_data = ['name' => $data['name'], 'status' => $data['status']
            , 'createtime' => $data['createtime']];
        $res = Db('theme_1')->insertGetId($insert_data);
        return $res;
    }

    /**
     * 更新2级主题信息
     * @param $data
     * @param $where
     */
    public function update_2($where, $data)
    {
        $update_data = ['t1_id' => $data['t1_id'], 'name' => $data['name'], 'status' => $data['status']
            , 'updatetime' => $data['updatetime']];
        $res = Db('theme_2')
            ->where("id = '$where'")
            ->update($update_data);
        return res;
    }

    /**
     * 更新1级主题信息
     * @param $data
     * @param $where
     */
    public function update_1($where, $data)
    {
        $update_data = ['name' => $data['name'], 'status' => $data['status']
            , 'updatetime' => $data['updatetime']];
        $res = Db('theme_1')
            ->where("id = '$where'")
            ->update($update_data);
        return res;
    }

    /**
     * 删除操作
     * @param array $cond
     * @return false|int
     * @throws MyException
     */
    public function remove($cond = [])
    {
        $res = $this->save(['status' => 2], $cond);
        if ($res === false) throw new MyException('2', '删除失败');
        return $res;
    }

    /**
     * 导入数据处理
     * @param $data
     * @return mixed
     */
    public function import_theme($data)
    {
        $res['errors'] = [];
        $list_t3 = $this->getT1ByName($data['t3_name']);
        mydump($list_t3);
        if (!empty($list_t3)) {
            if ($list_t3['t1_name'] == $data['t1_name'] && $list_t3['t2_name'] == $data['t2_name'] && $list_t3['t3_name'] == $data['t3_name']) {
                $res['errors'] = "数据已存在";
                return $res;
            }
        }
        $data_t1 = [];
        $data_t1['name'] = $data['t1_name'];
        $this->addTheme_1($data_t1);
        $data_t2 = [];
        $list_t1 = $this->getT1ByName($data['t1_name']);
        $data_t2['t1_id'] = $list_t1['t1_id'];
        $data_t2['name'] = $data['t2_name'];
        $this->addTheme_2($data_t2);
        $data_t3 = [];
        $list_t2 = $this->getT2ByName($data['t2_name']);
        $data_t3['t1_id'] = $list_t1['t1_id'];
        $data_t3['t2_id'] = $list_t2['t2_id'];
        $data_t3['name'] = $data['t3_name'];
        $this->addTheme_3($data_t3);
        return $res;
    }

    /**
     * 删除非表字段
     * @param $data
     */
    private function filterKeys(&$data)
    {
        $keys = array_keys($data);
        if (isset($data['id'])) {
            unset($data['id']);
        }
        foreach ($keys as $key) {
            $isin = in_array($key, $this->fields, true);
            if (!$isin) {
                unset($data[$key]);
            }
        }
    }
}

?>