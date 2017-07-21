<?php

/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2017/3/27
 * Time: 下午2:35
 */
class main extends AWS_CONTROLLER
{
    public function get_access_rule()
    {
        $rule_action['rule_type'] = "black"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查

        return $rule_action;
    }

    public function setup() {}

    public function index_action()
    {
        if(!$ad = $this->model('ad')->fetch_row('ad', 'id = ' . intval($_GET['id']))){
            H::redirect_msg(AWS_APP::lang()->_t('该广告不存在'));
        }

        //广告的统计
        if($_GET['a'] && $_GET['u']){
            $show_id = AWS_APP::crypt()->decode($_GET['a']);
            $click_uid = AWS_APP::crypt()->decode($_GET['u']);
            $client_ip = ip2long(fetch_ip());
            $page_link = $_SERVER['HTTP_REFERER'];

            if(is_numeric($show_id)){
                if($show_id == -1){
                    $show_ad = [
                        'id'      => -1,
                        'ad_id'   => $ad['id'],
                        'ad_uid'  => $ad['uid'],
                        'uid'     => 0,
                        'item_id' => 0,
                        'location' => ad_class::L_AD_LIST,
                    ];
                }else{
                    $show_ad = $this->model('ad')->fetch_row('ad_show', 'id = ' . intval($show_id));
                }

                if($show_ad){
                    if(!$show_ad['uid'] OR ($show_ad['uid'] AND $show_ad['uid'] != $click_uid)){
                        if($click_uid){
                            $day_count = $this->model('ad')->count('click_log', 'uid = ' . $click_uid . ' AND ad_id = ' . $show_ad['ad_id'] . ' AND add_time >=' . strtotime(date('Y-m-d', strtotime('this day'))));
                            $week_count = $this->model('ad')->count('click_log', 'uid = ' . $click_uid . ' AND ad_id = ' . $show_ad['ad_id'] . ' AND add_time >=' . strtotime(date('Y-m-d', strtotime('this week'))));
                            if($day_count < 3 AND $week_count < 15){
                                //这里需要计数
                                $last_click = $this->model('ad')->count('click_log', 'uid = ' . $click_uid . ' AND ad_id = ' . $show_ad['ad_id']);
                                if($last_click['add_time'] < (time() - 300)){
                                    //防止误点计数
                                    $this->model('ad')->ad_click($click_uid, $show_ad, $page_link);
                                }
                            }
                        }else{
                            $day_count = $this->model('ad')->count('click_log', 'ip = ' . $client_ip . ' AND ad_id = ' . $show_ad['ad_id'] . ' AND add_time >=' . strtotime(date('Y-m-d', strtotime('this day'))));
                            $week_count = $this->model('ad')->count('click_log', 'ip = ' . $client_ip . ' AND ad_id = ' . $show_ad['ad_id'] . ' AND add_time >=' . strtotime(date('Y-m-d', strtotime('this week'))));
                            if($day_count < 3 AND $week_count < 15){
                                //这里需要计数
                                $last_click = $this->model('ad')->count('click_log', 'ip = ' . $client_ip . ' AND ad_id = ' . $show_ad['ad_id']);
                                if($last_click['add_time'] < (time() - 300)){
                                    //防止误点计数
                                    $this->model('ad')->ad_click($click_uid, $show_ad, $page_link);
                                }
                            }
                        }
                    }
                }
            }
        }

        //评论列表
        $comments = $this->model('ad')->fetch_all('ad_comments', 'ad_id = ' . $ad['id'], 'id desc');
        if($comments){
            foreach ($comments as $key => $val){
                $uids[$val['uid']] = $val['uid'];
            }
            $user_infos = $this->model('account')->get_user_info_by_uids($uids);
            foreach ($comments as $key => $val){
                $comments[$key]['user_info'] = $user_infos[$val['uid']];
            }
        }
        TPL::assign('comments', $comments);

        $this->crumb($ad['title'], '/info/' . $ad['title']);

        TPL::set_meta('keywords', implode(',', $this->model('system')->analysis_keyword($ad['title'])));

        TPL::set_meta('description', $ad['title'] . ' - ' . cjk_substr(str_replace("\r\n", ' ', strip_tags($ad['description'])), 0, 128, 'UTF-8', '...'));
        TPL::assign('ad', $ad);
        TPL::output('info/index');
    }

    public function index_square_action()
    {
        if($_GET['select'] AND !AWS_APP::session()->refer){
            if(strpos($_SERVER['HTTP_REFERER'], 'select-true') === false AND strpos($_SERVER['HTTP_REFERER'], 'info/') === false){
                AWS_APP::session()->refer = $_SERVER['HTTP_REFERER'];
            }
        }

        TPL::output('info/square');
    }
}