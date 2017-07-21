<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class main extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white';
		$rule_action['actions'] = array(
			'explore'
		);

		if ($this->user_info['permission']['visit_explore'] AND $this->user_info['permission']['visit_site'])
		{
			$rule_action['actions'][] = 'index';
		}

		return $rule_action;
	}

	public function setup()
	{
		if (is_mobile() AND !$_GET['ignore_ua_check'])
		{
			switch ($_GET['app'])
			{
				default:
					HTTP::redirect('/m/');
				break;
			}
		}
	}

	public function index_action()
	{		
		if (! $this->user_id)
		{
			HTTP::redirect('/explore/');
		}

		if (! $this->user_info['email'])
		{
			HTTP::redirect('/account/complete_profile/');
		}

		// 边栏可能感兴趣的人或话题
		if (TPL::is_output('block/sidebar_recommend_users_topics.tpl.htm', 'home/index'))
		{
			$recommend_users_topics = $this->model('module')->recommend_users_topics($this->user_id);

			TPL::assign('sidebar_recommend_users_topics', $recommend_users_topics);
		}

		// 边栏热门用户
		if (TPL::is_output('block/sidebar_hot_users.tpl.htm', 'home/index'))
		{
			$sidebar_hot_users = $this->model('module')->sidebar_hot_users($this->user_id);

			TPL::assign('sidebar_hot_users', $sidebar_hot_users);
		}

		$this->crumb(AWS_APP::lang()->_t('动态'), '/home/');

		TPL::import_js('js/app/index.js');

		TPL::output('home/index');
	}

	public function explore_action()
	{
		HTTP::redirect('/explore/');
	}

	public function finance_action(){
        if($_GET['type'] == 'shang'){
            $type = finance_class::TYPE_SHANG;
            $finance_crumb = '赏金明细';
        }else{
            $type = finance_class::TYPE_AD;
            $finance_crumb = '广告明细';
        }
        if($_GET['action'] == 'out'){
            $action = finance_class::ACTION_IN;
        }else{
            $action = finance_class::ACTION_OUT;
        }

        $list = $this->model('finance')->fetch_all('withdraw', "status = 'wait' AND type = " . $type, 'id desc');
        TPL::assign('list', $list);

        $this->crumb('是金包包', '/home/finance');

        TPL::assign('finance_crumb', $finance_crumb);
        TPL::output('home/finance');
    }

    public function pay_password_action(){

        $this->crumb('交易密码', '/home/pay_password');
        TPL::output('home/pay_password');
    }

    public function my_ad_action(){
        $list = $this->model('ad')->fetch_all('ad', 'uid = ' . $this->user_id,  'id desc');

        TPL::assign('list', $list);

        TPL::output('home/my_ad');

    }

    public function ad_click_action(){
        $ad = $this->model('ad')->fetch_row('ad', 'id = ' . $_GET['ad_id']);
        if(!$ad){
            H::redirect_msg(AWS_APP::lang()->_t('该广告不存在'));
        }
        if($ad['uid'] !=  $this->user_id){
            H::redirect_msg(AWS_APP::lang()->_t('该广告不存在1'));
        }

        TPL::assign('ad', $ad);
        TPL::output('home/ad_click');

    }
}