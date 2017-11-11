<?php
/**
 * JSON 模型
 * Author yzs
 * CreateTime 2017/8/16
 */
namespace app\model;

class JSON{

    /**
     * 导出json格式数据的文件
     * @param $data
     * @param string $filename
     * @param string $template
     */
    function export($data,$filename='sample.json', $template=''){

    }

    /**
     * 导入json文件
     * @param  string $params json文件路径
     * @return array
     */
    public function import($params){
        $ret = ['errors' => [], 'data' => []];
        $errors = $this->filterField($params);
        if(!empty($errors)){
            $ret['errors'] = $errors;
            return $ret;
        }
        else{
            $file = '.'.$params['file'];
        }
        // 判断文件是什么格式
        $type = pathinfo($file);
        $type = strtolower($type["extension"]);
        if($type != 'json'){
            $errors = ['file' => '文件格式不正确'];
            $ret['errors'] = $errors;
            return $ret;
        }
        // 从文件中读取数据到PHP变量
        $json_string = file_get_contents($file);
        // 把JSON字符串转成PHP数组
        $data = json_decode($json_string, true);
        $ret['data'] = $data;
        return $ret;
    }

    /**
     * 过滤导入信息
     * @param $data
     * @return array
     */
    private function filterField($data){
        $errors = [];
        if (isset($data['file']) && !$data['file']){
            $errors['file'] = '导入文件不能为空';
        }
        return $errors;
    }

}
?>