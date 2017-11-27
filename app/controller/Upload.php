<?php
/**
 * 多媒体--控制器
 * author：yzs
 * create：2017.8.15
 */
namespace app\controller;

class Upload extends Common{
    /**
     * 上传多媒体
     */
    public function upload(){
        D('Upload')->upload();
    }
}
?>