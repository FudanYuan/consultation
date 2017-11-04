<?php
/**
 * 通用集成模块
 * yzs
 * 2017.8.15
 */
namespace app\controller;

use think\Request;
use think\Controller;

class Common{
    const USER_TOKEN = 'admin_user_token';
    const TOKEN_USER = 'admin_token_user';

    public function __construct(){
        $request = Request::instance();
        $filters = config('filters');
        if(in_array($request->controller().'/'.$request->action(), $filters)){
            cache_del(CACHE_NAME);
        }else{
            $this->initUser();
        }
    }

    public function __destruct()
    {
        cache_del(CACHE_NAME);
    }

    /**
     * 初始化用户
     */
    private function initUser(){
        if(($token = session('token')) || ($token = input('request.token'))){
            $user = D('UserAdmin')->getUserByToken($token);
            !empty($user) && config('user', $user);
        }else{
            $this->myRedirect('UserAdmin/login');
        }
    }
    protected function jsonReturn($data){
        header('Content-type: application/json');
        echo json_encode($data);exit;
    }
    protected function exceptionReturn($data){
        header('Content-type: application/json');
        echo json_encode($data);exit;
    }
    private function generateToken(){
        $request = Request::instance();
        return md5(time().$request->ip().'-'.rand(0,100));
    }
    private function myRedirect($uri, $params = []){
        if(!empty($params)){
            $params = http_build_query($params);
            $uri .= '?'.$params;
        }
        header('Location:/' . $uri);exit;
    }

    /**
     * 获取通知
     */
    private function updateNotice(){

    }

    /**
     * 获取用户登录IP地址
     * @return string
     */
    public function getUserIp(){
        $request = Request::instance();
        return $request->ip();
    }

    public function getUserId(){
        $token = session('token');
        $token_user = json_decode(cache_hash_hget(self::TOKEN_USER, $token), true);
        return $token_user['id'];
    }
}
?>