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

		if ($this->user_info['permission']['visit_question'] AND $this->user_info['permission']['visit_site'])
		{
			$rule_action['actions'][] = 'square';
			$rule_action['actions'][] = 'index';
		}

		return $rule_action;
	}

	public function index_action()
	{
			
		if ($_GET['notification_id'])
		{
			$this->model('notify')->read_notification($_GET['notification_id'], $this->user_id);
		}

		if (is_mobile())
		{
			HTTP::redirect('/m/sortout/' . $_GET['id']);
		}

		if (! $article_info = $this->model('sortout')->get_sortout_info_by_id($_GET['id']))
		{
			HTTP::error_404();
		}

		if ($article_info['has_attach'])
		{
			$article_info['attachs'] = $this->model('publish')->get_attach('sortout', $article_info['id'], 'min');

			$article_info['attachs_ids'] = FORMAT::parse_attachs($article_info['message'], true);
		}

		$article_info['user_info'] = $this->model('account')->get_user_info_by_uid($article_info['uid'], true);

		$article_info['message'] = FORMAT::parse_attachs(nl2br(FORMAT::parse_bbcode($article_info['message'])));

		if ($this->user_id)
		{
			$article_info['vote_info'] = $this->model('sortout')->get_sortout_vote_by_id('sortout', $article_info['id'], null, $this->user_id);
		}

		$article_info['vote_users'] = $this->model('sortout')->get_sortout_vote_users_by_id('sortout', $article_info['id'], 1, 10);

		
		TPL::assign('article_info', $article_info);
        $question_info = $this->model('question')->get_question_info_by_id($article_info['question_id']);
		TPL::assign('question_info', $question_info);
        $question_topics = $this->model('topic')->get_topics_by_item_id($question_info['question_id'], 'question');

        TPL::assign('question_topics', $question_topics);

        //$answer_list = $this->model('answer')->fetch_all('answer', 'shang_cash > 0 AND question_id = ' . $question_info['question_id']);
        $question_shang = $this->model('people')->fetch_row('question_shang', 'id = ' . intval($article_info['shang_id']));
        $shang_list = $this->model('people')->fetch_all('shang', 'cash > 0 AND item_type = \'answer\' and from_uid = ' . intval($article_info['uid']) . ' AND from_question_shang = ' . intval($question_shang['id']));
        //var_dump($question_shang['id']);
        // exit(1);
        foreach ($shang_list as $key => $val){
            $answer_uids[$val['uid']] = $val['uid'];
        }
        $answer_users = $this->model('account')->get_user_info_by_uids($answer_uids, true);
        foreach ($answer_users as $key => $val){
            $answer_users[$key]['user_follow_check'] = $this->model('follow')->user_follow_check($this->user_id, $val['uid']);
        }
        TPL::assign('answer_users', $answer_users);
        TPL::assign('question_related_list', $this->model('question')->get_related_question_list($question_info['question_id'], $question_info['question_content']));
        //其他整理
        $other_sortout = $this->model('question')->fetch_all('sortout', 'id != ' . $article_info['id'], null, 5);
        foreach ($other_sortout as $key => $val){
            $sortout_uids[$val['uid']] = $val['uid'];
        }
        $sortout_users = $this->model('account')->get_user_info_by_uids($answer_uids, true);
        foreach ($other_sortout as $key => $val){
            $other_sortout[$key]['user_info'] = $sortout_users[$val['uid']];
        }
        TPL::assign('other_sortout', $other_sortout);


        TPL::assign('article_info', $article_info);

		TPL::assign('category_information', $this->model('system')->get_category_info($article_info['category_id']));

		$article_topics = $this->model('topic')->get_topics_by_item_id($article_info['id'], 'article');

		if ($article_topics)
		{
			TPL::assign('article_topics', $article_topics);

			foreach ($article_topics AS $topic_info)
			{
				$article_topic_ids[] = $topic_info['topic_id'];
			}
		}

		TPL::assign('reputation_topics', $this->model('people')->get_user_reputation_topic($article_info['user_info']['uid'], $user['reputation'], 5));

		$this->crumb($article_info['title'], '/sortout/' . $article_info['id']);

		TPL::assign('human_valid', human_valid('answer_valid_hour'));

		if ($_GET['item_id'])
		{
			$comments[] = $this->model('sortout')->get_comment_by_id($_GET['item_id']);
		}
		else
		{
			$comments = $this->model('sortout')->get_comments($article_info['id'], $_GET['page'], 100);
		}
		//echo '5';
		//exit;
		if ($comments AND $this->user_id)
		{
			foreach ($comments AS $key => $val)
			{
				$comments[$key]['vote_info'] = $this->model('sortout')->get_sortout_vote_by_id('comment', $val['id'], 1, $this->user_id);
				$comments[$key]['message'] = $this->model('question')->parse_at_user($val['message']);

			}
		}

		if ($this->user_id)
		{
			TPL::assign('user_follow_check', $this->model('follow')->user_follow_check($this->user_id, $article_info['uid']));
		}

		TPL::assign('question_related_list', $this->model('question')->get_related_question_list(null, $article_info['title']));

		$this->model('sortout')->update_views($article_info['id']);

		TPL::assign('comments', $comments);
		TPL::assign('comments_count', $article_info['comments']);

		TPL::assign('human_valid', human_valid('answer_valid_hour'));

		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/sortout/id-' . $article_info['id']),
			'total_rows' => $article_info['comments'],
			'per_page' => 100
		))->create_links());

		TPL::set_meta('keywords', implode(',', $this->model('system')->analysis_keyword($article_info['title'])));

		TPL::set_meta('description', $article_info['title'] . ' - ' . cjk_substr(str_replace("\r\n", ' ', strip_tags($article_info['message'])), 0, 128, 'UTF-8', '...'));

		TPL::assign('attach_access_key', md5($this->user_id . time()));

		$recommend_posts = $this->model('posts')->get_recommend_posts_by_topic_ids($article_topic_ids);

		if ($recommend_posts)
		{
			foreach ($recommend_posts as $key => $value)
			{
				if ($value['id'] AND $value['id'] == $article_info['id'])
				{
					unset($recommend_posts[$key]);

					break;
				}
			}

			TPL::assign('recommend_posts', $recommend_posts);
		}
		
		if ($this->user_id)
		{
			TPL::assign('user_collected_check', $this->model('favorite')->user_collected_check($this->user_id, $article_info['id']));
		}
		TPL::output('sortout/index');
	}

	public function index_square_action()
	{
		if (is_mobile())
		{
			HTTP::redirect('/m/sortout/');
		}

		$this->crumb(AWS_APP::lang()->_t('文章'), '/sortout/');

		if ($_GET['category'])
		{
			if (is_digits($_GET['category']))
			{
				$category_info = $this->model('system')->get_category_info($_GET['category']);
			}
			else
			{
				$category_info = $this->model('system')->get_category_info_by_url_token($_GET['category']);
			}
		}

		if ($_GET['feature_id'])
		{
			$article_list = $this->model('sortout')->get_sortouts_list_by_topic_ids($_GET['page'], get_setting('contents_per_page'), 'add_time DESC', $this->model('feature')->get_topics_by_feature_id($_GET['feature_id']));

			$article_list_total = $this->model('sortout')->article_list_total;

			if ($feature_info = $this->model('feature')->get_feature_by_id($_GET['feature_id']))
			{
				$this->crumb($feature_info['title'], '/sortout/feature_id-' . $feature_info['id']);

				TPL::assign('feature_info', $feature_info);
			}
		}
		else if($_GET['shang']){
			$article_list=$this->model('sortout')->get_sortouts_list_with_shang($_GET['page'], get_setting('contents_per_page'), 'add_time DESC');
			$article_list_total = $this->model('sortout')->found_rows();
		}else
		{
			$article_list = $this->model('sortout')->get_sortouts_list($category_info['id'], $_GET['page'], get_setting('contents_per_page'), 'add_time DESC');
			$article_list_total = $this->model('sortout')->found_rows();
		}



		if ($article_list)
		{
			foreach ($article_list AS $key => $val)
			{
				$article_ids[] = $val['id'];

				$article_uids[$val['uid']] = $val['uid'];
			}

			$article_topics = $this->model('topic')->get_topics_by_item_ids($article_ids, 'sortout');
			$article_users_info = $this->model('account')->get_user_info_by_uids($article_uids);

			foreach ($article_list AS $key => $val)
			{
				$article_list[$key]['user_info'] = $article_users_info[$val['uid']];
			}
		}

		// 导航
		if (TPL::is_output('block/content_nav_menu.tpl.htm', 'sortout/square'))
		{
			TPL::assign('content_nav_menu', $this->model('menu')->get_nav_menu_list('sortout'));
		}

		//边栏热门话题
		if (TPL::is_output('block/sidebar_hot_topics.tpl.htm', 'sortout/square'))
		{
			TPL::assign('sidebar_hot_topics', $this->model('module')->sidebar_hot_topics($category_info['id']));
		}

		if ($category_info)
		{
			TPL::assign('category_info', $category_info);

			$this->crumb($category_info['title'], '/sortout/category-' . $category_info['id']);

			$meta_description = $category_info['title'];

			if ($category_info['description'])
			{
				$meta_description .= ' - ' . $category_info['description'];
			}

			TPL::set_meta('description', $meta_description);
		}

		TPL::assign('article_list', $article_list);
		TPL::assign('article_topics', $article_topics);

		TPL::assign('hot_articles', $this->model('sortout')->get_sortouts_list_with_shang(1, 10, 'shang_cash DESC', 30));

		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/sortout/category_id-' . $_GET['category_id'] . '__feature_id-' . $_GET['feature_id']),
			'total_rows' => $article_list_total,
			'per_page' => get_setting('contents_per_page')
		))->create_links());

        //热门文章
        //TPL::assign('hot_articles', $this->model('article')->get_articles_list(null, 1, 5, 'comments DESC'));

		TPL::output('sortout/square');
	}
}
