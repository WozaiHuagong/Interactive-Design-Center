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

		$rule_action['actions'] = array();

		return $rule_action;
	}

	public function setup()
	{
		$this->crumb(AWS_APP::lang()->_t('我的评论'), '/comment/');
	}

	public function index_action()
	{

		$get_type = $_GET['t'];
		if(empty($get_type)){
			$get_type = 'article';
		}

		if ($action_list = $this->model('favorite')->get_item_comment_list($this->user_id, calc_page_limit($_GET['page'], get_setting('contents_per_page')),$get_type)){
			TPL::assign('list', $action_list);
		}
		
		TPL::output('comment/index');
	}
}