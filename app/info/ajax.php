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

define('IN_AJAX', TRUE);

if (!defined('IN_ANWSION'))
{
    die;
}

class ajax extends AWS_CONTROLLER
{
    public function get_access_rule()
    {
        $rule_action['rule_type'] = "white"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查

        $rule_action['actions'] = array(
            'list'
        );

        return $rule_action;
    }

    public function setup()
    {
        HTTP::no_cache_header();
    }

    public function remove_info_action(){
        if(!$ad = $this->model('ad')->fetch_row('ad', 'id = ' . intval($_POST['id']))){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', '广告不存在'));
        }
        if(!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'] AND $ad['uid'] != $this->user_id){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', '您不能删除该广告'));
        }

        $this->model('ad')->delete('ad', 'id = ' . intval($_POST['ad_id']));

        H::ajax_json_output(AWS_APP::RSM([
            'url' => get_js_url('info/')
        ], '1', null));
    }

    public function remove_comment_action()
    {
        if (!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('对不起, 你没有删除评论的权限')));
        }

        if ($comment_info = $this->model('ad')->fetch_row('ad_comments', 'id = ' . intval($_POST['comment_id'])))
        {
            $this->model('ad')->remove_comment($comment_info['id']);
        }

        H::ajax_json_output(AWS_APP::RSM(array(
            'url' => get_js_url('/info/' . $comment_info['ad_id'])
        ), 1, null));
    }

    public function list_action()
    {
        $where[] = 'status = 1';
        if($_GET['uid']){
            $where[] = 'uid = ' . intval($_GET['uid']);
        }
        $list = $this->model('ad')->fetch_page('ad', implode(' AND ', $where),  'id desc', $_GET['page'], get_setting('contents_per_page'));

        if($list){
            foreach ($list as $key => $val){
                $user = $this->model('ad')->fetch_row('users', 'uid = ' . intval($val['uid']));
                if($user['ad_cash'] < (get_setting('ad_click_cash')*100)){
                    unset($list[$key]);
                }
            }
        }

        TPL::assign('list', $list);

        TPL::output('info/ajax_list');

    }

    public function save_comment_action()
    {
        if (!$ad_info = $this->model('ad')->fetch_row('ad', 'id = ' . intval($_POST['ad_id'])))
        {
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('指定广告不存在')));
        }

        $message = trim($_POST['message'], "\r\n\t");

        if (! $message)
        {
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('请输入回复内容')));
        }

        if (strlen($message) < get_setting('answer_length_lower'))
        {
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('回复内容字数不得少于 %s 字节', get_setting('answer_length_lower'))));
        }

        if (! $this->user_info['permission']['publish_url'] AND FORMAT::outside_url_exists($message))
        {
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('你所在的用户组不允许发布站外链接')));
        }

        if (human_valid('answer_valid_hour') and ! AWS_APP::captcha()->is_validate($_POST['seccode_verify']))
        {
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('请填写正确的验证码')));
        }

        // !注: 来路检测后面不能再放报错提示
        if (! valid_post_hash($_POST['post_hash']))
        {
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('页面停留时间过长,或内容已提交,请刷新页面')));
        }

        $comment_id = $this->model('ad')->insert('ad_comments', [
            'ad_id'    => $ad_info['id'],
            'message'  => htmlspecialchars($message),
            'at_uid'   => intval($_POST['at_uid']),
            'add_time' => time(),
            'uid'      => $this->user_id
        ]);

        $comment_info = $this->model('ad')->get_comment_by_id($comment_id);

        $comment_info['message'] = $this->model('question')->parse_at_user($comment_info['message']);

        TPL::assign('comment_info', $comment_info);

        if (is_mobile())
        {
            H::ajax_json_output(AWS_APP::RSM(array(
                'ajax_html' => TPL::output('m/ajax/article_answer', false)
            ), 1, null));
        }
        else
        {
            H::ajax_json_output(AWS_APP::RSM(array(
                'ajax_html' => TPL::output('info/ajax_comment', false)
            ), 1, null));
        }
    }

    public function select_ad_action(){
        $location   = $_POST['location'];
        $select_uid = $_POST['select_uid'];
        $ad_id      = $_POST['ad_id'];
        $item_id    = $_POST['item_id'];
        $refer = AWS_APP::session()->refer;

        if($location > 20){
            //系统的
            if(!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator']){
                H::ajax_json_output(AWS_APP::RSM(null, '-1', '您没有权限操作'));
            }
        }else{
            if(!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator']){
                if($select_uid != $this->user_id){
                    H::ajax_json_output(AWS_APP::RSM(null, '-1', '您没有权限操作'));
                }
            }
        }

        if(!$ad = $this->model('ad')->fetch_row('ad', 'id = ' . intval($ad_id))){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', '广告不存在'));
        }

        $user = $this->model('ad')->fetch_row('users', 'uid = ' . intval($ad['uid']));

        if($user['ad_cash'] < (get_setting('ad_click_cash')*100)){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', '用户消费完了额度'));
        }

        $this->model('ad')->select_ad($location, $item_id, $select_uid, $ad_id, $this->user_id, $refer);

        unset(AWS_APP::session()->refer);
        H::ajax_json_output(AWS_APP::RSM([
            'url' => $refer
        ], 1, null));
    }

    public function offline_action(){
        if(!$ad_show = $this->model('ad')->fetch_row('ad_show', 'id = ' . intval($_POST['id']))){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', '广告不存在'));
        }
        if($ad_show['location'] > 20){
            //系统的
            if(!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator']){
                H::ajax_json_output(AWS_APP::RSM(null, '-1', '您没有权限操作'));
            }
        }else{
            if(!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator']){
                if($ad_show['uid'] != $this->user_id){
                    H::ajax_json_output(AWS_APP::RSM(null, '-1', '您没有权限操作'));
                }
            }
        }

        $this->model('ad')->remove_ad_show($ad_show['id'], $this->user_id, $_SERVER['HTTP_REFERER']);

        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }

    public function offline_ad_action(){
        if(!$ad = $this->model('ad')->fetch_row('ad', 'id = ' . intval($_POST['id']))){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', '广告不存在'));
        }
        if(!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'] AND $ad['uid'] != $this->user_id){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', '您不能下架该广告'));
        }

        $this->model('ad')->update('ad', [
            'status' => -1
        ], 'id = ' . intval($_POST['id']));

        //发送删除通知
        $this->model('notify')->send(0, $ad['uid'], notify_class::TYPE_CONTEXT, notify_class::CATEGORY_CONTEXT, 0, [
            'from_uid'     => 0,
            'content' => '您的广告《' . $ad['title'] . '》被下架了',
        ]);

        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }

    public function info_vote_action()
    {
        switch ($_POST['type'])
        {
            case 'info':
                $item_info = $this->model('ad')->fetch_row('ad', 'id = ' . intval($_POST['item_id']));
                break;
        }

        if (!$item_info)
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('内容不存在')));
        }

        if ($item_info['uid'] == $this->user_id)
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('不能对自己发表的内容进行投票')));
        }

        $reputation_factor = $this->model('account')->get_user_group_by_id($this->user_info['reputation_group'], 'reputation_factor');

        $this->model('ad')->ad_vote($_POST['type'], $_POST['item_id'], $_POST['rating'], $this->user_id, $reputation_factor, $item_info['uid']);

        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }
}
