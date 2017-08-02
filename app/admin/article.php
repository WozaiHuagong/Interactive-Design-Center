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

class article extends AWS_ADMIN_CONTROLLER
{
	public function setup()
	{
		TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(309));
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
				'url' => get_js_url('/admin/article/list/' . implode('__', $param))
			), 1, null));
		}


		$where = array();

		if ($_GET['keyword'])
		{
			$where[] = "(`title` LIKE '%" . $this->model('article')->quote($_GET['keyword']) . "%')";
		}

		if ($_GET['category_id'])
		{
			$where[] = "(`category_id` = ". $_GET['category_id'] ." )";
			TPL::assign('active_tab',$this->model('topic')->get_topic_by_id($_GET['category_id']));
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

		$url_param = array();

		foreach($_GET as $key => $val)
		{
			if (!in_array($key, array('app', 'c', 'act', 'page')))
			{
				$url_param[] = $key . '-' . $val;
			}
		}


		// $industryInfo_where=$where;
		// $industryInfo_topic_id = $this->model('topic')->get_topic_by_title('行业资讯')["topic_id"];
		// $industryInfo_where[] = ' category_id = ' .$industryInfo_topic_id;

		// if ($industryInfo_list = $this->model('article')->fetch_page('article', implode(' AND ', $industryInfo_where), 'id DESC', $_GET['industryInfo_page'], $this->per_page)){
		// 	$search_industryInfo_total = $this->model('article')->found_rows();
		// }

		// $industryInfo_list = $this->articles_list_modify($industryInfo_list);

		// TPL::assign('industryInfo_pagination', AWS_APP::pagination()->initialize(array(
		// 	'base_url' => get_js_url('/admin/article/list/') . implode('__', $url_param),
		// 	'total_rows' => $search_industryInfo_total,
		// 	'per_page' => $this->per_page,
		// 	'query_string_segment' => 'industryInfo_page'
		// ))->create_links());
		$this->pagination_wrapper('行业资讯','industryInfo',$where,$url_param);
		$this->pagination_wrapper('研究中心资讯','libInfo',$where,$url_param);
		$this->pagination_wrapper('在研项目','working',$where,$url_param);
		$this->pagination_wrapper('研究成果','finished',$where,$url_param);
		

		$this->crumb(AWS_APP::lang()->_t('文章管理'), 'admin/article/list/');

		//TPL::assign('articles_count', $search_articles_total);
		TPL::assign('list', $industryInfo_list);
		TPL::assign('category_list',$this->model('topic')->get_topic_list('type = "article"'));

		TPL::output('admin/article/list');
	}

	private function articles_list_modify($articles_list){
		if ($articles_list)
		{
			foreach ($articles_list AS $key => $val)
			{
				$articles_list_uids[$val['uid']] = $val['uid'];
			}

			if ($articles_list_uids)
			{
				$articles_list_user_infos = $this->model('account')->get_user_info_by_uids($articles_list_uids);
			}

			foreach ($articles_list AS $key => $val)
			{
				$articles_list[$key]['user_info'] = $articles_list_user_infos[$val['uid']];
			}

			foreach ($articles_list AS $key => $val)
			{
				$articles_list[$key]['topic_title'] = $this->model('topic')->get_topic_by_id($val['category_id'])['topic_title'];
			}

			foreach ($articles_list AS $key => $val)
			{
				$attach = $this->model('publish')->get_attach('article',$val['id']);
	            if (!empty($attach))
	                $articles_list[$key]['img'] = current($attach)['attachment'];
			}
		}
		return $articles_list;
	}

	private function pagination_wrapper($topic_title,$prefix,$where,$url_param){
		$__where=$where;
		$__topic_id = $this->model('topic')->get_topic_by_title($topic_title)["topic_id"];
		$__where[] = ' category_id = ' .$__topic_id;

		if ($__list = $this->model('article')->fetch_page('article', implode(' AND ', $__where), 'id DESC', $_GET[$prefix.'_page'], $this->per_page)){
			$search__total = $this->model('article')->found_rows();
		}

		$__list = $this->articles_list_modify($__list);
		TPL::assign($prefix.'_pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/admin/article/list/') . implode('__', $url_param),
			'total_rows' => $search__total,
			'per_page' => $this->per_page,
			'query_string_segment' => $prefix.'_page'
		))->create_links());
		TPL::assign($prefix.'_list', $__list);
	}
}