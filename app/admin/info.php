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

if (! defined('IN_ANWSION'))
{
	die();
}

class info extends AWS_ADMIN_CONTROLLER
{
	public function setup()
	{

	}

	public function list_action()
	{
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(850));
		if ($this->is_post())
		{
			foreach ($_POST as $key => $val)
			{
				if ($key == 'start_date' OR $key == 'end_date')
				{
					$val = base64_encode($val);
				}

				if ($key == 'keyword' OR $key == 'user_name')
				{
					$val = rawurlencode($val);
				}

				$param[] = $key . '-' . $val;
			}

			H::ajax_json_output(AWS_APP::RSM(array(
				'url' => get_js_url('/admin/info/list/' . implode('__', $param))
			), 1, null));
		}


		$where = array();

		if ($_GET['keyword'])
		{
			$where[] = "(`title` LIKE '%" . $this->model('ad')->quote($_GET['keyword']) . "%')";
		}

		if ($_GET['start_date'])
		{
			$where[] = 'add_time >= ' . strtotime(base64_decode($_GET['start_date']));
		}

		if ($_GET['end_date'])
		{
			$where[] = 'add_time <= ' . strtotime('+1 day', strtotime(base64_decode($_GET['end_date'])));
		}

		if ($_GET['user_name'])
		{
			$user_info = $this->model('account')->get_user_info_by_username($_GET['user_name']);

			$where[] = 'uid = ' . intval($user_info['uid']);
		}

		if ($_GET['comment_count_min'])
		{
			$where[] = 'comments >= ' . intval($_GET['comment_count_min']);
		}

		if ($_GET['answer_count_max'])
		{
			$where[] = 'comments <= ' . intval($_GET['comment_count_max']);
		}

		if ($ad_list = $this->model('ad')->fetch_page('ad', implode(' AND ', $where), 'status ASC, id DESC', $_GET['page'], $this->per_page))
		{
			$search_articles_total = $this->model('ad')->found_rows();
		}

		if ($ad_list)
		{
			foreach ($ad_list AS $key => $val)
			{
				$articles_list_uids[$val['uid']] = $val['uid'];
			}

			if ($articles_list_uids)
			{
				$articles_list_user_infos = $this->model('account')->get_user_info_by_uids($articles_list_uids);
			}

			foreach ($ad_list AS $key => $val)
			{
                $ad_list[$key]['user_info'] = $articles_list_user_infos[$val['uid']];
			}
		}

		$url_param = array();

		foreach($_GET as $key => $val)
		{
			if (!in_array($key, array('app', 'c', 'act', 'page')))
			{
				$url_param[] = $key . '-' . $val;
			}
		}

		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/admin/info/list/') . implode('__', $url_param),
			'total_rows' => $search_articles_total,
			'per_page' => $this->per_page
		))->create_links());

		$this->crumb(AWS_APP::lang()->_t('信息发布管理'), 'admin/info/list/');

		TPL::assign('ad_count', $search_articles_total);
		TPL::assign('list', $ad_list);

		TPL::output('admin/info/list');
	}

	public function comments_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(851));

        $where = $url_param = [];
        if($_GET['ad_id']){
            $where[] = 'ad_id = ' . intval($_GET['ad_id']);
            $url_param[] = 'ad_id-' . intval($_GET['ad_id']);
        }
        $list = $this->model('ad')->fetch_page('ad_comments', implode(' AND ', $where), 'id DESC', $_GET['page'], $this->per_page);

        if ($list)
        {
            foreach ($list AS $key => $val)
            {
                $articles_list_uids[$val['uid']] = $val['uid'];
            }

            if ($articles_list_uids)
            {
                $articles_list_user_infos = $this->model('account')->get_user_info_by_uids($articles_list_uids);
            }

            foreach ($list AS $key => $val)
            {
                $list[$key]['user_info'] = $articles_list_user_infos[$val['uid']];
            }
        }


        $search_articles_total = $this->model('ad')->found_rows();

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/info/comments/') . implode('__', $url_param),
            'total_rows' => $search_articles_total,
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('信息发布评论管理'), 'admin/info/comments/');

        TPL::assign('comments_count', $search_articles_total);
        TPL::assign('list', $list);

        TPL::output('admin/info/comments');
    }

    public function ajax_approval_action(){
        if(!$ad = $this->model('ad')->fetch_row('ad', 'id = ' . intval($_POST['id']))){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('广告不存在')));
        }

        if($ad['status'] == 0){
            $status = 1;
        }else{
            $status = 0;
        }

        $this->model('ad')->update('ad', [
            'status' => $status
        ], 'id = ' . intval($_POST['id']));

        H::ajax_json_output(AWS_APP::RSM(null, '1', null));
    }

    public function ad_manage_action()
    {
        if (!$_POST['ad_ids'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请选择广告进行操作')));
        }

        switch ($_POST['action'])
        {
            case 'del':
                foreach ($_POST['ad_ids'] AS $article_id)
                {
                    $this->model('ad')->remove_ad($article_id);
                }

                H::ajax_json_output(AWS_APP::RSM(null, 1, null));
                break;
        }
    }

    public function comments_manage_action()
    {
        if (!$_POST['comment_ids'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请选择广告评论进行操作')));
        }

        switch ($_POST['action'])
        {
            case 'del':
                foreach ($_POST['comment_ids'] AS $article_id)
                {
                    $this->model('ad')->remove_comment($article_id);
                }

                H::ajax_json_output(AWS_APP::RSM(null, 1, null));
                break;
        }
    }

    public function charge_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(852));
        $list = $this->model('finance')->fetch_page('charge', "status = 'ok'", 'pay_time desc', $_GET['page'], $this->per_page);
        if ($list)
        {
            foreach ($list AS $key => $val)
            {
                $articles_list_uids[$val['uid']] = $val['uid'];
            }

            if ($articles_list_uids)
            {
                $articles_list_user_infos = $this->model('account')->get_user_info_by_uids($articles_list_uids);
            }

            foreach ($list AS $key => $val)
            {
                $list[$key]['user_info'] = $articles_list_user_infos[$val['uid']];
            }
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/info/charge/'),
            'total_rows' => $this->model('finance')->found_rows(),
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('充值管理'), 'admin/info/charge/');

        TPL::assign('list', $list);

        TPL::output('admin/info/charge');
    }

    public function setting_action(){
        $this->crumb(AWS_APP::lang()->_t('财务设置'), 'admin/info/setting/');

        if (!$this->user_info['permission']['is_administortar'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你没有访问权限, 请重新登录'), '/');
        }

        TPL::assign('setting', get_setting(null, false));

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(853));

        TPL::output('admin/info/setting');
    }

    public function withdraw_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(854));
        $list = $this->model('finance')->fetch_page('withdraw', "", 'id desc', $_GET['page'], $this->per_page);
        if ($list)
        {
            foreach ($list AS $key => $val)
            {
                $articles_list_uids[$val['uid']] = $val['uid'];
            }

            if ($articles_list_uids)
            {
                $articles_list_user_infos = $this->model('account')->get_user_info_by_uids($articles_list_uids);
            }

            foreach ($list AS $key => $val)
            {
                $list[$key]['user_info'] = $articles_list_user_infos[$val['uid']];
            }
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/info/withdraw/'),
            'total_rows' => $this->model('finance')->found_rows(),
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('提现管理'), 'admin/info/withdraw/');

        TPL::assign('list', $list);

        TPL::output('admin/info/withdraw');
    }

    public function withdraw_info_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(854));

        $withdraw = $this->model('finance')->fetch_row('withdraw', 'id = ' . intval($_GET['id']));

        if(!$withdraw){
            H::redirect_msg(AWS_APP::lang()->_t('出错啦'), '/admin/info/withdraw/');
        }

        $withdraw['user_info'] = $this->model('account')->get_user_info_by_uid($withdraw['uid']);

        TPL::assign('withdraw', $withdraw);

        TPL::output('admin/info/withdraw_info');
    }

    public function save_withdraw_action(){
        if (!$_POST['id'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('非法提交')));
        }

        if($_POST['status'] == 'ok' && !$_POST['terrace_id']){
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('支付宝流水号不能为空,否则到时候不能查账')));
        }

        $withdraw = $this->model('finance')->fetch_row('withdraw', 'id = ' . intval($_POST['id']));
        if (!$withdraw)
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('数据不存在')));
        }

        $this->model('finance')->set_ok_withdraw($withdraw['id'], $_POST['status'], $_POST['terrace_id']);

        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }

    public function flow_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(855));
        if ($this->is_post()) {
            foreach ($_POST as $key => $val) {
                if ($key == 'start_date' OR $key == 'end_date') {
                    $val = base64_encode($val);
                }
                if ( $key == 'uid' OR $key == 'from_uid') {
                    $val = rawurlencode($val);
                }
                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/info/flow/' . implode('__', $param))
            ), 1, null));
        }

        $where = [];
        if($_GET['uid']){
            if(is_digits($_GET['uid'])){
                $where[] = 'uid = ' . intval($_GET['uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['from_uid']){
            if(is_digits($_GET['from_uid'])){
                $where[] = 'from_uid = ' . intval($_GET['from_uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['from_uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'from_uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['start_date']){
            $where[] = 'add_time >= ' . strtotime(base64_decode($_GET['start_date']));
        }
        if($_GET['end_date']){
            $where[] = 'add_time < ' . (strtotime(base64_decode($_GET['end_date'])) + 86400);
        }
        if($_GET['type']){
            $where[] = 'type = ' . intval($_GET['type']);
        }

        $list = $this->model('ad')->fetch_page('finance', implode(' AND ', $where), 'id desc', $_GET['page'], $this->per_page);

        if($list){
            foreach ($list as $key => $val){
                $uids[$val['uid']] = $val['uid'];
                $uids[$val['from_uid']] = $val['from_uid'];
            }
            $users = $this->model('account')->get_user_info_by_uids($uids);
            foreach ($list as $key => $val){
                $list[$key]['user_info'] = $users[$val['uid']];
                $list[$key]['from_user'] = $users[$val['from_uid']];
                $item = $this->model('finance')->getItemType($val['item_type'], $val['item_id']);
                $list[$key]['title'] = $item['name'];
                $list[$key]['info']  = $item['info'];
                $list[$key]['link']  = $item['link'];
            }
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/info/flow/'),
            'total_rows' => $this->model('ad')->found_rows(),
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('交易明细'), 'admin/info/flow/');

        TPL::assign('list', $list);

        TPL::output('admin/info/flow');
    }

    public function ad_flow_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(856));
        $list = $this->model('finance')->get_ad_flow_list($_GET['page'], $this->per_page);

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/info/ad_flow/'),
            'total_rows' => $this->model('finance')->found_rows(),
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('广告上下架明细'), 'admin/info/ad_flow/');

        TPL::assign('list', $list);

        TPL::output('admin/info/ad_flow');
    }
    public function ad_click_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(857));

        if ($this->is_post())
        {
            foreach ($_POST as $key => $val)
            {
                if ($key == 'start_date' OR $key == 'end_date')
                {
                    $val = base64_encode($val);
                }

                if($key == 'location'){
                    $val = json_encode($val);
                }

                if ($key == 'ad_id' OR $key == 'uid' OR $key == 'show_uid' OR $key == 'click_uid')
                {
                    $val = rawurlencode($val);
                }

                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/info/ad_click/' . implode('__', $param))
            ), 1, null));
        }

        $where = [];
        if($_GET['ad_id']){
            if(is_digits($_GET['ad_id'])){
                $where[] = 'ad_id = ' . intval($_GET['ad_id']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('ad', 'title like \'%'. $this->model('ad')->quote($_GET['ad_id']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['id']] = $val['id'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'ad_id IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['uid']){
            if(is_digits($_GET['uid'])){
                $where[] = 'ad_uid = ' . intval($_GET['uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'ad_uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['show_uid']){
            if(is_digits($_GET['show_uid'])){
                $where[] = 'show_uid = ' . intval($_GET['show_uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['show_uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'show_uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['click_uid']){
            if(is_digits($_GET['click_uid'])){
                $where[] = 'uid = ' . intval($_GET['click_uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['click_uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['start_date']){
            $where[] = 'add_time >= ' . strtotime(base64_decode($_GET['start_date']));
        }

        if($_GET['end_date']){
            $where[] = 'add_time < ' . (strtotime(base64_decode($_GET['end_date'])) + 86400);
        }
        if($_GET['location']){
            $where[] = 'show_location IN(' . implode(',', json_decode($_GET['location'])) . ')';
        }

        $list = $this->model('finance')->get_ad_click_list($_GET['page'], $this->per_page, implode(' AND ', $where));

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/info/ad_click/'),
            'total_rows' => $this->model('finance')->found_rows(),
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('广告点击明细'), 'admin/info/ad_click/');

        TPL::assign('list', $list);
        TPL::assign('ad_count', $this->model('finance')->found_rows());
        TPL::assign('total_cash', $this->model('finance')->sum('click_log', 'cash', implode(' AND ', $where)));

        TPL::output('admin/info/ad_click');
    }
}