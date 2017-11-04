<?php
/**
 * 标签模型
 * Author yzs
 * Create 2017.8.15
 */
namespace app\model;

use think\Model;

class Tag extends Model{
    protected $table = 'vox_tag';
    protected $pk = 'id';
    protected $fields = array(
        'id', 'title','section','status','createtime','updatetime'
    );
    protected $type = [
        'id' => 'integer',
        'status' => 'integer'
    ];
    /**
     * 获取企业标签数量
     * @return mixed
     */
    public function getWebTypeNumber(){
        $res = $this->field('count(id) as tag_num')
            ->where("status <> '2'")
            ->select();
        return $res[0]['url'];
    }


    /**
     * 获取企业标签列表
     * @param array $cond
     * @return mixed
     */
    public function getWebTypeList($cond = []){
        $res = $this->field('id as tagid, title as title ')
            ->where("status <> '2'")
            ->select();
        return $res;
    }

    /**
     * 获取标签列表
     * @param array $cond
     */
    public function getList($cond = []){
        if(!isset($cond['status'])){
            $cond['status'] = ['<>', 2];
        }
        $res = $this->field('id,title,section')->order('id')->where($cond)->paginate(10);
        return $res;
    }

    /**
     * 根据ID获取标签信息
     * @param $id
     * @return mixed
     */
    public function getById($id){
        $res = $this->field('id,title,section')->where(['id' => $id])->find();
        return $res;
    }

    /**
     * 更新标签
     * {@inheritDoc}
     * @see \think\Model::save()
     */
    public function saveData($id, $data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)){
            $data['updatetime'] = time();
            $this->save($data, ['id' => $id]);
        }
        return $ret;
    }

    /**
     * 添加标签
     * @param $data
     * @return array
     */
    public function addData($data){
        $ret = [];
        $errors = $this->filterField($data);
        $ret['errors'] = $errors;
        if(empty($errors)){
            $data['createtime'] = time();
            if(!isset($data['status']))
                $data['status'] = 1;
            $this->save($data);
        }
        return $ret;
    }

    /**
     * 删除标签
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
     *
     */
    public function getCompanyTag($id){
        $cond = [];
        switch ($id){
            case 0:
                $cond['C_500']=['<>',0];
                break;
            case 1:
                $cond['W_500']=['<>',0];
                break;
            case 2:
                $cond['CL_500']=['<>',0];
                break;
            case 3:
                $cond['T_100']=['<>',0];
                break;
            case 4:
                $cond['NEEQ_rank']=['<>',0];
                break;
        }
        return $cond;
    }

    /**
     * 获取板块列表
     */
    public function getSections(){
        return [
            1 => ['title' => '企业监测'],
            2 => ['title' => '主题监测'],
            3 => ['title' => '网站监测'],
            4 => ['title' => '采集项目'],
            5 => ['title' => '抓取设置'],
            6 => ['title' => '企业库'],
            7 => ['title' => '主题库'],
            8 => ['title' => '网站库'],
            9 => ['title' => '抓取设置'],
            10 => ['title' => '数据清洗'],
            11 => ['title' => '更新备份'],
            12 => ['title' => '标签设置'],
            13 => ['title' => '权限设置'],
            14 => ['title' => '角色设置']
        ];
    }

    /**
     * 过滤必要字段
     * @param $data
     * @return array
     */
    private function filterField($data){
        $ret = [];
        $errors = [];
        if(isset($data['title']) && !$data['title']){
            $errors['title'] = '标题不能为空';
        }
        return $errors;
    }
}
?>