<?php 
/**
 * 首页--控制器
 * author：yzs
 * create：2017.8.15
 */
namespace app\controller;

use app\model\Data;
use think\Db;

class Index extends Common{
	/**
	 * 首页
	 * @return \think\response\View
	 */
	public function index(){
        //$data = input('get.')
        $data['limit'] = -1;
        $count = [];
        $count['theme'] = formatNum(D('Theme')->getT3Number());
        $count['data'] = formatNum(D('DataMonitor')->getDataNumber());
        $count['url'] = formatNum(D('Media')->getMedNumber());
        $count['task'] = formatNum(D('Task')->getTaskNumber());

        $compare = [];
        $compare['data'] = formatNum(D('DataMonitor')->getPercentNumber());
        $compare['url'] = formatNum(D('Media')->getPercentNumber());
        $compare['theme'] = formatNum(D('Theme')->getPercentNumber());

        $task = [];
        $task['completed'] = formatNum(D('Task')->getCompletedNum());
        $task['todeal'] = formatNum(D('Task')->getTodealNum());
        $task['time_consume'] = 5;
        $task['percent'] = formatNum(D('Task')->getPercentCompleted());
        $list = D('Theme')->getbubbleList($data);

        $news[] = ['title' => '★★从一无所有到年入百万，讲讲我的真实经历和感悟—', 'url' => 'https://tieba.baidu.com/p/5113168953', 'origin' => '农业吧', 'time' => 1508222979];
        $news[] = ['title' => '【经验篇】此篇献给那些还在奋斗之路迷茫的朋友。', 'url' => 'https://tieba.baidu.com/p/5365072533',  'origin' => '农业吧', 'time' => 1508222979];
        $news[] = ['title' => '光伏农业大棚补贴政策', 'url' => 'http://weibo.com/u/5245496522?refer_flag=1001030103_', 'origin' => '微博', 'time' => 1508222989];
        $news[] = ['title' => '【今日，你愿为农民转发吗？】', 'url' => 'http://weibo.com/hnrbwb?refer_flag=1005055014_&is_hot=1#_rnd1508225911906', 'origin' => '河南日报', 'time' => 1508222999];
        $news[] = ['title' => '愿意转发，但是不愿意做农民[可爱]，因为农民就是被欺骗和盘剥的阶层', 'url' => 'http://weibo.com/hnrbwb?refer_flag=1005055014_&is_hot=1#_loginLayer_1508226061289', 'origin' => '独思独行', 'time' => 1508222779];
        $news[] = ['title' => '政策加速落实 农业供给侧改革概念股迎风口（附股）', 'url' => 'https://finance.sina.cn/2017-10-12/detail-ifymviyp0358665.d.html', 'origin' => '大众证券报', 'time' => 1508222579];
        $news[] = ['title' => '助推脱贫攻坚，贵州为农业植入大数据“基因”', 'url' => 'http://news.163.com/17/1015/14/D0PVM8QQ00018AOQ.html', 'origin' => '新华社新媒体专线(广州)', 'time' => 1508222279];
        $news[] = ['title' => '农业部信息中心与奥科美强强联合 农业大数据助力产业扶贫', 'url' => 'http://city.sina.com.cn/invest/t/2017-09-19/16366942.html', 'origin' => '中青在线', 'time' => 1508222179];
        return view('', ['count' => $count, 'task' => $task, 'theme_company'=>$list, 'news' => $news]);

	}
	/**
	 * 清除缓存
	 */
	public function clearcache(){
		$ret = ['errorcode' => 0, 'msg' => '成功'];
		cache_del(CACHE_NAME);
		$this->jsonReturn($ret);
	}

    /**
     * 获取定时任务---命令（shell定时执行）
     */
    public function regularcommand(){
        $flag = false;
        //定时发布
        $publish = D('Sys')->getPublish();
        mydump($publish);
        if(!empty($publish)){
            foreach($publish as $v){
                $v = json_decode($v, true);
                $sec = $this->getSection($v['section']);
                D($sec)->saveData($v['conid'], ['ispublish' => 1]);
            }
            $flag = true;
        }
        //定时推荐开始
        $recomm = D('Sys')->getRecommend();
        mydump($recomm);
        if(!empty($recomm)){
            foreach($recomm as $v){
                $v = json_decode($v, true);
                $sec = $this->getSection($v['section']);
                D($sec)->saveData($v['conid'], ['recommendtime' => $v['time']]);
            }
            $flag = true;
        }
        //定时推荐结束
        $recommEnd = D('Sys')->getRecommendEnd();
        mydump($recommEnd);
        if(!empty($recommEnd)){
            foreach($recommEnd as $v){
                $v = json_decode($v, true);
                $sec = $this->getSection($v['section']);
                D($sec)->saveData($v['conid'], ['isrecommend' => 0]);
            }
            $flag = true;
        }
        //定时置顶
        $top = D('Sys')->getTop();
        mydump($top);
        if(!empty($top)){
            foreach($top as $v){
                $v = json_decode($v, true);
                D('Banner')->saveData($v['t3_id'], ['status' => 1]);
            }
            $flag = true;
        }
        //定时置顶结束
        $topEnd = D('Sys')->getTopEnd();
        mydump($topEnd);
        if(!empty($topEnd)){
            foreach($topEnd as $v){
                $v = json_decode($v, true);
                D('Banner')->remove(['t3_id' => $v['t3_id']]);
            }
            $flag = true;
        }
        //清空缓存
        if($flag) cache_del('web_index');
    }

    private function getSection($secid){
		switch($secid){
            case 1: //研究方向
                $sec = 'ResearchArea';
                break;
            case 2: //科研成果
                $sec = 'Result';
                break;
            case 3: //团队成员
                $sec = 'Member';
                break;
            case 4: //最新动态
                $sec = 'News';
                break;
		}
		return $sec;
	}
}
?>