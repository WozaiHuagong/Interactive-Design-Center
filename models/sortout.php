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

class sortout_class extends AWS_MODEL
{
	public function get_sortout_info_by_id($sortout_id)
	{
		if (!is_digits($sortout_id))
		{
			return false;
		}

		static $sortouts;

		if (!$sortouts[$sortout_id])
		{
			$sortouts[$sortout_id] = $this->fetch_row('sortout', 'id = ' . $sortout_id);
		}

		return $sortouts[$sortout_id];
	}

	public function get_sortout_info_by_ids($sortout_ids)
	{
		if (!is_array($sortout_ids) OR sizeof($sortout_ids) == 0)
		{
			return false;
		}

		array_walk_recursive($sortout_ids, 'intval_string');

		if ($sortouts_list = $this->fetch_all('sortout', 'id IN(' . implode(',', $sortout_ids) . ')'))
		{
			foreach ($sortouts_list AS $key => $val)
			{
				$result[$val['id']] = $val;
			}
		}

		return $result;
	}

	public function get_comment_by_id($comment_id)
	{
		if ($comment = $this->fetch_row('sortout_comments', 'id = ' . intval($comment_id)))
		{
			$comment_user_infos = $this->model('account')->get_user_info_by_uids(array(
				$comment['uid'],
				$comment['at_uid']
			));

			$comment['user_info'] = $comment_user_infos[$comment['uid']];
			$comment['at_user_info'] = $comment_user_infos[$comment['at_uid']];
		}

		return $comment;
	}

	public function get_comments_by_ids($comment_ids)
	{
		if (!is_array($comment_ids) OR !$comment_ids)
		{
			return false;
		}

		array_walk_recursive($comment_ids, 'intval_string');

		if ($comments = $this->fetch_all('sortout_comments', 'id IN (' . implode(',', $comment_ids) . ')'))
		{
			foreach ($comments AS $key => $val)
			{
				$sortout_comments[$val['id']] = $val;
			}
		}

		return $sortout_comments;
	}

	public function get_comments($sortout_id, $page, $per_page)
	{
		if ($comments = $this->fetch_page('sortout_comments', 'article_id = ' . intval($sortout_id), 'add_time ASC', $page, $per_page))
		{
			foreach ($comments AS $key => $val)
			{
				$comment_uids[$val['uid']] = $val['uid'];

				if ($val['at_uid'])
				{
					$comment_uids[$val['at_uid']] = $val['at_uid'];
				}
			}

			if ($comment_uids)
			{
				$comment_user_infos = $this->model('account')->get_user_info_by_uids($comment_uids);
			}

			foreach ($comments AS $key => $val)
			{
				$comments[$key]['user_info'] = $comment_user_infos[$val['uid']];
				$comments[$key]['at_user_info'] = $comment_user_infos[$val['at_uid']];
			}
		}

		return $comments;
	}

	public function remove_sortout($sortout_id)
	{
		if (!$sortout_info = $this->get_sortout_info_by_id($sortout_id))
		{
			return false;
		}

		$this->delete('sortout_comments', "article_id = " . intval($sortout_id)); // 删除关联的回复内容

		$this->delete('topic_relation', "`type` = 'sortout' AND item_id = " . intval($sortout_id));		// 删除话题关联

		ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_QUESTION . ' AND associate_action IN(' . ACTION_LOG::ADD_ARTICLE . ', ' . ACTION_LOG::ADD_AGREE_ARTICLE . ', ' . ACTION_LOG::ADD_COMMENT_ARTICLE . ') AND associate_id = ' . intval($sortout_id));	// 删除动作

		// 删除附件
		if ($attachs = $this->model('publish')->get_attach('sortout', $sortout_id))
		{
			foreach ($attachs as $key => $val)
			{
				$this->model('publish')->remove_attach($val['id'], $val['access_key']);
			}
		}

		$this->model('notify')->delete_notify('model_type = 8 AND source_id = ' . intval($sortout_id));	// 删除相关的通知

		$this->model('posts')->remove_posts_index($sortout_id, 'sortout');

		$this->shutdown_update('users', array(
			'sortout_count' => $this->count('sortout', 'uid = ' . intval($uid))
		), 'uid = ' . intval($uid));

		return $this->delete('sortout', 'id = ' . intval($sortout_id));
	}

	public function remove_comment($comment_id)
	{
		$comment_info = $this->get_comment_by_id($comment_id);

		if (!$comment_info)
		{
			return false;
		}

		$this->delete('sortout_comments', 'id = ' . $comment_info['id']);

		$this->update('sortout', array(
			'comments' => $this->count('sortout_comments', 'article_id = ' . $comment_info['sortout_id'])
		), 'id = ' . $comment_info['sortout_id']);

		return true;
	}

	public function update_sortout($sortout_id, $uid, $title, $message, $topics, $category_id, $create_topic, $award_price, $is_original, $is_award, $summary,$other_category)
	{
		if (!$sortout_info = $this->model('sortout')->get_sortout_info_by_id($sortout_id))
		{
			return false;
		}

		$this->delete('topic_relation', 'item_id = ' . intval($sortout_id) . " AND `type` = 'sortout'");

		if (is_array($topics))
		{
			foreach ($topics as $key => $topic_title)
			{
				$topic_id = $this->model('topic')->save_topic($topic_title, $uid, $create_topic);

				$this->model('topic')->save_topic_relation($uid, $topic_id, $sortout_id, 'sortout');
			}
		}

		$this->model('search_fulltext')->push_index('sortout', htmlspecialchars($title), $sortout_info['id']);
		if($category_id == 0 && !empty($other_category)){
			 $category_id = $this->model('category')->add_category('question', $other_category, 0);
		}
		$this->update('sortout', array(
			'title'        => htmlspecialchars($title),
			'message'      => htmlspecialchars($message),
			'category_id'  => intval($category_id),
			'award_price' => htmlspecialchars($award_price),
			'is_award'    => intval($is_award),
			'is_original'  => intval($is_original),
			'summary' => htmlspecialchars($summary)
		), 'id = ' . intval($sortout_id));
		//award
		$this->model('posts')->set_posts_index($sortout_id, 'sortout');

		return true;
	}

	public function get_sortouts_list($category_id, $page, $per_page, $order_by, $day = null)
	{
		$where = array();

		if ($category_id)
		{
			$where[] = 'category_id = ' . intval($category_id);
		}

		if ($day)
		{
			$where[] = 'add_time > ' . (time() - $day * 24 * 60 * 60);
		}

		return $this->fetch_page('sortout', implode(' AND ', $where), $order_by, $page, $per_page);
	}

	public function get_sortouts_list_by_uid($category_id, $page, $per_page, $order_by, $day = null, $uid = null)
	{
		$where = array();

		if ($category_id)
		{
			$where[] = 'category_id = ' . intval($category_id);
		}

		if ($uid)
		{
			$where[] = 'uid = ' . intval($uid);
		}

		if ($day)
		{
			$where[] = 'add_time > ' . (time() - $day * 24 * 60 * 60);
		}

		return $this->fetch_page('sortout', implode(' AND ', $where), $order_by, $page, $per_page);
	}

	public function get_sortouts_list_by_topic_ids($page, $per_page, $order_by, $topic_ids)
	{
		if (!$topic_ids)
		{
			return false;
		}

		if (!is_array($topic_ids))
		{
			$topic_ids = array(
				$topic_ids
			);
		}

		array_walk_recursive($topic_ids, 'intval_string');

		$result_cache_key = 'sortout_list_by_topic_ids_' . md5(implode('_', $topic_ids) . $order_by . $page . $per_page);

		$found_rows_cache_key = 'sortout_list_by_topic_ids_found_rows_' . md5(implode('_', $topic_ids) . $order_by . $page . $per_page);

		if (!$result = AWS_APP::cache()->get($result_cache_key) OR $found_rows = AWS_APP::cache()->get($found_rows_cache_key))
		{
			$topic_relation_where[] = '`topic_id` IN(' . implode(',', $topic_ids) . ')';
			$topic_relation_where[] = "`type` = 'sortout'";

			if ($topic_relation_query = $this->query_all("SELECT item_id FROM " . get_table('topic_relation') . " WHERE " . implode(' AND ', $topic_relation_where)))
			{
				foreach ($topic_relation_query AS $key => $val)
				{
					$sortout_ids[$val['item_id']] = $val['item_id'];
				}
			}

			if (!$sortout_ids)
			{
				return false;
			}

			$where[] = "id IN (" . implode(',', $sortout_ids) . ")";
		}


		if (!$result)
		{
			$result = $this->fetch_page('sortout', implode(' AND ', $where), $order_by, $page, $per_page);

			AWS_APP::cache()->set($result_cache_key, $result, get_setting('cache_level_high'));
		}


		if (!$found_rows)
		{
			$found_rows = $this->found_rows();

			AWS_APP::cache()->set($found_rows_cache_key, $found_rows, get_setting('cache_level_high'));
		}

		$this->sortout_list_total = $found_rows;

		return $result;
	}

	public function get_sortouts_list_with_shang($page, $per_page, $order_by, $day = null)
	{
		$where = array();
		$where[] = 'shang_cash > 0';

		if ($day)
		{
			$where[] = 'add_time > ' . (time() - $day * 24 * 60 * 60);
		}

		return $this->fetch_page('sortout', implode(' AND ', $where), $order_by, $page, $per_page);
	}
	public function get_shang_time_with_sortout_id($sortout_id){
		$sql='SELECT COUNT(*) as n FROM '.$this->get_table('question_shang').' WHERE sortout_id = '.intval($sortout_id).' GROUP BY sortout_id';
		return $this->query_row($sql)['n'];
	}

	public function lock_sortout($sortout_id, $lock_status = true)
	{
		return $this->update('sortout', array(
			'lock' => intval($lock_status)
		), 'id = ' . intval($sortout_id));
	}

	public function sortout_vote($type, $item_id, $rating, $uid, $reputation_factor, $item_uid)
	{
		$this->delete('sortout_vote', "`type` = '" . $this->quote($type) . "' AND item_id = " . intval($item_id) . ' AND uid = ' . intval($uid));

		if ($rating)
		{
			if ($sortout_vote = $this->fetch_row('sortout_vote', "`type` = '" . $this->quote($type) . "' AND item_id = " . intval($item_id) . " AND rating = " . intval($rating) . ' AND uid = ' . intval($uid)))
			{
				$this->update('sortout_vote', array(
					'rating' => intval($rating),
					'time' => time(),
					'reputation_factor' => $reputation_factor
				), 'id = ' . intval($sortout_vote['id']));
			}
			else
			{
				$this->insert('sortout_vote', array(
					'type' => $type,
					'item_id' => intval($item_id),
					'rating' => intval($rating),
					'time' => time(),
					'uid' => intval($uid),
					'item_uid' => intval($item_uid),
					'reputation_factor' => $reputation_factor
				));
			}
		}

		switch ($type)
		{
			case 'sortout':
				$this->update('sortout', array(
					'votes' => $this->count('sortout_vote', "`type` = '" . $this->quote($type) . "' AND item_id = " . intval($item_id) . " AND rating = 1")
				), 'id = ' . intval($item_id));

				switch ($rating)
				{
					case 1:
						ACTION_LOG::save_action($uid, $item_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::ADD_AGREE_ARTICLE);
					break;

					case -1:
						ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_QUESTION . ' AND associate_action = ' . ACTION_LOG::ADD_AGREE_ARTICLE . ' AND uid = ' . intval($uid) . ' AND associate_id = ' . intval($item_id));
					break;
				}
			break;

			case 'comment':
				$this->update('sortout_comments', array(
					'votes' => $this->count('sortout_vote', "`type` = '" . $this->quote($type) . "' AND item_id = " . intval($item_id) . " AND rating = 1")
				), 'id = ' . intval($item_id));
			break;
		}

		$this->model('account')->sum_user_agree_count($item_uid);

		return true;
	}

	public function get_sortout_vote_by_id($type, $item_id, $rating = null, $uid = null)
	{
		if ($sortout_vote = $this->get_sortout_vote_by_ids($type, array(
			$item_id
		), $rating, $uid))
		{
			return end($sortout_vote[$item_id]);
		}
	}

	public function get_sortout_vote_by_ids($type, $item_ids, $rating = null, $uid = null)
	{
		if (!is_array($item_ids))
		{
			return false;
		}

		if (sizeof($item_ids) == 0)
		{
			return false;
		}

		array_walk_recursive($item_ids, 'intval_string');

		$where[] = "`type` = '" . $this->quote($type) . "'";
		$where[] = 'item_id IN(' . implode(',', $item_ids) . ')';

		if ($rating)
		{
			$where[] = 'rating = ' . intval($rating);
		}

		if ($uid)
		{
			$where[] = 'uid = ' . intval($uid);
		}

		if ($sortout_votes = $this->fetch_all('sortout_vote', implode(' AND ', $where)))
		{
			foreach ($sortout_votes AS $key => $val)
			{
				$result[$val['item_id']][] = $val;
			}
		}

		return $result;
	}

	public function get_sortout_vote_users_by_id($type, $item_id, $rating = null, $limit = null)
	{
		$where[] = "`type` = '" . $this->quote($type) . "'";
		$where[] = 'item_id = ' . intval($item_id);

		if ($rating)
		{
			$where[] = 'rating = ' . intval($rating);
		}

		if ($sortout_votes = $this->fetch_all('sortout_vote', implode(' AND ', $where)))
		{
			foreach ($sortout_votes AS $key => $val)
			{
				$uids[$val['uid']] = $val['uid'];
			}

			return $this->model('account')->get_user_info_by_uids($uids);
		}
	}

	public function get_sortout_vote_users_by_ids($type, $item_ids, $rating = null, $limit = null)
	{
		if (! is_array($item_ids))
		{
			return false;
		}

		if (sizeof($item_ids) == 0)
		{
			return false;
		}

		array_walk_recursive($item_ids, 'intval_string');

		$where[] = "`type` = '" . $this->quote($type) . "'";
		$where[] = 'item_id IN(' . implode(',', $item_ids) . ')';

		if ($rating)
		{
			$where[] = 'rating = ' . intval($rating);
		}

		if ($sortout_votes = $this->fetch_all('sortout_vote', implode(' AND ', $where)))
		{
			foreach ($sortout_votes AS $key => $val)
			{
				$uids[$val['uid']] = $val['uid'];
			}

			$users_info = $this->model('account')->get_user_info_by_uids($uids);

			foreach ($sortout_votes AS $key => $val)
			{
				$vote_users[$val['item_id']][$val['uid']] = $users_info[$val['uid']];
			}

			return $vote_users;
		}
	}

	public function update_views($sortout_id)
	{
		if (AWS_APP::cache()->get('update_views_sortout_' . md5(session_id()) . '_' . intval($sortout_id)))
		{
			return false;
		}

		AWS_APP::cache()->set('update_views_sortout_' . md5(session_id()) . '_' . intval($sortout_id), time(), 60);

		$this->shutdown_query("UPDATE " . $this->get_table('sortout') . " SET views = views + 1 WHERE id = " . intval($sortout_id));

		return true;
	}

	public function set_recommend($sortout_id)
	{
		$this->update('sortout', array(
			'is_recommend' => 1
		), 'id = ' . intval($sortout_id));

		$this->model('posts')->set_posts_index($sortout_id, 'sortout');
	}

	public function unset_recommend($sortout_id)
	{
		$this->update('sortout', array(
			'is_recommend' => 0
		), 'id = ' . intval($sortout_id));

		$this->model('posts')->set_posts_index($sortout_id, 'sortout');
	}
}