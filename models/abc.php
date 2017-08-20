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

class abc_class extends AWS_MODEL
{



/**
***ab_answer start
**/
    public function get_answer_count_by_question_id($question_id, $where = null)
    {
        if ($where)
        {
            $where = ' AND ' . $where;
        }

        return $this->count('ab_answer', "question_id = " . intval($question_id) . $where);
    }

    public function get_answer_list_by_question_id($question_id, $limit = 20, $where = null, $order = 'answer_id DESC')
    {
        if ($where)
        {
            $_where = ' AND (' . $where . ')';
        }

        if ($answer_list = $this->fetch_all('ab_answer', 'question_id = ' . intval($question_id) . $_where, $order, $limit))
        {
            foreach($answer_list as $key => $val)
            {
                $uids[] = $val['uid'];
            }
        }

        if ($uids)
        {
            if ($users_info = $this->model('account')->get_user_info_by_uids($uids, true))
            {
                foreach($answer_list as $key => $val)
                {
                    $answer_list[$key]['user_info'] = $users_info[$val['uid']];
                }
            }
        }

        return $answer_list;
    }

    public function has_answer_by_uid($question_id, $uid)
    {
        return $this->fetch_one('ab_answer', 'answer_id', "question_id = " . intval($question_id) . " AND uid = " . intval($uid));
    }

    public function get_answer_by_id($answer_id)
    {
        static $answers;

        if ($answers[$answer_id])
        {
            return $answers[$answer_id];
        }

        $answers[$answer_id] = $this->fetch_row('ab_answer', 'answer_id = ' . intval($answer_id));

        return $answers[$answer_id];
    }

    public function remove_answer_by_id($answer_id)
    {
        if ($answer_info = $this->model('abc')->get_answer_by_id($answer_id))
        {
            $this->delete('ab_answer_comments', 'answer_id = ' . intval($answer_id));  // 删除评论

            ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_ANSWER . ' AND associate_id = ' . intval($answer_id));
            ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_QUESTION . ' AND associate_action = ' . ACTION_LOG::ANSWER_QUESTION . ' AND associate_attached = ' . intval($answer_id));

            if ($attachs = $this->model('publish')->get_attach('ab_answer', $answer_id))
            {
                foreach ($attachs as $key => $val)
                {
                    $this->model('publish')->remove_attach($val['id'], $val['access_key']);
                }
            }

            $this->delete('ab_answer', "answer_id = " . intval($answer_id));

            $this->model('abc')->update_answer_count($answer_info['question_id']);
        }

        return true;
    }

    /**
     *
     * 保存问题回复内容
     */
    public function save_answer($question_id, $answer_content, $uid, $anonymous = 0)
    {
        if (!$question_info = $this->model('abc')->get_question_info_by_id($question_id))
        {
            return false;
        }

        if (!$answer_id = $this->insert('ab_answer', array(
            'question_id' => $question_info['question_id'],
            'answer_content' => htmlspecialchars($answer_content),
            'add_time' => time(),
            'uid' => intval($uid),
            'category_id' => $question_info['category_id'],
            'anonymous' => intval($anonymous),
            'ip' => ip2long(fetch_ip())
        )))
        {
            return false;
        }

        $this->update('ab_question', array(
            'update_time' => time(),
        ), 'question_id = ' . intval($question_id));

        $this->model('abc')->update_answer_count($question_id);
        $this->model('abc')->update_answer_users_count($question_id);

        $this->shutdown_update('users', array(
            'answer_count' => $this->count('answer', 'uid = ' . intval($uid))
        ), 'uid = ' . intval($uid));

        return $answer_id;
    }

    /**
     *
     * 更新问题回复内容
     */
    public function update_answer($answer_id, $question_id, $answer_content, $attach_access_key)
    {
        $answer_id = intval($answer_id);
        $question_id = intval($question_id);

        if (!$answer_id OR !$question_id)
        {
            return false;
        }

        $data = array(
            'answer_content' => htmlspecialchars($answer_content)
        );

        // 更新问题最后时间
        $this->shutdown_update('ab_question', array(
            'update_time' => time(),
        ), 'question_id = ' . intval($question_id));

        if ($attach_access_key)
        {
            $this->model('publish')->update_attach('ab_answer', $answer_id, $attach_access_key);
        }

        return $this->update('ab_answer', $data, 'answer_id = ' . intval($answer_id));
    }

    public function get_answer_comments($answer_id)
    {
        return $this->fetch_all('ab_answer_comments', "answer_id = " . intval($answer_id), "time ASC");
    }


    public function insert_answer_comment($answer_id, $uid, $message)
    {
        if (!$answer_info = $this->model('abc')->get_answer_by_id($answer_id))
        {
            return false;
        }

        if (!$question_info = $this->model('abc')->get_question_info_by_id($answer_info['question_id']))
        {
            return false;
        }

        $message = $this->model('abc')->parse_at_user($message, false, false, true);

        $comment_id = $this->insert('ab_answer_comments', array(
            'uid' => intval($uid),
            'answer_id' => intval($answer_id),
            'message' => htmlspecialchars($message),
            'time' => time()
        ));

        if ($answer_info['uid'] != $uid)
        {

            if ($weixin_user = $this->model('openid_weixin_weixin')->get_user_info_by_uid($answer_info['uid']))
            {
                $weixin_user_info = $this->model('account')->get_user_info_by_uid($weixin_user['uid']);

                if ($weixin_user_info['weixin_settings']['NEW_COMMENT'] != 'N')
                {
                    $this->model('weixin')->send_text_message($weixin_user['openid'], "您在 [" . $question_info['question_content'] . "] 中的回答收到了新评论:\n\n" . strip_tags($message), $this->model('openid_weixin_weixin')->redirect_url('/m/question/' . $question_info['question_id'] . '?answer_id=' . $answer_info['answer_id'] . '&single=TRUE'));
                }
            }
        }

        if ($at_users = $this->model('abc')->parse_at_user($message, false, true))
        {
            foreach ($at_users as $user_id)
            {
                if ($user_id != $uid)
                {
                    $this->model('notify')->send($uid, $user_id, notify_class::TYPE_ANSWER_COMMENT_AT_ME, notify_class::CATEGORY_QUESTION, $answer_info['question_id'], array(
                        'from_uid' => $uid,
                        'question_id' => $answer_info['question_id'],
                        'item_id' => $answer_info['answer_id'],
                        'comment_id' => $comment_id
                    ));

                    if ($weixin_user = $this->model('openid_weixin_weixin')->get_user_info_by_uid($user_id))
                    {
                        $answer_user = $this->model('account')->get_user_info_by_uid($uid);

                        $this->model('weixin')->send_text_message($weixin_user['openid'], $answer_user['user_name'] . " 在问题 [" . $question_info['question_content'] . "] 的答案评论中提到了您", $this->model('openid_weixin_weixin')->redirect_url('/m/question/' . $question_info['question_id'] . '?answer_id=' . $answer_info['answer_id'] . '&single=TRUE'));
                    }
                }
            }
        }

        $this->update_answer_comments_count($answer_id);

        return $comment_id;
    }

    public function update_answer_comments_count($answer_id)
    {
        $count = $this->count('ab_answer_comments', "answer_id = " . intval($answer_id));

        $this->shutdown_update('ab_answer', array(
            'comment_count' => $count
        ), "answer_id = " . intval($answer_id));
    }

//ab_answer end

    public function get_question_info_by_id($question_id, $cache = true)
    {
        if (! $question_id)
        {
            return false;
        }

        if (!$cache)
        {
            $questions[$question_id] = $this->fetch_row('ab_question', 'question_id = ' . intval($question_id));
        }
        else
        {
            static $questions;

            if ($questions[$question_id])
            {
                return $questions[$question_id];
            }

            $questions[$question_id] = $this->fetch_row('ab_question', 'question_id = ' . intval($question_id));
        }

        if ($questions[$question_id])
        {
            $questions[$question_id]['unverified_modify'] = @unserialize($questions[$question_id]['unverified_modify']);

            if (is_array($questions[$question_id]['unverified_modify']))
            {
                $counter = 0;

                foreach ($questions[$question_id]['unverified_modify'] AS $key => $val)
                {
                    $counter = $counter + sizeof($val);
                }

                $questions[$question_id]['unverified_modify_count'] = $counter;
            }
        }

        return $questions[$question_id];
    }

    public function get_question_info_by_ids($question_ids)
    {
        if (!$question_ids)
        {
            return false;
        }

        array_walk_recursive($question_ids, 'intval_string');

        if ($questions_list = $this->fetch_all('ab_question', "question_id IN(" . implode(',', $question_ids) . ")"))
        {
            foreach ($questions_list AS $key => $val)
            {
                $result[$val['question_id']] = $val;
            }
        }

        return $result;
    }

    /**
     * 增加问题浏览次数记录
     * @param int $question_id
     */
    public function update_views($question_id)
    {
        if (AWS_APP::cache()->get('update_views_ab_question_' . md5(session_id()) . '_' . intval($question_id)))
        {
            return false;
        }

        AWS_APP::cache()->set('update_views_ab_question_' . md5(session_id()) . '_' . intval($question_id), time(), 60);

        $this->shutdown_query("UPDATE " . $this->get_table('ab_question') . " SET view_count = view_count + 1 WHERE question_id = " . intval($question_id));

        return true;
    }

    /**
     *
     * 增加问题内容
     * @param string $question_content //问题内容
     * @param string $question_detail  //问题说明
     *
     * @return boolean true|false
     */
    public function save_question($question_content, $question_detail, $published_uid, $anonymous = 0, $ip_address = null, $ToShow = null)
    {

        if ($ToShow AND is_array($ToShow))
        {
            foreach ($ToShow AS $toShowPlace => $question_id)
            {
                $to_save_question[$toShowPlace] = $question_id;
            }
        }

        if (!$ip_address)
        {
            $ip_address = fetch_ip();
        }

        $now = time();

        $to_save_question = array(
            'question_content' => htmlspecialchars($question_content),
            'question_detail' => htmlspecialchars($question_detail),
            'add_time' => $now,
            'update_time' => $now,
            'published_uid' => intval($published_uid),
            'anonymous' => intval($anonymous),
            'ip' => ip2long($ip_address)
        );

        $question_id = $this->insert('ab_question', $to_save_question);

        if ($question_id)
        {
            $this->shutdown_update('users', array(
                'question_count' => $this->count('question', 'published_uid = ' . intval($published_uid))
            ), 'uid = ' . intval($published_uid));

            $this->model('search_fulltext')->push_index('ab_question', $question_content, $question_id);
        }

        return $question_id;
    }

    public function update_question($question_id, $question_content, $question_detail, $uid, $verified = true, $modify_reason = null, $anonymous = null, $category_id = null,$thankmoney,$free_question = null,$toShow=null)
    {
        if (!$question_info = $this->get_question_info_by_id($question_id) OR !$uid)
        {
            return false;
        }

        if ($toShow AND is_array($toShow))
        {
            foreach ($toShow AS $toShowPlace => $question_id)
            {
                $data[$toShowPlace] = $question_id;
            }
        }



        if ($verified)
        {
            $data['question_detail'] = htmlspecialchars($question_detail);
            if ($free_question)
            {
                $data['question_free'] = htmlspecialchars($free_question);
            }else{ $data['question_free'] = 0; }
            if ($question_content)
            {
                $data['question_content'] = htmlspecialchars($question_content);
            }
            $data['update_time']=time();
            $this->model('search_fulltext')->push_index('ab_question', $question_content, $question_id);
            $this->update('ab_question', $data, 'question_id = ' . intval($question_id));
        }

        if ($category_id)
        {
            $this->update('ab_question', array(
                'category_id' => intval($category_id)
            ), 'question_id = ' . intval($question_id));
        }

        $addon_data = array(
            'modify_reason' => $modify_reason,
        );

        if ($question_info['question_detail'] != $question_detail)
        {
            $log_id = ACTION_LOG::save_action($uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::MOD_QUESTION_DESCRI, $question_detail, $question_info['question_detail'], null, $anonymous, $addon_data);

            if (!$verified)
            {
                $this->track_unverified_modify($question_id, $log_id, 'detail');
            }

        }

        //记录日志
        if ($question_info['question_content'] != $question_content)
        {
            $log_id = ACTION_LOG::save_action($uid, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::MOD_QUESTION_TITLE, $question_content, $question_info['question_content'], 0, 0, $addon_data);

            if (!$verified)
            {
                $this->track_unverified_modify($question_id, $log_id, 'content');
            }
        }

        $this->model('posts')->set_posts_index($question_id, 'ab_question');

        return true;
    }

    public function verify_modify($question_id, $log_id)
    {
        if (!$unverified_modify = $this->get_unverified_modify($question_id))
        {
            return false;
        }

        if (!$action_log = ACTION_LOG::get_action_by_history_id($log_id))
        {
            return false;
        }

        if (@in_array($log_id, $unverified_modify['content']))
        {
            $this->update('ab_question', array(
                'question_content' => $action_log['associate_content'],
                'update_time' => time()
            ), 'question_id = ' . intval($question_id));

            $this->model('search_fulltext')->push_index('ab_question', $action_log['associate_content'], $question_id);

            $this->clean_unverified_modify($question_id, 'content');

            ACTION_LOG::update_action_time_by_history_id($log_id);
        }
        else if (@in_array($log_id, $unverified_modify['detail']))
        {
            $this->update('ab_question', array(
                'question_detail' => $action_log['associate_content'],
                'update_time' => time()
            ), 'question_id = ' . intval($question_id));

            $this->clean_unverified_modify($question_id, 'detail');

            ACTION_LOG::update_action_time_by_history_id($log_id);
        }

        return false;
    }

    public function unverify_modify($question_id, $log_id)
    {
        if (!$unverified_modify = $this->get_unverified_modify($question_id))
        {
            return false;
        }

        if (!$log_id)
        {
            return false;
        }

        if (@in_array($log_id, $unverified_modify['content']))
        {
            unset($unverified_modify['content'][$log_id]);

            ACTION_LOG::delete_action_history('history_id = ' . intval($log_id));
        }
        else if (@in_array($log_id, $unverified_modify['detail']))
        {
            unset($unverified_modify['detail'][$log_id]);

            ACTION_LOG::delete_action_history('history_id = ' . intval($log_id));
        }

        $this->save_unverified_modify($question_id, $unverified_modify);

        return false;
    }

    public function get_unverified_modify($question_id)
    {
        if (!$question_info = $this->get_question_info_by_id($question_id, false))
        {
            return false;
        }

        if (is_array($question_info['unverified_modify']))
        {
            return $question_info['unverified_modify'];
        }

        if ($question_info['unverified_modify'] = @unserialize($question_info['unverified_modify']))
        {
            return $question_info['unverified_modify'];
        }

        return array();
    }

    public function save_unverified_modify($question_id, $unverified_modify = array())
    {
        $unverified_modify_count = 0;

        foreach ($unverified_modify AS $unverified_modify_info)
        {
            $unverified_modify_count = $unverified_modify_count + count($unverified_modify_info);
        }

        return $this->update('ab_question', array(
            'unverified_modify' => serialize($unverified_modify),
            'unverified_modify_count' => $unverified_modify_count
        ), 'question_id = ' . intval($question_id));
    }

    public function track_unverified_modify($question_id, $log_id, $type)
    {
        $unverified_modify = $this->get_unverified_modify($question_id);

        $unverified_modify[$type][$log_id] = $log_id;

        return $this->save_unverified_modify($question_id, $unverified_modify);
    }

    public function clean_unverified_modify($question_id, $type)
    {
        $unverified_modify = $this->get_unverified_modify($question_id);

        unset($unverified_modify[$type]);

        return $this->save_unverified_modify($question_id, $unverified_modify);
    }

    public function get_unverified_modify_count($question_id)
    {
        $question_info = $this->get_question_info_by_id($question_id, false);

        if (!$question_info)
        {
            return false;
        }

        return $question_info['unverified_modify_count'];
    }

    public function remove_question($question_id)
    {
        if (!$question_info = $this->get_question_info_by_id($question_id))
        {
            return false;
        }

        $this->model('ab_answer')->remove_answers_by_question_id($question_id); // 删除关联的回复内容

        // 删除评论
        $this->delete('ab_question_comments', 'question_id = ' . intval($question_id));

        ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_QUESTION .  ' AND associate_id = ' . intval($question_id));    // 删除动作

        ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_QUESTION .  ' AND associate_action = ' . ACTION_LOG::ANSWER_QUESTION . ' AND associate_attached = ' . intval($question_id));   // 删除动作

        ACTION_LOG::delete_action_history('associate_type = ' . ACTION_LOG::CATEGORY_TOPIC . ' AND associate_action = ' . ACTION_LOG::ADD_TOPIC . ' AND associate_attached = ' . intval($question_id)); // 删除动作

        // 删除附件
        if ($attachs = $this->model('publish')->get_attach('ab_question', $question_id))
        {
            foreach ($attachs as $key => $val)
            {
                $this->model('publish')->remove_attach($val['id'], $val['access_key']);
            }
        }

        $this->model('notify')->delete_notify('model_type = 1 AND source_id = ' . intval($question_id));    // 删除相关的通知

        $this->shutdown_update('users', array(
            'question_count' => $this->count('question', 'published_uid = ' . intval($question_info['published_uid']))
        ), 'uid = ' . intval($question_info['published_uid']));

        $this->delete('redirect', "item_id = " . intval($question_id) . " OR target_id = " . intval($question_id));

        $this->model('posts')->remove_posts_index($question_id, 'ab_question');

        $this->delete('geo_location', "`item_type` = 'ab_question' AND `item_id` = " . intval($question_id));

        $this->delete('ab_question', 'question_id = ' . intval($question_id));

        if ($question_info['weibo_msg_id'])
        {
            if ($question_info['ticket_id'])
            {
                remove_assoc('weibo_msg', 'ab_question', $question_info['question_id']);
            }
            else
            {
                $this->model('openid_weibo_weibo')->del_msg_by_id($question_info['weibo_msg_id']);
            }
        }

        if ($question_info['received_email_id'])
        {
            if ($question_info['ticket_id'])
            {
                remove_assoc('received_email', 'ab_question', $question_info['question_id']);
            }
            else
            {
                $this->model('edm')->remove_received_email($question_info['received_email_id']);
            }
        }

        if ($question_info['ticket_id'])
        {
            remove_assoc('ticket', 'ab_question', $question_info['question_id']);
        }
    }


    public function update_answer_count($question_id)
    {
        if (!$question_id)
        {
            return false;
        }

        return $this->update('ab_question', array(
            'answer_count' => $this->count('ab_answer', 'question_id = ' . intval($question_id))
        ), 'question_id = ' . intval($question_id));
    }

    public function update_answer_users_count($question_id)
    {
        if (!$question_id)
        {
            return false;
        }

        return $this->update('ab_question', array(
            'answer_users' => $this->count('ab_answer', 'question_id = ' . intval($question_id))
        ), 'question_id = ' . intval($question_id));
    }


    public function parse_at_user($content, $popup = false, $with_user = false, $to_uid = false)
    {
        preg_match_all('/@([^@,:\s,]+)/i', strip_tags($content), $matchs);

        if (is_array($matchs[1]))
        {
            $match_name = array();

            foreach ($matchs[1] as $key => $user_name)
            {
                if (in_array($user_name, $match_name))
                {
                    continue;
                }

                $match_name[] = $user_name;
            }

            $match_name = array_unique($match_name);

            arsort($match_name);

            $all_users = array();

            $content_uid = $content;

            foreach ($match_name as $key => $user_name)
            {
                if (preg_match('/^[0-9]+$/', $user_name))
                {
                    $user_info = $this->model('account')->get_user_info_by_uid($user_name);
                }
                else
                {
                    $user_info = $this->model('account')->get_user_info_by_username($user_name);
                }

                if ($user_info)
                {
                    $content = str_replace('@' . $user_name, '<a href="people/' . $user_info['url_token'] . '"' . (($popup) ? ' target="_blank"' : '') . ' class="aw-user-name" data-id="' . $user_info['uid'] . '">@' . $user_info['user_name'] . '</a>', $content);

                    if ($to_uid)
                    {
                        $content_uid = str_replace('@' . $user_name, '@' . $user_info['uid'], $content_uid);
                    }

                    if ($with_user)
                    {
                        $all_users[] = $user_info['uid'];
                    }
                }
            }
        }

        if ($with_user)
        {
            return $all_users;
        }

        if ($to_uid)
        {
            return $content_uid;
        }

        return $content;
    }

    public function update_question_toShow($question_id,$toShow){
            $data=$toShow;
            $data['update_time']=time();
            $this->update('ab_question', $data, 'question_id = ' . intval($question_id));

    }

    public function update_question_comments_count($question_id)
    {
        $count = $this->count('ab_question_comments', 'question_id = ' . intval($question_id));

        $this->shutdown_update('ab_question', array(
            'comment_count' => $count
        ), 'question_id = ' . intval($question_id));
    }

    public function insert_question_comment($question_id, $uid, $message)
    {
        if (!$question_info = $this->model('abc')->get_question_info_by_id($question_id))
        {
            return false;
        }

        $message = $this->model('abc')->parse_at_user($_POST['message'], false, false, true);

        $comment_id = $this->insert('ab_question_comments', array(
            'uid' => intval($uid),
            'question_id' => intval($question_id),
            'message' => htmlspecialchars($message),
            'time' => time()
        ));


        $this->update_question_comments_count($question_id);

        return $comment_id;
    }

    public function get_question_comments($question_id)
    {
        return $this->fetch_all('ab_question_comments', 'question_id = ' . intval($question_id), "time ASC");
    }

    public function get_comment_by_id($comment_id)
    {
        return $this->fetch_row('ab_question_comments', "id = " . intval($comment_id));
    }

    public function remove_comment($comment_id)
    {
        return $this->delete('ab_question_comments', "id = " . intval($comment_id));
    }


    public function redirect($uid, $item_id, $target_id = NULL)
    {
        if ($item_id == $target_id)
        {
            return false;
        }

        if (! $target_id)
        {
            if ($this->delete('redirect', 'item_id = ' . intval($item_id)))
            {
                return ACTION_LOG::save_action($uid, $item_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::DEL_REDIRECT_QUESTION);
            }
        }
        else if ($question = $this->get_question_info_by_id($item_id))
        {
            if (! $this->fetch_row('redirect', 'item_id = ' . intval($item_id) . ' AND target_id = ' . intval($target_id)))
            {
                $redirect_id = $this->insert('redirect', array(
                    'item_id' => intval($item_id),
                    'target_id' => intval($target_id),
                    'time' => time(),
                    'uid' => intval($uid)
                ));

                ACTION_LOG::save_action($uid, $item_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::REDIRECT_QUESTION, $question['question_content'], $target_id);

                return $redirect_id;
            }
        }
    }

    public function get_redirect($item_id)
    {
        return $this->fetch_row('redirect', 'item_id = ' . intval($item_id));
    }

    public function save_last_answer($question_id, $answer_id = null)
    {
        if (!$answer_id)
        {
            if ($last_answer = $this->fetch_row('ab_answer', 'question_id = ' . intval($question_id), 'add_time DESC'))
            {
                $answer_id = $last_answer['answer_id'];
            }
        }

        return $this->shutdown_update('ab_question', array('last_answer' => intval($answer_id)), 'question_id = ' . intval($question_id));
    }

    public function calc_popular_value($question_id)
    {
        if (!$question_info = $this->fetch_row('ab_question', 'question_id = ' . intval($question_id)))
        {
            return false;
        }

        if ($question_info['popular_value_update'] > time() - 300)
        {
            return false;
        }

        //$popular_value = (log($question_info['view_count'], 10) * 4 + $question_info['focus_count'] + ($question_info['agree_count'] * $question_info['agree_count'] / ($question_info['agree_count'] + $question_info['against_count'] + 1))) / (round(((time() - $question_info['add_time']) / 3600), 1) / 2 + round(((time() - $question_info['update_time']) / 3600), 1) / 2 + 1);
        $popular_value = log($question_info['view_count'], 10) + $question_info['focus_count'] + ($question_info['agree_count'] * $question_info['agree_count'] / ($question_info['agree_count'] + $question_info['against_count'] + 1));

        return $this->shutdown_update('question', array(
            'popular_value' => $popular_value,
            'popular_value_update' => time()
        ), 'question_id = ' . intval($question_id));
    }

    public function get_modify_reason()
    {
        if ($modify_reasons = explode("\n", get_setting('question_modify_reason')))
        {
            $modify_reason = array();

            foreach($modify_reasons as $key => $val)
            {
                $val = trim($val);

                if ($val)
                {
                    $modify_reason[] = $val;
                }
            }

            return $modify_reason;
        }
        else
        {
            return false;
        }
    }

    public function save_report($uid, $type, $target_id, $reason, $url)
    {
        return $this->insert('report', array(
            'uid' => $uid,
            'type' => htmlspecialchars($type),
            'target_id' => $target_id,
            'reason' => htmlspecialchars($reason),
            'url' => htmlspecialchars($url),
            'add_time' => time(),
            'status' => 0,
        ));
    }

    public function get_report_list($where, $page, $pre_page, $order = 'id DESC')
    {
        return $this->fetch_page('report', $where, $order, $page, $pre_page);
    }

    public function update_report($report_id, $data)
    {
        return $this->update('report', $data, 'id = ' . intval($report_id));
    }

    public function delete_report($report_id)
    {
        return $this->delete('report', 'id = ' . intval($report_id));
    }


    public function lock_question($question_id, $lock_status = true)
    {
        return $this->update('ab_question', array(
            'lock' => intval($lock_status)
        ), 'question_id = ' . intval($question_id));
    }

    public function auto_lock_question()
    {
        if (!get_setting('auto_question_lock_day'))
        {
            return false;
        }

        return $this->shutdown_update('ab_question', array(
            'lock' => 1
        ), '`lock` = 0 AND `update_time` < ' . (time() - 3600 * 24 * get_setting('auto_question_lock_day')));
    }

    public function get_answer_users_by_question_id($question_id, $limit = 5, $published_uid = null)
    {
        if ($result = AWS_APP::cache()->get('ab_answer_users_by_question_id_' . md5($question_id . $limit . $published_uid)))
        {
            return $result;
        }

        if (!$published_uid)
        {
            if (!$question_info = $this->get_question_info_by_id($question_id))
            {
                return false;
            }

            $published_uid = $question_info['published_uid'];
        }

        if ($answer_users = $this->query_all("SELECT DISTINCT uid FROM " . get_table('ab_answer') . " WHERE question_id = " . intval($question_id) . " AND uid <> " . intval($published_uid) . " AND anonymous = 0 ORDER BY agree_count DESC LIMIT " . intval($limit)))
        {
            foreach ($answer_users AS $key => $val)
            {
                $answer_uids[] = $val['uid'];
            }

            $result = $this->model('account')->get_user_info_by_uids($answer_uids);

            AWS_APP::cache()->set('ab_answer_users_by_question_id_' . md5($question_id . $limit . $published_uid), $result, get_setting('cache_level_normal'));
        }

        return $result;
    }

    /**
     * 问题列表
     * 
     */
    function get_question_list($limit = null,$type=null,$site=null)
    {
        $WHERE=[];
        $WHERE[]=" 1=1 ";
        if($type)
            $WHERE[]=" `category_id` = ".$type;
        switch ($site) {
            case 'article':
                $WHERE[]=" `WZ` IS NOT NULL ";
                break;
            case 'question':
                $WHERE[]=" `WD` IS NOT NULL ";
                break;
            case 'explore':
                $WHERE[]=" `SY` IS NOT NULL ";
                break;
            default:
                # code...
                break;
        }
        $where=implode(" AND ", $WHERE);

        $sql = "SELECT * FROM " . get_table('ab_question') . " WHERE ".$where." ORDER BY category_id,question_id DESC";
        $result = $this->query_all($sql, $limit);

        return $result;
    }
}
