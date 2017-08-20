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

class home_class extends AWS_MODEL
{
	public function update_home_info($category_id, $title, $parent_id, $url_token)
	{
		return $this->update('home', array(
			'title' => htmlspecialchars($title),
			'parent_id' => intval($parent_id),
			'url_token' => $url_token
		), 'id = ' . intval($category_id));
	}

	public function set_home_sort($category_id, $sort)
	{
		return $this->update('home', array(
			'sort' => intval($sort)
		), 'id = ' . intval($category_id));
	}

	public function add_home($type, $title, $parent_id)
	{
		return $this->insert('home', array(
			'type' => $type,
			'title' => $title,
			'parent_id' => intval($parent_id),
		));
	}

	public function delete_home($type, $category_id)
	{
		return $this->delete('home', 'id = ' . intval($category_id));
	}

	public function get_home_exits($title)
	{
		if( $this->fetch_row('home', 'title = "'.$title.'"') ){
			return true;
		}
		
	}

	public function get_home_list($page, $per_page, $order_by)
	{
		$where = array();

		return $this->fetch_page('home', implode(' AND ', $where), $order_by, $page, $per_page);
	}

	public function contents_exists($category_id)
	{
		if ($this->fetch_one('question', 'question_id', 'category_id = ' . intval($category_id)) OR $this->fetch_one('article', 'id', 'category_id = ' . intval($category_id)))
		{
			return true;
		}
	}

	public function check_url_token($url_token, $category_id)
	{
		return $this->count('home', "url_token = '" . $this->quote($url_token) . "' AND id != " . intval($category_id));
	}

	public function move_contents($from_id, $target_id)
	{
		if (!$from_id OR !$target_id)
		{
			return false;
		}

		$this->update('question', array(
			'category_id' => intval($target_id)
		), 'category_id = ' . intval($from_id));

		$this->update('article', array(
			'category_id' => intval($target_id)
		), 'category_id = ' . intval($from_id));

		$this->update('posts_index', array(
			'category_id' => intval($target_id)
		), 'category_id = ' . intval($from_id));
	}


	public function get_single_info($id,$type = '',$t = '')
	{

		if($type == 1){
			//问答
			$info = $this->fetch_row('question', 'question_id = '.$id);
		}

		if($type == 2){
			//整理
			$info = $this->fetch_row('sortout', 'id = '.$id);
		}

		if($type == 3){
			//文章
			$info = $this->fetch_row('article', 'id = '.$id);
		}

		if($type == 4){
			//有奖
			$info = $this->fetch_row('prize', 'id = '.$id);
		}
		return $info;
	}
}
