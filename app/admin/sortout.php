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

class sortout extends AWS_ADMIN_CONTROLLER
{
	public function setup()
	{
		TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(410));
	}

	public function list_action()
	{
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
				'url' => get_js_url('/admin/sortout/list/' . implode('__', $param))
			), 1, null));
		}


		$where = array();

		if ($_GET['keyword'])
		{
			$where[] = "(`title` LIKE '%" . $this->model('sortout')->quote($_GET['keyword']) . "%')";
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

		if ($sortouts_list = $this->model('sortout')->fetch_page('sortout', implode(' AND ', $where), 'id DESC', $_GET['page'], $this->per_page))
		{
			$search_sortouts_total = $this->model('sortout')->found_rows();
		}

		if ($sortouts_list)
		{
			foreach ($sortouts_list AS $key => $val)
			{
				$sortouts_list_uids[$val['uid']] = $val['uid'];
			}

			if ($sortouts_list_uids)
			{
				$sortouts_list_user_infos = $this->model('account')->get_user_info_by_uids($sortouts_list_uids);
			}

			foreach ($sortouts_list AS $key => $val)
			{
				$sortouts_list[$key]['user_info'] = $sortouts_list_user_infos[$val['uid']];
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
			'base_url' => get_js_url('/admin/sortout/list/') . implode('__', $url_param),
			'total_rows' => $search_sortouts_total,
			'per_page' => $this->per_page
		))->create_links());

		$this->crumb(AWS_APP::lang()->_t('文章管理'), 'admin/sortout/list/');

		TPL::assign('sortouts_count', $search_sortouts_total);
		TPL::assign('list', $sortouts_list);
		TPL::output('admin/sortout/list');
	}
}