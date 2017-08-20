<?php

if (!defined('IN_ANWSION'))
{
    die;
}

class question_class extends AWS_MODEL
{

    /**
     * 增加问题浏览次数记录
     * @param int $question_id
     */
    public function update_views($question_id)
    {
        if (AWS_APP::cache()->get('update_views_question_' . md5(session_id()) . '_' . intval($question_id)))
        {
            return false;
        }

        AWS_APP::cache()->set('update_views_question_' . md5(session_id()) . '_' . intval($question_id), time(), 60);

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
    public function save_question($question_content, $question_detail, $published_uid, $anonymous = 0, $ip_address = null, $from = null)
    {
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

        if ($from AND is_array($from))
        {
            foreach ($from AS $type => $from_id)
            {
                if (!is_digits($from_id))
                {
                    continue;
                }

                $to_save_question[$type . '_id'] = $from_id;
            }
        }

        $question_id = $this->insert('ab_question', $to_save_question);

        if ($question_id)
        {
            $this->shutdown_update('users', array(
                'question_count' => $this->count('question', 'published_uid = ' . intval($published_uid))
            ), 'uid = ' . intval($published_uid));

        }

        return $question_id;
    }

    public function update_question($question_id, $question_content, $question_detail, $uid, $verified = true, $modify_reason = null, $anonymous = null, $category_id = null,$thankmoney,$free_question = null)
    {
        if (!$question_info = $this->get_question_info_by_id($question_id) OR !$uid)
        {
            return false;
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

            $this->update('ab_question', $data, 'question_id = ' . intval($question_id));
        }

        if ($category_id)
        {
            $this->update('ab_question', array(
                'category_id' => intval($category_id)
            ), 'question_id = ' . intval($question_id));
        }

        return true;
    }

    public function remove_question($question_id)
    {
        if (!$question_info = $this->get_question_info_by_id($question_id))
        {
            return false;
        }

        $this->model('answer')->remove_answers_by_question_id($question_id); // 删除关联的回复内容

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

        $this->model('posts')->remove_posts_index($question_id, 'question');

        $this->delete('geo_location', "`item_type` = 'question' AND `item_id` = " . intval($question_id));

        $this->delete('question', 'question_id = ' . intval($question_id));

        if ($question_info['weibo_msg_id'])
        {
            if ($question_info['ticket_id'])
            {
                remove_assoc('weibo_msg', 'question', $question_info['question_id']);
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
                remove_assoc('received_email', 'question', $question_info['question_id']);
            }
            else
            {
                $this->model('edm')->remove_received_email($question_info['received_email_id']);
            }
        }

        if ($question_info['ticket_id'])
        {
            remove_assoc('ticket', 'question', $question_info['question_id']);
        }
    }

}