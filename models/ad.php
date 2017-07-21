<?php

/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2017/3/27
 * Time: 上午11:15
 */
class ad_class extends AWS_MODEL
{
    //用户的小于20,系统的大于20
    const L_ARTICLE_1       = 1;
    const L_ARTICLE_2       = 2;
    const L_ARTICLE_3       = 3;
    const L_SORTOUT_1       = 4;
    const L_SORTOUT_2       = 5;
    const L_SORTOUT_3       = 6;
    const L_PEOPLE_1        = 7;
    const L_PEOPLE_2        = 8;
    const L_PEOPLE_3        = 9;
    const L_ASK_1           = 21;
    const L_ASK_2           = 22;
    const L_ASK_3           = 23;
    const L_ASK_LIST_1      = 24;
    const L_ASK_LIST_2      = 25;
    const L_ASK_LIST_3      = 26;
    const L_ARTICLE_LIST_1  = 27;
    const L_ARTICLE_LIST_2  = 28;
    const L_ARTICLE_LIST_3  = 29;
    const L_SORTOUT_LIST_1  = 30;
    const L_SORTOUT_LIST_2  = 31;
    const L_SORTOUT_LIST_3  = 32;
    const L_PUBLISH_ASK     = 33;
    const L_PUBLISH_ARTICLE = 34;
    const L_AD_LIST         = 35;
    const L_TOPIC_1         = 36;
    const L_TOPIC_2         = 37;
    const L_TOPIC_3         = 38;
    const L_TOPIC_LIST_1    = 39;
    const L_TOPIC_LIST_2    = 40;
    const L_TOPIC_LIST_3    = 41;

    const SHOW_ADD          = 1;
    const SHOW_REMOVE       = 2;

    public function get_location($location_id){
        switch ($location_id){
            case static::L_ARTICLE_1:
                $msg = '文章内页1'; break;
            case static::L_ARTICLE_2:
                $msg = '文章内页2'; break;
            case static::L_ARTICLE_3:
                $msg = '文章内页3'; break;
            case static::L_SORTOUT_1:
                $msg = '整理内页1'; break;
            case static::L_SORTOUT_2:
                $msg = '整理内页2'; break;
            case static::L_SORTOUT_3:
                $msg = '整理内页3'; break;
            case static::L_PEOPLE_1:
                $msg = '用户页1'; break;
            case static::L_PEOPLE_2:
                $msg = '用户页2'; break;
            case static::L_PEOPLE_3:
                $msg = '用户页3'; break;
            case static::L_ASK_1:
                $msg = '问答内页1'; break;
            case static::L_ASK_2:
                $msg = '问答内页1'; break;
            case static::L_ASK_3:
                $msg = '问答内页3'; break;
            case static::L_ASK_LIST_1:
                $msg = '问答列表1'; break;
            case static::L_ASK_LIST_2:
                $msg = '问答列表2'; break;
            case static::L_ASK_LIST_3:
                $msg = '问答列表3'; break;
            case static::L_ARTICLE_LIST_1:
                $msg = '文章列表1'; break;
            case static::L_ARTICLE_LIST_2:
                $msg = '文章列表2'; break;
            case static::L_ARTICLE_LIST_3:
                $msg = '文章列表3'; break;
            case static::L_SORTOUT_LIST_1:
                $msg = '整列列表1'; break;
            case static::L_SORTOUT_LIST_2:
                $msg = '整列列表2'; break;
            case static::L_SORTOUT_LIST_3:
                $msg = '整列列表3'; break;
            case static::L_PUBLISH_ASK:
                $msg = '问题发布页'; break;
            case static::L_PUBLISH_ARTICLE:
                $msg = '文章发布页'; break;
            case static::L_AD_LIST:
                $msg = '广告列表页'; break;
            case static::L_TOPIC_1:
                $msg = '关键词页1'; break;
            case static::L_TOPIC_2:
                $msg = '关键词页2'; break;
            case static::L_TOPIC_3:
                $msg = '关键词页3'; break;
            case static::L_TOPIC_LIST_1:
                $msg = '关键词列表页1'; break;
            case static::L_TOPIC_LIST_2:
                $msg = '关键词列表页2'; break;
            case static::L_TOPIC_LIST_3:
                $msg = '关键词列表页3'; break;
        }

        return $msg;
    }

    public function remove_comment($comment_id)
    {
        $comment_info = $this->fetch_row('ad_comments', 'id = ' . intval($comment_id));
        if(!$comment_info){
            return false;
        }
        $this->delete('ad_comments', 'id = ' . intval($comment_id));

        $this->update('ad', array(
            'comment_count' => $this->count('ad_comments', 'ad_id = ' . $comment_info['ad_id'])
        ), 'id = ' . $comment_info['ad_id']);

        //发送删除通知
        $this->model('notify')->send(0, $comment_info['uid'], notify_class::TYPE_CONTEXT, notify_class::CATEGORY_CONTEXT, 0, [
            'from_uid'     => 0,
            'content' => '您的广告评论《' . $comment_info['message'] . '》被管理员删除了',
        ]);

        return true;
    }

    public function get_comment_by_id($comment_id)
    {
        if ($comment = $this->fetch_row('ad_comments', 'id = ' . intval($comment_id)))
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

    public function remove_ad($ad_id)
    {
        if (!$ad_info = $this->fetch_row('ad', 'id = ' . intval($ad_id)))
        {
            return false;
        }

        $this->delete('ad_comments', "ad_id = " . intval($ad_id)); // 删除关联的回复内容

        // 删除附件
        if ($attachs = $this->model('publish')->get_attach('ad', $ad_id))
        {
            foreach ($attachs as $key => $val)
            {
                $this->model('publish')->remove_attach($val['id'], $val['access_key']);
            }
        }

        $this->model('notify')->delete_notify('model_type = 10 AND source_id = ' . intval($ad_id));	// 删除相关的通知

        //发送删除通知
        $this->model('notify')->send(0, $ad_info['uid'], notify_class::TYPE_CONTEXT, notify_class::CATEGORY_CONTEXT, 0, [
            'from_uid'     => 0,
            'content' => '您的广告《' . $ad_info['title'] . '》被管理员删除了',
        ]);

        return $this->delete('ad', 'id = ' . intval($ad_id));
    }

    public function select_ad($location, $item_id, $uid, $ad_id, $operator, $page_link = null){
        if(!$ad = $this->fetch_row('ad', 'id = ' . intval($ad_id))){
            return false;
        }
        //if($ad_show = $this->fetch_row('ad_show', "uid = " . intval($uid) . " AND location = " . intval($location))){
        if($ad_show = $this->fetch_row('ad_show', "item_id = " . intval($item_id) . " AND location = " . intval($location))){
            if($ad_show['ad_id'] != $ad_id){
                $this->update('ad_show', [
                    'ad_id'    => $ad_id,
                    'operator' => intval($operator),
                    'add_time' => time()
                ], 'id = ' . $ad_show['id']);

                $this->insert('ad_log', [
                    'ad_id'     => $ad_show['ad_id'],
                    'uid'       => intval($uid),
                    'item_id'   => intval($item_id),
                    'location'  => $location,
                    'ad_uid'    => $ad['uid'],
                    'operator'  => intval($operator),
                    'add_time'  => time(),
                    'type'      => static::SHOW_REMOVE,
                    'page_link' => htmlspecialchars($page_link)
                ]);

                $this->insert('ad_log', [
                    'ad_id'     => $ad_id,
                    'uid'       => intval($uid),
                    'item_id'   => intval($item_id),
                    'location'  => $location,
                    'ad_uid'    => $ad['uid'],
                    'operator'  => intval($operator),
                    'add_time'  => time(),
                    'type'      => static::SHOW_ADD,
                    'page_link' => htmlspecialchars($page_link)
                ]);
            }
        }else{
            $this->insert('ad_show', [
                'ad_id'    => $ad_id,
                'uid'      => intval($uid),
                'item_id'  => intval($item_id),
                'location' => $location,
                'ad_uid'   => $ad['uid'],
                'operator' => intval($operator),
                'add_time' => time()
            ]);

            $this->insert('ad_log', [
                'ad_id'     => $ad_id,
                'uid'       => intval($uid),
                'item_id'   => intval($item_id),
                'location'  => $location,
                'ad_uid'    => $ad['uid'],
                'operator'  => intval($operator),
                'add_time'  => time(),
                'type'      => static::SHOW_ADD,
                'page_link' => htmlspecialchars($page_link)
            ]);
        }
    }

    public function remove_ad_show($id, $operator, $page_link) {
        if ($ad_show = $this->fetch_row('ad_show', 'id = ' . intval($id))) {
            $this->delete('ad_show', 'id = ' . intval($id));
            $this->insert('ad_log', [
                'ad_id'     => $ad_show['ad_id'],
                'uid'       => $ad_show['uid'],
                'item_id'   => intval($ad_show['item_id']),
                'location'  => $ad_show['location'],
                'ad_uid'    => $ad_show['ad_uid'],
                'operator'  => intval($operator),
                'add_time'  => time(),
                'type'      => static::SHOW_REMOVE,
                'page_link' => htmlspecialchars($page_link)
            ]);
        }

        return true;
    }

    public function ad_click($click_uid, $show_ad, $page_link){
        if($click_uid == $show_ad['ad_uid']){
            return false;
        }
        $ad_cash = get_setting('ad_click_cash')*100;
        $ad_user = $this->fetch_row('users', 'uid = ' . intval($show_ad['ad_uid']));

        if($ad_user['ad_cash'] < (get_setting('ad_click_cash')*100)){
            return false;//不能出现负值
        }
        //这里需要计数
        $this->model('ad')->insert('click_log', [
            'uid'      => intval($click_uid),
            'ip'       => ip2long(fetch_ip()),
            'ad_id'    => $show_ad['ad_id'],
            'add_time' => time(),
            'ad_uid'   => $show_ad['ad_uid'],
            'show_uid' => $show_ad['uid'],
            'item_id'   => intval($show_ad['item_id']),
            'show_location' => $show_ad['location'],
            'page_link' => $page_link,
            'cash'     => $ad_cash,
        ]);

        $this->update('users', [
            'ad_cash' => $ad_user['ad_cash'] - $ad_cash,
        ], 'uid = ' . intval($show_ad['ad_uid']));
        //记录流水
        $this->insert('finance', [
            'uid' => $show_ad['ad_uid'],
            'add_time' => time(),
            'type'     => finance_class::TYPE_AD,
            'action'   => finance_class::ACTION_OUT,
            'item_type' => 'ad',
            'item_id'   => $show_ad['ad_id'],
            'cash'      => $ad_cash,
            'from_uid'  => intval($show_ad['uid']),
            'balance'   => $this->model('account')->fetch_one('users', 'ad_cash', 'uid = ' . intval($show_ad['ad_uid']))
        ]);

        if($show_ad['uid']) {
            $show_ad_user = $this->fetch_row('users', 'uid = ' . intval($show_ad['uid']));
            $this->update('users', [
                'ad_cash' => $show_ad_user['ad_cash'] + $ad_cash,
            ], 'uid = ' . intval($show_ad['uid']));
            //记录流水
            $this->insert('finance', [
                'uid' => $show_ad['uid'],
                'add_time' => time(),
                'type'     => finance_class::TYPE_AD,
                'action'   => finance_class::ACTION_IN,
                'item_type' => 'ad',
                'item_id'   => $show_ad['ad_id'],
                'cash'      => $ad_cash,
                'from_uid'  => intval($show_ad['ad_uid']),
                'balance'   => $this->model('account')->fetch_one('users', 'ad_cash', 'uid = ' . intval($show_ad['uid']))
            ]);
        }
        //tongji
        $this->update('ad', [
            'has_cash' => $this->sum('click_log', 'cash', 'ad_id = ' . $show_ad['ad_id'])
        ], 'id = ' . $show_ad['ad_id']);

        if($ad_user['ad_cash'] < (get_setting('ad_click_cash')*100)*2){
            $this->update('ad', [
                'status' => -2,
            ], 'uid = ' . intval($show_ad['ad_uid']));
            //发送删除通知
            $this->model('notify')->send(0, $show_ad['ad_uid'], notify_class::TYPE_CONTEXT, notify_class::CATEGORY_CONTEXT, 0, [
                'from_uid'     => 0,
                'content' => '由于您的账号余额不足,您的广告被下架了',
            ]);
        }
    }

    public function ad_vote($type, $item_id, $rating, $uid, $reputation_factor, $item_uid)
    {
        $this->delete('ad_vote', "`type` = '" . $this->quote($type) . "' AND item_id = " . intval($item_id) . ' AND uid = ' . intval($uid));

        if ($rating)
        {
            if ($ad_vote = $this->fetch_row('ad_vote', "`type` = '" . $this->quote($type) . "' AND item_id = " . intval($item_id) . " AND rating = " . intval($rating) . ' AND uid = ' . intval($uid)))
            {
                $this->update('ad_vote', array(
                    'rating' => intval($rating),
                    'time' => time(),
                    'reputation_factor' => $reputation_factor
                ), 'id = ' . intval($ad_vote['id']));
            }
            else
            {
                $this->insert('ad_vote', array(
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
            case 'info':
                $this->update('ad', array(
                    'agree_count' => $this->count('ad_vote', "`type` = '" . $this->quote($type) . "' AND item_id = " . intval($item_id) . " AND rating = 1")
                ), 'id = ' . intval($item_id));

                break;
        }

        return true;
    }
}