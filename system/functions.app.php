<?php
/**
 * WeCenter Framework
 *
 * An open source application development framework for PHP 5.2.2 or newer
 *
 * @package		WeCenter Framework
 * @author		WeCenter Dev Team
 * @copyright	Copyright (c) 2011 - 2014, WeCenter, Inc.
 * @license		http://www.wecenter.com/license/
 * @link		http://www.wecenter.com/
 * @since		Version 1.0
 * @filesource
 */

/**
 * WeCenter APP 函数类
 *
 * @package		WeCenter
 * @subpackage	App
 * @category	Libraries
 * @author		WeCenter Dev Team
 */


/**
 * 获取头像地址
 *
 * 举个例子：$uid=12345，那么头像路径很可能(根据您部署的上传文件夹而定)会被存储为/uploads/000/01/23/45_avatar_min.jpg
 *
 * @param  int
 * @param  string
 * @return string
 */
function get_avatar_url($uid, $size = 'min')
{
	$uid = intval($uid);

	if (!$uid)
	{
		return G_STATIC_URL . '/common/avatar-' . $size . '-img.png';
	}

	foreach (AWS_APP::config()->get('image')->avatar_thumbnail as $key => $val)
	{
		$all_size[] = $key;
	}

	$size = in_array($size, $all_size) ? $size : $all_size[0];

	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);

	if (file_exists(get_setting('upload_dir') . '/avatar/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, - 2) . '_avatar_' . $size . '.jpg'))
	{
		return get_setting('upload_url') . '/avatar/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, - 2) . '_avatar_' . $size . '.jpg';
	}
	else
	{
		return G_STATIC_URL . '/common/avatar-' . $size . '-img.png';
	}
}

/**
 * 附件url地址，实际上是通过一定格式编码指配到/app/file/main.php中，让download控制器处理并发送下载请求
 * @param  string $file_name 附件的真实文件名，即上传之前的文件名称，包含后缀
 * @param  string $url 附件完整的真实url地址
 * @return string 附件下载的完整url地址
 */
function download_url($file_name, $url)
{
	return get_js_url('/file/download/file_name-' . base64_encode($file_name) . '__url-' . base64_encode($url));
}

// 检测当前操作是否需要验证码
function human_valid($permission_tag)
{
	if (! is_array(AWS_APP::session()->human_valid))
	{
		return FALSE;
	}

	if (! AWS_APP::session()->human_valid[$permission_tag] or ! AWS_APP::session()->permission[$permission_tag])
	{
		return FALSE;
	}

	foreach (AWS_APP::session()->human_valid[$permission_tag] as $time => $val)
	{
		if (date('H', $time) != date('H', time()))
		{
			unset(AWS_APP::session()->human_valid[$permission_tag][$time]);
		}
	}

	if (sizeof(AWS_APP::session()->human_valid[$permission_tag]) >= AWS_APP::session()->permission[$permission_tag])
	{
		return TRUE;
	}

	return FALSE;
}

function set_human_valid($permission_tag)
{
	if (! is_array(AWS_APP::session()->human_valid))
	{
		return FALSE;
	}

	AWS_APP::session()->human_valid[$permission_tag][time()] = TRUE;

	return count(AWS_APP::session()->human_valid[$permission_tag]);
}

/**
 * 仅附件处理中的preg_replace_callback()的每次搜索时的回调
 * @param  array $matches preg_replace_callback()搜索时返回给第二参数的结果
 * @return string  取出附件的加载模板字符串
 */
function parse_attachs_callback($matches)
{
	if ($attach = AWS_APP::model('publish')->get_attach_by_id($matches[1]))
	{
		TPL::assign('attach', $attach);

		return TPL::output('question/ajax/load_attach', false);
	}
}

/**
 * 获取主题图片指定尺寸的完整url地址
 * @param  string $size
 * @param  string $pic_file 某一尺寸的图片文件名
 * @return string           取出主题图片或主题默认图片的完整url地址
 */
function get_topic_pic_url($size = null, $pic_file = null)
{
	if ($sized_file = AWS_APP::model('topic')->get_sized_file($size, $pic_file))
	{
		return get_setting('upload_url') . '/topic/' . $sized_file;
	}

	if (! $size)
	{
		return G_STATIC_URL . '/common/topic-max-img.png';
	}

	return G_STATIC_URL . '/common/topic-' . $size . '-img.png';
}

/**
 * 获取专题图片指定尺寸的完整url地址
 * @param  string $size     三种图片尺寸 max(100px)|mid(50px)|min(32px)
 * @param  string $pic_file 某一尺寸的图片文件名
 * @return string           取出专题图片的完整url地址
 */
function get_feature_pic_url($size = null, $pic_file = null)
{
	if (! $pic_file)
	{
		return false;
	}
	else
	{
		if ($size)
		{
			$pic_file = str_replace(AWS_APP::config()->get('image')->feature_thumbnail['min']['w'] . '_' . AWS_APP::config()->get('image')->feature_thumbnail['min']['h'], AWS_APP::config()->get('image')->feature_thumbnail[$size]['w'] . '_' . AWS_APP::config()->get('image')->feature_thumbnail[$size]['h'], $pic_file);
		}
	}

	return get_setting('upload_url') . '/feature/' . $pic_file;
}

function get_host_top_domain()
{
	$host = strtolower($_SERVER['HTTP_HOST']);

	if (strpos($host, '/') !== false)
	{
		$parse = @parse_url($host);
		$host = $parse['host'];
	}

	$top_level_domain_db = array('com', 'edu', 'gov', 'int', 'mil', 'net', 'org', 'biz', 'info', 'pro', 'name', 'coop', 'aero', 'xxx', 'idv', 'mobi', 'cc', 'me', 'jp', 'uk', 'ws', 'eu', 'pw', 'kr', 'io', 'us', 'cn');

	foreach ($top_level_domain_db as $v)
	{
		$str .= ($str ? '|' : '') . $v;
	}

	$matchstr = "[^\.]+\.(?:(" . $str . ")|\w{2}|((" . $str . ")\.\w{2}))$";

	if (preg_match('/' . $matchstr . '/ies', $host, $matchs))
	{
		$domain = $matchs['0'];
	}
	else
	{
		$domain = $host;
	}

	return $domain;
}

function parse_link_callback($matches)
{
	if (preg_match('/^(?!http).*/i', $matches[1]))
	{
		$url = $matches[1];
		//$url = 'http://' . $matches[1];
	}
	else
	{
		$url = $matches[1];
	}

	if (is_inside_url($url))
	{
		//return '<a href="' . $url . '">' . FORMAT::sub_url($matches[1], 50) . '</a>';
		return $url;
	}
	else
	{
		return $url;
		//return '<a href="' . $url . '" rel="nofollow" target="_blank">' . FORMAT::sub_url($matches[1], 50) . '</a>';
	}
}

function is_inside_url($url)
{
	if (!$url)
	{
		return false;
	}

	if (preg_match('/^(?!http).*/i', $url))
	{
		$url = 'http://' . $url;
	}

	$domain = get_host_top_domain();

	if (preg_match('/^http[s]?:\/\/([-_a-zA-Z0-9]+[\.])*?' . $domain . '(?!\.)[-a-zA-Z0-9@:;%_\+.~#?&\/\/=]*$/i', $url))
	{
		return true;
	}

	return false;
}

function get_weixin_rule_image($image_file, $size = '')
{
	return AWS_APP::model('weixin')->get_weixin_rule_image($image_file, $size);
}

function import_editor_static_files()
{
	//TPL::import_js('js/editor/ckeditor/ckeditor.js');
	//TPL::import_js('js/editor/ckeditor/adapters/jquery.js');
	TPL::import_js('js/ckfinder/ckfinder.js');
	TPL::import_js('js/ckfinder/samples/js/ckeditor/ckeditor.js');
}

function get_chapter_icon_url($id, $size = 'max', $default = true)
{
	if (file_exists(get_setting('upload_dir') . '/chapter/' . $id . '-' . $size . '.jpg'))
	{
		return get_setting('upload_url') . '/chapter/' . $id . '-' . $size . '.jpg';
	}
	else if ($default)
	{
		return G_STATIC_URL . '/common/help-chapter-' . $size . '-img.png';
	}

	return false;
}

function base64_url_encode($parm)
{
	if (!is_array($parm))
	{
		return false;
	}

	return strtr(base64_encode(json_encode($parm)), '+/=', '-_,');
}

function base64_url_decode($parm)
{
	return json_decode(base64_decode(strtr($parm, '-_,', '+/=')), true);
}

function remove_assoc($from, $type, $id)
{
	if (!$from OR !$type OR !is_digits($id))
	{
		return false;
	}

	return $this->query('UPDATE ' . $this->get_table($from) . ' SET `' . $type . '_id` = NULL WHERE `' . $type . '_id` = ' . $id);
}

/**
 * 获取分类名称
 */
function get_cat_info($id)
{
	//$category_info = AWS_APP::model('system')->get_category_info($id);
    $category_info = AWS_APP::model('topic')->get_topic_by_id($id);
	if ($category_info)
	{
		return $category_info;
	}
}

/**
 * 获取用户信息
 */
function get_user_info($uid)
{
	$users_info = AWS_APP::model('account')->get_user_info_by_uid($uid);
	if ($users_info)
	{
		return $users_info;
	}
}

/**
 * 获取文章信息
 */
function get_article_info($id)
{
	$article_info = AWS_APP::model('article')->get_article_info_by_id($id);
	if ($article_info)
	{
		return $article_info;
	}
}

/**
 * 获取内容信息
 */
function get_content_info($id,$type)
{
	if($type == 'article'){
		$article_info = AWS_APP::model('article')->get_article_info_by_id($id);
	}

	if($type == 'sortout'){
		$article_info = AWS_APP::model('sortout')->get_sortout_info_by_id($id);
	}

	if($type == 'prize'){
		$article_info = AWS_APP::model('prize')->get_prize_info_by_id($id);
	}

	if ($article_info)
	{
		return $article_info;
	}
}


/**
 * 获取首页列表
 */
function get_home_single_info($id, $type = '',$t = ''){
	$info = AWS_APP::model('home')->get_single_info($id,$type,$t);
	if($t == "title"){
		if($type == 1){
			$info = $info['question_content'];
		}else{
			$info = $info['title'];
		}
	}else if($t == "content"){
		if($type == 1){
			$info = $info['question_detail'];
		}else{
			$info = $info['message'];
		}
	}else if($t == "image"){
		if($type == 1){
			$question_id   = $info['question_id'];
			$published_uid = $info['published_uid'];
			$info          = AWS_APP::model('question')->get_answer_users_by_question_id( $question_id,1,$published_uid );
            $info = reset($info);
			$uid  = $info['uid'];
		}else{
			$uid = $info['uid'];
            $info = AWS_APP::model('account')->get_user_info_by_uid($uid);
		}
		if($info){
            $info = '<a class="aw-user-name" data-id="'.$uid.'" href="people/'.$info['url_token'].'"><img src="'.get_avatar_url($uid, "max").'" /></a>';
        }else{
            $info = '<a class="aw-user-name" href="javascript:;"><img src="'.get_avatar_url($uid, "max").'" /></a>';
        }

	}else if( $t == 'link'){
		if($type == 1){
			//问答
			$info = 'question/'.$info['question_id'];
		}
		if($type == 2){
			//整理
			$info = 'sortout/'.$info['id'];
		}
		if($type == 3){
			//文章
			$info = 'article/'.$info['id'];
		}
		if($type == 4){
			//有奖
			$info = 'prize/'.$info['id'];
		}
	}else if( $t == 'meta' ){
		//$info  = '';
		if($type == 1){
			//问答
            $question_topics = AWS_APP::model('topic')->get_topics_by_item_id($info['question_id'], 'question');
			$info = '
			<div class="bt-line">
				<span><i class="icon icon-date"></i>&nbsp;'.date_friendly($info["add_time"],"","Y-m-d").'</span>
				<span><i class="icon icon-topic"></i>&nbsp;'.$info["answer_count"].'人回答</span>
				<span><i class="icon icon-favor"></i>'.$info['focus_count'].'人关注</span>
				<span><i class="icon icon-thank"></i>7人赞助</span>
				<span><i class="icon icon-format"></i>'.$info["view_count"].'次整理</span>';
			
            if(count($question_topics)){
                foreach($question_topics as $key => $item){
                    $info .= '<span class="keywords"><a href="topic/'. $item['url_token'] . '">' . $item['topic_title'] . '</a></span>';
                }
            }
            $info .= '</div>';
		}
		if($type == 2){
			//整理
            $category = AWS_APP::model('system')->get_category_info($info['category_id']);
			$info = '
			<div class="bt-line">
				<span><i class="icon icon-date"></i>&nbsp;'.date_friendly($info["add_time"],"","Y-m-d").'</span>
				<span><i class="icon icon-topic"></i>&nbsp;'.$info["comments"].'人评论</span>
				<span><i class="icon icon-share"></i> '.$info['focus_count'].'人收藏</span>';
            if($category){
                $info .= '<span class="keywords"><a href="javascript:;">' . $category['title'] . '</a></span>';
            }
            $info .= '</div>';
		}
		if($type == 3){
			//文章
            $category = AWS_APP::model('system')->get_category_info($info['category_id']);
            if($category){
                $cat_title = '<span class="keywords"><a href="javascript:;">'.$category["title"].'</a></span>';
            }
			$info = '
			<div class="bt-line">
				<span><i class="icon icon-date"></i>&nbsp;'.date_friendly($info["add_time"],"","Y-m-d").'</span>
				<span><i class="icon icon-topic"></i>&nbsp;'.$info['comments'].'人评论</span>
				<span><i class="icon icon-share"></i>&nbsp;'.$info['views'].'人浏览</span>
				'.$cat_title.'
			</div>';
		}
		if($type == 4){
			//有奖
            $category = AWS_APP::model('system')->get_category_info($info['category_id']);
			if($category){
				$cat_title = '<span class="keywords"><a href="javascript:;">'.$category["title"].'</a></span>';
			}
			$info = '
			<div class="bt-line">
				<span><i class="icon icon-date"></i>&nbsp;'.date_friendly($info['add_time'],"","Y-m-d").'</span>
				<span><i class="icon icon-topic"></i>&nbsp;'.$info['comments'].'人评论</span>
				<span><i class="icon icon-share"></i>&nbsp;'.$info['views'].'人浏览</span>
				'.$cat_title.'
			</div>';
		}
	}else{

	}
	//echo '========';
	return $info;
}

//获取用户关注信息
function get_single_user_follow($uid = 0){
	$info = AWS_APP::model('follow')->get_single_follow($uid);
	return $info;
}

function get_ad_thumb($thumb) {
    if (!$thumb)
    {
        return G_STATIC_URL . '/images/thumb.png';
    }

    if (file_exists(get_setting('upload_dir') . '/thumb/' . $thumb))
    {
        return get_setting('upload_url') . '/thumb/' . $thumb;
    }
    else
    {
        return G_STATIC_URL . '/images/thumb.png';
    }
}

//获取广告位的广告
function get_ad($location, $item_id, $uid, $user_info, $output = true){
    if($user_info['permission']['is_administortar'] OR $user_info['permission']['is_moderator']){
        $has_control = true;
    }
    if($uid){
        if($uid == $user_info['uid']){
            $has_control = true;
        }
        //用户的
        //$ad_show = AWS_APP::model('ad')->fetch_row('ad_show', "uid = " . intval($uid) . " AND location = " . intval($location));
        $ad_show = AWS_APP::model('ad')->fetch_row('ad_show', "item_id = " . intval($item_id) . " AND location = " . intval($location));
    }else{
        //管理员的
        $ad_show = AWS_APP::model('ad')->fetch_row('ad_show', "location = " . intval($location));
    }

    $need_remove = false;

    if($ad_show){
        $ad = AWS_APP::model('ad')->fetch_row('ad', 'status = 1 AND id = ' . intval($ad_show['ad_id']));
        /*if($ad['total_cash'] > 0 AND ($ad['total_cash'] - $ad['has_cash']) < (get_setting('ad_click_cash')*100)){
            //AWS_APP::model('ad')->remove_ad_show($ad_show['id']);
            $need_remove = true;
        }*/
        //if($ad['uid']){
            $user = AWS_APP::model('ad')->fetch_row('users', 'uid = ' . intval($ad['uid']));
            if($user['ad_cash'] < (get_setting('ad_click_cash')*100)){
                //AWS_APP::model('ad')->remove_ad_show($ad_show['id']);
                $need_remove = true;
            }
        //}
    }

    $content = '';
    if($ad OR $has_control){
        if($need_remove){
            if($ad['status'] == 1){
                AWS_APP::model('ad')->update('ad', [
                    'status' => -2
                ], 'id = ' . intval($ad['id']));
                //发送删除通知
                $this->model('notify')->send(0, $ad['uid'], notify_class::TYPE_CONTEXT, notify_class::CATEGORY_CONTEXT, 0, [
                    'from_uid'     => 0,
                    'content' => '您的广告《' . $ad['title'] . '》因欠费被下架了',
                ]);
            }
            $ad_show = null;
            $ad      = null;
        }
        if(!$has_control AND $need_remove){
            return $content;
        }
        TPL::assign('need_remove', $need_remove);
        TPL::assign('show_ad', $ad);
        TPL::assign('ad_show', $ad_show);
        TPL::assign('has_control', $has_control);
        TPL::assign('ad_location', $location);
        TPL::assign('select_uid', $uid);
        TPL::assign('item_id', $item_id);
        $content = TPL::output('block/ad', false);
    }
    if($output){
        echo $content;
    }else{
        return $content;
    }
}

function get_ad_url($ad_show = null, $uid = 0, $ad = null){
    if(!$ad_show AND !$ad){
        return false;
    }
    if($ad_show){
        $url = get_js_url('/info/' . $ad_show['ad_id']
            . '?a=' . AWS_APP::crypt()->encode($ad_show['id'])
            . '&u=' . AWS_APP::crypt()->encode($uid)
            . '&hash=' . uniqid(true));
    }else{
        $url = get_js_url('/info/' . $ad['id']
            . '?a=' . AWS_APP::crypt()->encode(-1)
            . '&u=' . AWS_APP::crypt()->encode($uid)
            . '&hash=' . uniqid(true));
    }

    return $url;
}