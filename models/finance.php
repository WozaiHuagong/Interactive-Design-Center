<?php

/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2017/3/28
 * Time: 下午3:47
 */
class finance_class extends AWS_MODEL
{
    const ACTION_IN  = 1;
    const ACTION_OUT = 2;

    const TYPE_AD     = 1;
    const TYPE_SHANG  = 2;
    //const TYPE_CHARGE = 3;
    //status = 'wait','ok','close'

    public function save_charge($uid, $cash, $type){
        if($charge = $this->fetch_row('charge', 'add_time > ' . strtotime('-1 day') . ' AND status = \'wait\' AND uid = ' . intval($uid) . ' AND cash = ' . intval($cash) . ' AND type = ' . intval($type))){
            return $charge;
        }

        $charge_id = $this->insert('charge', [
            'uid'  => intval($uid),
            'cash' => intval($cash),
            'type' => intval($type),
            'add_time' => time(),
            'status'   => 'wait',
            'order_id' => '6' . date('ymdHis') . substr(microtime(), 2, 5)
        ]);

        return $this->fetch_row('charge', 'id = ' . intval($charge_id));
    }

    public function set_ok_charge($order_id, $status = 1, $terrace_id = null){
        if(!is_digits($order_id)){
            return false;
        }
        if(!$charge = $this->fetch_row('charge', 'order_id = \'' . $order_id . '\'')){
            return false;
        }

        if($charge['status'] != 'wait'){
            return false;
        }

        if($status != 1){
            $this->update('charge', [
                'status' => 'close'
            ], 'order_id = \'' . $order_id . '\'');
        }else{
            $this->update('charge', [
                'status'   => 'ok',
                'pay_time' => time(),
                'terrace_id' => $terrace_id
            ], 'order_id = \'' . $order_id . '\'');
            $user = $this->fetch_row('users', 'uid = ' . $charge['uid']);
            if($charge['type'] == static::TYPE_AD){
                $user_data = [
                    'ad_cash' => $user['ad_cash'] + $charge['cash']
                ];
            }else{
                $user_data = [
                    'cash' => $user['cash'] + $charge['cash']
                ];
            }

            $this->update('users', $user_data, 'uid = ' . $charge['uid']);

            if($charge['type'] == static::TYPE_AD){
                $user_balance = $this->model('account')->fetch_one('users', 'ad_cash', 'uid = ' . intval($charge['uid']));
            }else{
                $user_balance = $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($charge['uid']));
            }

            $this->insert('finance', [
                'uid' => $charge['uid'],
                'add_time' => time(),
                'type'     => $charge['type'],
                'action'   => static::ACTION_IN,
                'item_type' => 'charge',
                'item_id'   => $charge['id'],
                'cash'      => $charge['cash'],
                'from_uid'  => intval($charge['uid']),
                'balance'   => $user_balance
            ]);
        }

        return true;
    }

    public function get_finance_list($uid = null, $type = null, $action = null, $page = 1, $pre_page = 20){
        $where = [];
        if($uid){
            $where[] = 'uid = ' . intval($uid);
        }
        if($type){
            $where[] = 'type = ' . intval($type);
        }
        if($action){
            $where[] = 'action = ' . intval($action);
        }

        $list = $this->fetch_page('finance', implode(' AND ', $where), 'id desc', $page, $pre_page);

        if($list){
            foreach ($list as $key => $val){
                $uids[$val['uid']] = $val['uid'];
                $uids[$val['from_uid']] = $val['from_uid'];
            }
            $users = $this->model('account')->get_user_info_by_uids($uids);
            foreach ($list as $key => $val){
                $list[$key]['user_info'] = $users[$val['uid']];
                $list[$key]['from_user'] = $users[$val['from_uid']];
                $item = $this->getItemType($val['item_type'], $val['item_id']);
                $list[$key]['title'] = $item['name'];
                $list[$key]['info']  = $item['info'];
                $list[$key]['link']  = $item['link'];
            }
        }

        return $list;
    }

    public function set_ok_withdraw($id, $status, $terrace_id = null){
        $withdraw = $this->model('finance')->fetch_row('withdraw', 'id = ' . intval($id));
        if (!$withdraw)
        {
            return false;
        }
        $user = $this->model('account')->get_user_info_by_uid($withdraw['uid']);
        if($withdraw['status'] != 'wait'){
            return true;
        }

        if($status != 'ok'){
            $this->update('withdraw', [
                'status' => 'close'
            ], 'id = ' . intval($id));

            if($withdraw['type'] == static::TYPE_SHANG){
                $user_update = [
                    'cash' => ($user['cash'] + $withdraw['cash'])
                ];
            }else{
                $user_update = [
                    'ad_cash' => ($user['ad_cash'] + $withdraw['cash'])
                ];
            }
            $this->update('users', $user_update, 'uid = ' . intval($withdraw['uid']));
        }else{
            $this->update('withdraw', [
                'status' => 'ok',
                'pay_time' => time(),
                'terrace_id' => $terrace_id
            ], 'id = ' . intval($id));
            if($withdraw['type'] == static::TYPE_SHANG){
                $user_balance = $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($withdraw['uid']));
            }else{
                $user_balance = $this->model('account')->fetch_one('users', 'ad_cash', 'uid = ' . intval($withdraw['uid']));
            }

            //记录流水
            $this->insert('finance', [
                'uid' => $withdraw['uid'],
                'add_time' => time(),
                'type'     => $withdraw['type'],
                'action'   => static::ACTION_OUT,
                'item_type' => 'withdraw',
                'item_id'   => $withdraw['id'],
                'cash'      => $withdraw['cash'],
                'from_uid'  => intval($withdraw['uid']),
                'balance'   => $user_balance
            ]);
        }
    }

    public function get_ad_flow_list($page = 1, $pre_page = 20, $where  = null){

        $list = $this->fetch_page('ad_log', $where, 'id desc', $page, $pre_page);

        if($list){
            foreach ($list as $key => $val){
                $uids[$val['uid']] = $val['uid'];
                $uids[$val['operator']] = $val['operator'];
                $ad[$val['ad_id']]      = $val['ad_id'];
            }
            $users = $this->model('account')->get_user_info_by_uids($uids);

            if($ad){
                $ad_list = $this->model('account')->fetch_all('ad', 'id IN(' . implode(',', $ad) . ')');
                foreach ($ad_list as $key => $val){
                    $ads[$val['id']] = $val;
                }
            }

            foreach ($list as $key => $val){
                $list[$key]['user_info'] = $users[$val['uid']];
                $list[$key]['operator_info'] = $users[$val['operator']];
                $list[$key]['ad_info'] = $ads[$val['ad_id']];
            }
        }

        return $list;
    }

    public function get_ad_click_list($page = 1, $pre_page = 20, $where = null){

        $list = $this->fetch_page('click_log', $where, 'id desc', $page, $pre_page);

        if($list){
            foreach ($list as $key => $val){
                $uids[$val['uid']] = $val['uid'];
                $ad[$val['ad_id']]      = $val['ad_id'];
                $uids[$val['show_uid']]      = $val['show_uid'];
            }
            $users = $this->model('account')->get_user_info_by_uids($uids);

            if($ad){
                $ad_list = $this->model('account')->fetch_all('ad', 'id IN(' . implode(',', $ad) . ')');
                foreach ($ad_list as $key => $val){
                    $ads[$val['id']] = $val;
                }
            }

            foreach ($list as $key => $val){
                $list[$key]['user_info'] = $users[$val['uid']];
                $list[$key]['show_user'] = $users[$val['show_uid']];
                $list[$key]['ad_info'] = $ads[$val['ad_id']];
                $list[$key]['click_location'] = $this->model('ad')->get_location($val['show_location']);
            }
        }

        return $list;
    }

    public function getItemType($item_type, $item_id){
        $result = [
            'name' => '',
            'info' => '',
        ];
        switch ($item_type){
            case 'answer':
                $result['name'] = '回复';
                $result['info'] = $this->fetch_row('answer', 'answer_id = ' . intval($item_id));
                $result['info']['title'] = cjk_substr(strip_tags(htmlspecialchars_decode($result['info']['answer_content'])), 0, 20);
                $result['link'] = get_js_url('/question/' . $result['info']['question_id'] . '?answer_id='.$item_id.'&single=true');
                break;
            case 'article':
                $result['name'] = '文章';
                $result['info'] = $this->fetch_row('article', 'id = ' . intval($item_id));
                $result['link'] = get_js_url('/article/' . $item_id);
                break;
            case 'sortout':
                $result['name'] = '整理';
                $result['info'] = $this->fetch_row('sortout', 'id = ' . intval($item_id));
                $result['link'] = get_js_url('/sortout/' . $item_id);
                break;
            case 'question':
                $result['name'] = '问题';
                $result['info'] = $this->fetch_row('question', 'question_id = ' . intval($item_id));
                $result['info']['title'] = cjk_substr($result['info']['question_content'], 0, 20);
                $result['link'] = get_js_url('/question/' . $item_id);
                break;
            case 'charge':
                $result['name'] = '充值';
                break;
            case 'withdraw':
                $result['name'] = '提现';
                break;
            case 'ad':
                $result['name'] = '广告';
                $result['info'] = $this->fetch_row('ad', 'id = ' . intval($item_id));
                $result['link'] = get_js_url('/info/' . $item_id);
                break;
            case 'article_comment':
                $result['name'] = '文章评论';
                $result['info'] = $this->fetch_row('article_comments', 'id = ' . intval($item_id));
                $result['info']['title'] = cjk_substr(strip_tags(htmlspecialchars_decode($result['info']['message'])), 0, 20);
                $result['link'] = get_js_url('/article/' . $result['info']['article_id']);
                break;
            case 'sortout_comment':
                $result['name'] = '整理文章评论';
                $result['info'] = $this->fetch_row('sortout_comments', 'id = ' . intval($item_id));
                $result['info']['title'] = cjk_substr(strip_tags(htmlspecialchars_decode($result['info']['message'])), 0, 20);
                $result['link'] = get_js_url('/sortout/' . $result['info']['article_id']);
                break;
        }

        return $result;
    }
}