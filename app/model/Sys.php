<?php 
/**
 * 系统模型
 * Author yzs
 * Create 2017.8.15
 */
namespace app\model;

class Sys{
	const REGULAR_PUBLISH = 'regular_publish';
    const REGULAR_RECOMMEND = 'regular_recommend';
    const REGULAR_RECOMMEND_END = 'regular_recommend_end';
    const REGULAR_TOP = 'regular_top';
	const REGULAR_TOP_END = 'regular_top_end';
	/**
	 * 定时发布
	 * @param unknown $conid 内容
	 * @param unknown $section 模块
	 * @param unknown $time 发布时间
	 */
	public function regularPublish($conid, $section, $time){
		cache_zAdd(self::REGULAR_PUBLISH, $time, json_encode(['conid' => $conid, 'section' => $section, 'time' => $time]));
	}
	/**
	 * 获取当前定时发布并清空
	 */
	public function getPublish(){
		$time = time();
		$res = cache_zRangeByScore(self::REGULAR_PUBLISH, 0, $time);
		if(!empty($res)){
			cache_zRemRangeByScore(self::REGULAR_PUBLISH, 0, $time);
		}
		return $res;
	}
	/**
	 * 清空定时发布
	 */
	public function clearPublish(){
		cache_del(self::REGULAR_PUBLISH);
	}
    /**
     * 定时推荐
     * @param unknown $conid 内容
     * @param unknown $section 模块
     * @param unknown $time 发布时间
     */
    public function regularRecommend($conid, $section, $time){
        cache_zAdd(self::REGULAR_RECOMMEND, $time, json_encode(['conid' => $conid, 'section' => $section, 'time' => $time]));
    }
    /**
     * 获取当前定时推荐并清空
     */
    public function getRecommend(){
        $time = time();
        $res = cache_zRangeByScore(self::REGULAR_RECOMMEND, 0, $time);
        if(!empty($res)){
            cache_zRemRangeByScore(self::REGULAR_RECOMMEND, 0, $time);
        }
        return $res;
    }
    /**
     * 清空定时推荐
     */
    public function clearRecommend(){
        cache_del(self::REGULAR_RECOMMEND);
    }
    /**
     * 定时推荐结束
     * @param $conid 内容
     * @param $section 模块
     * @param $time 发布时间
     */
    public function regularRecommendEnd($conid, $section, $time){
        cache_zAdd(self::REGULAR_RECOMMEND_END, $time, json_encode(['conid' => $conid, 'section' => $section, 'time' => $time]));
    }
    /**
     * 获取当前定时推荐结束并清空
     */
    public function getRecommendEnd(){
        $time = time();
        $res = cache_zRangeByScore(self::REGULAR_RECOMMEND_END, 0, $time);
        if(!empty($res)){
            cache_zRemRangeByScore(self::REGULAR_RECOMMEND_END, 0, $time);
        }
        return $res;
    }
    /**
     * 清空定时推荐结束
     */
    public function clearRecommendEnd(){
        cache_del(self::REGULAR_RECOMMEND_END);
    }
    /**
     * 定时置顶
     * @param $id
     * @param $time
     */
	public function regularTop($id, $time){
		cache_zAdd(self::REGULAR_TOP, $time, json_encode(['id' => $id, 'time' => $time]));
	}
	/**
	 * 获取当前定时置顶并清空
	 */
	public function getTop(){
		$time = time();
		$res = cache_zRangeByScore(self::REGULAR_TOP, 0, $time);
		if(!empty($res)){
			cache_zRemRangeByScore(self::REGULAR_TOP, 0, $time);
		}
		return $res;
	}
	/**
	 * 清空定时置顶
	 */
	public function clearTop(){
		cache_del(self::REGULAR_TOP);
	}

    /**
     * 定时置顶结束
     * @param $id
     * @param $time
     */
	public function regularTopEnd($id, $time){
		cache_zAdd(self::REGULAR_TOP_END, $time, json_encode(['id' => $id, 'time' => $time]));
	}
	/**
	 * 获取当前定时置顶结束并清空
	 */
	public function getTopEnd(){
		$time = time();
		$res = cache_zRangeByScore(self::REGULAR_TOP_END, 0, $time);
		if(!empty($res)){
			cache_zRemRangeByScore(self::REGULAR_TOP_END, 0, $time);
		}
		return $res;
	}
	/**
	 * 清空定时置顶结束
	 */
	public function clearTopEnd(){
		cache_del(self::REGULAR_TOP_END);
	}
}
?>