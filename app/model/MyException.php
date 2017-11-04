<?php
/**
 * 自定义异常
 * author：yzs
 * create：2017.8.15
 */
namespace app\model;
class MyException extends \Exception{
	public function formatException(){
		return ['code' => $this->getCode(), 'msg' => $this->getMessage()];
	}
}
?>