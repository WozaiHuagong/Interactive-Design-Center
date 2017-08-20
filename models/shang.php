<?php

/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2017/4/13
 * Time: 上午10:42
 */
class shang_class extends AWS_MODEL
{
    /**
     * @var $action = [1,2];1添加,2,更改
     */
    /**
     * @param int $question_id 问题id
     * @param int $uid 用户名
     * @param float $question_bounty 赏金,单位分
     * @return boolean
     */
    public function shang_question($question_id, $uid, $question_bounty){
        $question = $this->fetch_row('question', 'question_id = ' . intval($question_id));
        $user     = $this->fetch_row('users', 'uid = ' . intval($uid));
        $shang = $this->fetch_row('question_shang', 'type = \'question\' AND question_id = ' . $question['question_id'] . ' AND uid = ' . $user['uid']);
        if(!$question OR !$user){
            return false;
        }

        //没变化
        if($question_bounty == $shang['cash']){
            return true;
        }

        $cash = $question_bounty - $shang['cash'];
        if($user['cash'] - $cash < 0){
            return false;
        }
        
        if($shang){
            if($shang['status'] != 0){
                return false;
            }
            $this->update('question_shang', [
                'update_time' => time(),
                'cash'        => intval($question_bounty),
            ], 'id = ' . $shang['id']);
            $shang_id = $shang['id'];
            $action = 2;
        }else{
            $shang_id = $this->insert('question_shang', [
                'question_id' => $question['question_id'],
                'uid'         => $user['uid'],
                'add_time'    => time(),
                'status'      => 0,
                'sortout_id'  => 0,
                'update_time' => time(),
                'cash'        => intval($question_bounty),
                'type'        => 'question'
            ]);
            $action = 1;
        }

        //更新用户打赏余额
        $this->update('users', [
            'cash' => $user['cash'] - $cash
        ], 'uid = ' . $user['uid']);

        //记录到日志
        $this->insert('question_shang_log', [
            'question_id' => $question['question_id'],
            'uid'         => $user['uid'],
            'add_time'    => time(),
            'sortout_id'  => 0,
            'cash'        => intval($question_bounty),
            'action'      => $action
        ]);
        //更新问题的赏金
        $this->update('question', [
            'question_bounty' => $this->sum('question_shang', 'cash', 'type = \'question\' AND status = 0 AND sortout_id = 0 AND question_id = ' . $question['question_id'])
        ], 'question_id = ' . $question['question_id']);
        //记录资金流动到finance表
        //记录流水
        if($cash < 0){
            //赏金被减少,用户余额增加
            $finance_action = finance_class::ACTION_IN;
        }else{
            $finance_action = finance_class::ACTION_OUT;
        }
        $this->insert('finance', [
            'uid' => $user['uid'],
            'add_time' => time(),
            'type'     => finance_class::TYPE_SHANG,
            'action'   => $finance_action,
            'item_type' => 'question',
            'item_id'   => $question['question_id'],
            'cash'      => abs($cash),
            'from_uid'  => intval($user['uid']),
            'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($user['uid']))
        ]);

        return true;
    }

    public function shang_user($uid, $cash, $from_uid, $from_shang_id, $item_type, $item_id){
        if($from_shang_id){
            $this->insert('shang', [
                'uid' => $uid,
                'add_time' => time(),
                'cash'      => $cash,
                'item_type' => $item_type,
                'item_id'   => $item_id,
                'from_uid'  => $from_uid,
                'from_question_shang' => $from_shang_id,
            ]);
            //增加用户金额
            $user = $this->fetch_row('users', 'uid = ' . intval($uid));
            $this->update('users', [
                'cash' => $user['cash'] + $cash
            ], 'uid = ' . intval($uid));
            //记录到流水
            $this->insert('finance', [
                'uid' => $user['uid'],
                'add_time' => time(),
                'type'     => finance_class::TYPE_SHANG,
                'action'   => finance_class::ACTION_IN,
                'item_type' => $item_type,
                'item_id'   => $item_id,
                'cash'      => $cash,
                'from_uid'  => intval($from_uid),
                'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($user['uid']))
            ]);
        }else{
            //单纯的打赏回复,需要分配部分金额给提问者
            //判断作者是否打赏了,如果打赏了,用参数x,没打上用参数y
            switch ($item_type){
                case 'answer':
                    $question_info = $this->fetch_row('question', 'question_id = ' . intval($item_id));
                    $question_shang = $this->fetch_row('question_shang', 'cash > 0 AND status = 1 AND question_id = ' . $question_info['question_id'] . ' AND uid = ' . $question_info['published_uid']);
                    //扣除打赏者金额
                    $total_cash = 0;
                    foreach ($cash as $key => $val){
                        $total_cash += $val;
                    }
                    $total_cash = intval($total_cash*100);
                    //算钱
                    $shang_user = $this->fetch_row('users', 'uid = ' . intval($from_uid));
                    if($shang_user['cash'] < $total_cash){
                        return false;
                    }
                    //获得一次y权限
                    $this->update('users', [
                        'cash' => $shang_user['cash'] - $total_cash,
                    //    'y_permission' => $shang_user['y_permission'] + 1
                    ], 'uid = ' . intval($shang_user['uid']));

                    $shang_id = $this->insert('question_shang', [
                        'question_id' => $question_info['question_id'],
                        'uid'         => $from_uid,
                        'add_time'    => time(),
                        'status'      => 1,
                        'sortout_id'  => 0,
                        'update_time' => time(),
                        'cash'        => $total_cash,
                        'type'        => 'answer'
                    ]);
                    //打赏是2
                    $action = 1;

                    //记录到日志
                    $this->insert('question_shang_log', [
                        'question_id' => $question_info['question_id'],
                        'uid'         => $from_uid,
                        'add_time'    => time(),
                        'sortout_id'  => 0,
                        'cash'        => $total_cash,
                        'action'      => $action
                    ]);
                    //记录到流水
                    $this->insert('finance', [
                        'uid' => $shang_user['uid'],
                        'add_time' => time(),
                        'type'     => finance_class::TYPE_SHANG,
                        'action'   => finance_class::ACTION_OUT,
                        'item_type' => 'question',
                        'item_id'   => $item_id,
                        'cash'      => $total_cash,
                        'from_uid'  => intval($from_uid),
                        'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($shang_user['uid']))
                    ]);

                    foreach ($cash as $key => $val){
                        //打赏
                        //shang_user($uid, $cash, $from_uid, $from_shang_id, $item_type, $item_id)
                        //$this->model('shang')->shang_user($user_ids[$key], $val*100, $this->user_id, 0, 'answer', $key);
                        $answer_info = $this->model('answer')->get_answer_by_id($key);
                        $publisher = $this->fetch_row('users', 'uid = ' . intval($question_info['published_uid']));
                        $val_cash = intval($val*100);
                        $shang_percent = get_setting('shang_y');
                        $min_cash = $val_cash;
                        if($question_shang['cash'] > 0 OR $question_info['use_y']){
                            $shang_percent = get_setting('shang_x');
                            if($question_shang['cash']){
                                $shang_cash = $question_shang['cash'];
                            }
                            if($question_info['use_y'] AND $question_shang['cash'] < $publisher['avg_shang']){
                                $shang_cash = $publisher['avg_shang'];
                            }
                        }
                        if($shang_cash AND $shang_cash < $val_cash){
                            $min_cash = $shang_cash;
                        }
                        $publisher_cash = $min_cash*$shang_percent/100;
                        $answerer_cash  = $val_cash - $publisher_cash;
                        $publisher = $this->fetch_row('users', 'uid = ' . intval($question_info['published_uid']));
                        //分给提问者
                        $this->insert('shang', [
                            'uid' => $question_info['published_uid'],
                            'add_time' => time(),
                            'cash'      => $publisher_cash,
                            'item_type' => 'question',
                            'item_id'   => $key,
                            'from_uid'  => $from_uid,
                            'from_question_shang' => $shang_id,
                        ]);
                        //增加用户金额
                        $this->update('users', [
                            'cash' => $publisher['cash'] + $publisher_cash
                        ], 'uid = ' . intval($question_info['published_uid']));
                        //记录到流水
                        $this->insert('finance', [
                            'uid' => $question_info['published_uid'],
                            'add_time' => time(),
                            'type'     => finance_class::TYPE_SHANG,
                            'action'   => finance_class::ACTION_IN,
                            'item_type' => $item_type,
                            'item_id'   => $key,
                            'cash'      => $publisher_cash,
                            'from_uid'  => intval($from_uid),
                            'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($question_info['published_uid']))
                        ]);

                        //分给回答者
                        $this->insert('shang', [
                            'uid' => $uid[$key],
                            'add_time' => time(),
                            'cash'      => $answerer_cash,
                            'item_type' => $item_type,
                            'item_id'   => $key,
                            'from_uid'  => $from_uid,
                            'from_question_shang' => $shang_id,
                        ]);
                        //增加用户金额
                        $answerer = $this->fetch_row('users', 'uid = ' . intval($uid[$key]));
                        $this->update('users', [
                            'cash' => $answerer['cash'] + $answerer_cash
                        ], 'uid = ' . intval($uid[$key]));
                        //记录到流水
                        $this->insert('finance', [
                            'uid' => $answerer['uid'],
                            'add_time' => time(),
                            'type'     => finance_class::TYPE_SHANG,
                            'action'   => finance_class::ACTION_IN,
                            'item_type' => $item_type,
                            'item_id'   => $key,
                            'cash'      => $answerer_cash,
                            'from_uid'  => intval($from_uid),
                            'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($answerer['uid']))
                        ]);
                    }
                    break;
                case 'sortout':
                    $sortout = $this->fetch_row('sortout', 'id = ' . intval($item_id));
                    $question_info = $this->fetch_row('question', 'question_id = ' . $sortout['question_id']);
                    $question_shang = $this->fetch_row('question_shang', 'id = ' . intval($sortout['shang_id']));
                    //扣除打赏者金额
                    $shang_user = $this->fetch_row('users', 'uid = ' . intval($from_uid));
                    if($shang_user['cash'] < $cash){
                        return false;
                    }
                    //获得一次y权限
                    $this->update('users', [
                        'cash' => $shang_user['cash'] - $cash,
                        'y_permission' => $shang_user['y_permission'] + 1
                    ], 'uid = ' . intval($shang_user['uid']));

                    $shang_id = $this->insert('question_shang', [
                        'question_id' => 0,
                        'uid'         => $from_uid,
                        'add_time'    => time(),
                        'status'      => 1,
                        'sortout_id'  => $sortout['id'],
                        'update_time' => time(),
                        'cash'        => $cash,
                        'type'        => 'sortout'
                    ]);
                    //打赏是2
                    $action = 1;

                    //记录到日志
                    $this->insert('question_shang_log', [
                        'question_id' => 0,
                        'uid'         => $from_uid,
                        'add_time'    => time(),
                        'sortout_id'  => 0,
                        'cash'        => $cash,
                        'action'      => $action
                    ]);
                    //记录到流水
                    $this->insert('finance', [
                        'uid' => $shang_user['uid'],
                        'add_time' => time(),
                        'type'     => finance_class::TYPE_SHANG,
                        'action'   => finance_class::ACTION_OUT,
                        'item_type' => $item_type,
                        'item_id'   => $item_id,
                        'cash'      => $cash,
                        'from_uid'  => intval($from_uid),
                        'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($shang_user['uid']))
                    ]);
                    $publisher = $this->fetch_row('users', 'uid = ' . intval($sortout['uid']));

                    if($sortout['uid'] == $question_info['published_uid'] AND $question_info['use_y']){
                        //y权限,全收
                        $this->insert('shang', [
                            'uid' => $question_info['published_uid'],
                            'add_time' => time(),
                            'cash'      => $cash,
                            'item_type' => $item_type,
                            'item_id'   => $item_id,
                            'from_uid'  => $from_uid,
                            'from_question_shang' => $shang_id,
                        ]);
                        //增加用户金额
                        $this->update('users', [
                            'cash' => $publisher['cash'] + $cash
                        ], 'uid = ' . intval($question_info['published_uid']));
                        //记录到流水
                        $this->insert('finance', [
                            'uid' => $question_info['published_uid'],
                            'add_time' => time(),
                            'type'     => finance_class::TYPE_SHANG,
                            'action'   => finance_class::ACTION_IN,
                            'item_type' => $item_type,
                            'item_id'   => $item_id,
                            'cash'      => $cash,
                            'from_uid'  => intval($from_uid),
                            'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($question_info['published_uid']))
                        ]);
                    }else{
                        //正常分配
                        $shang_percent = get_setting('shang_w');
                        $min_cash = $cash;
                        if($question_shang['cash'] > 0 AND $question_shang['cash'] < $cash){
                            $min_cash = $question_shang['cash'];
                        }
                        $publisher_cash = $min_cash*$shang_percent/100;
                        $answerer_cash  = $cash - $publisher_cash;

                        //分给整理者
                        $this->insert('shang', [
                            'uid' => $sortout['uid'],
                            'add_time' => time(),
                            'cash'      => $publisher_cash,
                            'item_type' => $item_type,
                            'item_id'   => $item_id,
                            'from_uid'  => $from_uid,
                            'from_question_shang' => $shang_id,
                        ]);
                        //增加用户金额
                        $this->update('users', [
                            'cash' => $publisher['cash'] + $publisher_cash
                        ], 'uid = ' . intval($sortout['uid']));
                        //记录到流水
                        $this->insert('finance', [
                            'uid' => $sortout['uid'],
                            'add_time' => time(),
                            'type'     => finance_class::TYPE_SHANG,
                            'action'   => finance_class::ACTION_IN,
                            'item_type' => $item_type,
                            'item_id'   => $item_id,
                            'cash'      => $publisher_cash,
                            'from_uid'  => intval($from_uid),
                            'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($sortout['uid']))
                        ]);

                        //分给回答者,打赏过的
                        /*$answer_list = $this->model('answer')->fetch_all('answer', 'shang_cash > 0 AND question_id = ' . $question_info['question_id']);
                        $total_answer_shang = 0;
                        foreach ($answer_list as $key => $val){
                            $answer_uids[$val['uid']] = $val['uid'];
                            $total_answer_shang += $val['shang_cash'];
                        }*/
                        $shang_list = $this->fetch_all('shang', 'cash > 0 AND item_type = \'answer\' and from_uid = ' . intval($sortout['uid']) . ' AND from_question_shang = ' . $question_shang['id']);
                        $total_answer_shang = 0;
                        foreach ($shang_list as $key => $val){
                            $answer_uids[$val['uid']] = $val['uid'];
                            $total_answer_shang += $val['cash'];
                        }

                        //$answer_users = $this->model('account')->get_user_info_by_uids($answer_uids, true);
                        foreach ($shang_list as $key => $val){
                            $answer_user =  $this->fetch_row('users', 'uid = ' . intval($val['uid']));
                            $answer_shang = $answerer_cash/$total_answer_shang*$val['cash'];

                            $this->insert('shang', [
                                'uid' => $answer_user['uid'],
                                'add_time' => time(),
                                'cash'      => $answer_shang,
                                'item_type' => $item_type,
                                'item_id'   => $item_id,
                                'from_uid'  => $from_uid,
                                'from_question_shang' => $shang_id,
                            ]);
                            //增加用户金额
                            $this->update('users', [
                                'cash' => $answer_user['cash'] + $answer_shang
                            ], 'uid = ' . intval($answer_user['uid']));
                            //记录到流水
                            $this->insert('finance', [
                                'uid' => $answer_user['uid'],
                                'add_time' => time(),
                                'type'     => finance_class::TYPE_SHANG,
                                'action'   => finance_class::ACTION_IN,
                                'item_type' => $item_type,
                                'item_id'   => $item_id,
                                'cash'      => $answer_shang,
                                'from_uid'  => intval($from_uid),
                                'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($answer_user['uid']))
                            ]);
                        }

                    }

                    break;
                case 'article':
                    $article_info = $this->fetch_row('article', 'id = ' . intval($item_id));

                    //扣除打赏者金额
                    $shang_user = $this->fetch_row('users', 'uid = ' . intval($from_uid));
                    if($shang_user['cash'] < $cash){
                        return false;
                    }
                    //获得一次y权限
                    $this->update('users', [
                        'cash' => $shang_user['cash'] - $cash,
                        'y_permission' => $shang_user['y_permission'] + 1,
                    ], 'uid = ' . intval($shang_user['uid']));
                    //记录到流水
                    $this->insert('finance', [
                        'uid' => $shang_user['uid'],
                        'add_time' => time(),
                        'type'     => finance_class::TYPE_SHANG,
                        'action'   => finance_class::ACTION_OUT,
                        'item_type' => $item_type,
                        'item_id'   => $item_id,
                        'cash'      => $cash,
                        'from_uid'  => intval($from_uid),
                        'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($shang_user['uid']))
                    ]);

                    $publisher = $this->fetch_row('users', 'uid = ' . intval($article_info['uid']));
                    //分给作者
                    $this->insert('shang', [
                        'uid' => $publisher['uid'],
                        'add_time' => time(),
                        'cash'      => $cash,
                        'item_type' => $item_type,
                        'item_id'   => $item_id,
                        'from_uid'  => $from_uid,
                        'from_question_shang' => 0,
                    ]);
                    //增加用户金额
                    $this->update('users', [
                        'cash' => $publisher['cash'] + $cash
                    ], 'uid = ' . intval($publisher['uid']));
                    //记录到流水
                    $this->insert('finance', [
                        'uid' => $publisher['uid'],
                        'add_time' => time(),
                        'type'     => finance_class::TYPE_SHANG,
                        'action'   => finance_class::ACTION_IN,
                        'item_type' => $item_type,
                        'item_id'   => $item_id,
                        'cash'      => $cash,
                        'from_uid'  => intval($from_uid),
                        'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($publisher['uid']))
                    ]);
                    break;
                case 'article_comment':
                    $comment_info = $this->fetch_row('article_comments', 'id = ' . intval($item_id));

                    //扣除打赏者金额
                    $shang_user = $this->fetch_row('users', 'uid = ' . intval($from_uid));
                    if($shang_user['cash'] < $cash){
                        return false;
                    }
                    //获得一次y权限
                    $this->update('users', [
                        'cash' => $shang_user['cash'] - $cash,
                    //    'y_permission' => $shang_user['y_permission'] + 1,
                    ], 'uid = ' . intval($shang_user['uid']));
                    //记录到流水
                    $this->insert('finance', [
                        'uid' => $shang_user['uid'],
                        'add_time' => time(),
                        'type'     => finance_class::TYPE_SHANG,
                        'action'   => finance_class::ACTION_OUT,
                        'item_type' => $item_type,
                        'item_id'   => $item_id,
                        'cash'      => $cash,
                        'from_uid'  => intval($from_uid),
                        'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($shang_user['uid']))
                    ]);

                    $publisher = $this->fetch_row('users', 'uid = ' . intval($comment_info['uid']));
                    //分给作者
                    $this->insert('shang', [
                        'uid' => $publisher['uid'],
                        'add_time' => time(),
                        'cash'      => $cash,
                        'item_type' => $item_type,
                        'item_id'   => $item_id,
                        'from_uid'  => $from_uid,
                        'from_question_shang' => 0,
                    ]);
                    //增加用户金额
                    $this->update('users', [
                        'cash' => $publisher['cash'] + $cash
                    ], 'uid = ' . intval($publisher['uid']));
                    //记录到流水
                    $this->insert('finance', [
                        'uid' => $publisher['uid'],
                        'add_time' => time(),
                        'type'     => finance_class::TYPE_SHANG,
                        'action'   => finance_class::ACTION_IN,
                        'item_type' => $item_type,
                        'item_id'   => $item_id,
                        'cash'      => $cash,
                        'from_uid'  => intval($from_uid),
                        'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($publisher['uid']))
                    ]);
                    break;
                case 'sortout_comment':
                    $comment_info = $this->fetch_row('sortout_comments', 'id = ' . intval($item_id));

                    //扣除打赏者金额
                    $shang_user = $this->fetch_row('users', 'uid = ' . intval($from_uid));
                    if($shang_user['cash'] < $cash){
                        return false;
                    }
                    //获得一次y权限
                    $this->update('users', [
                        'cash' => $shang_user['cash'] - $cash,
                    //    'y_permission' => $shang_user['y_permission'] + 1,
                    ], 'uid = ' . intval($shang_user['uid']));
                    //记录到流水
                    $this->insert('finance', [
                        'uid' => $shang_user['uid'],
                        'add_time' => time(),
                        'type'     => finance_class::TYPE_SHANG,
                        'action'   => finance_class::ACTION_OUT,
                        'item_type' => $item_type,
                        'item_id'   => $item_id,
                        'cash'      => $cash,
                        'from_uid'  => intval($from_uid),
                        'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($shang_user['uid']))
                    ]);

                    $publisher = $this->fetch_row('users', 'uid = ' . intval($comment_info['uid']));
                    //分给作者
                    $this->insert('shang', [
                        'uid' => $publisher['uid'],
                        'add_time' => time(),
                        'cash'      => $cash,
                        'item_type' => $item_type,
                        'item_id'   => $item_id,
                        'from_uid'  => $from_uid,
                        'from_question_shang' => 0,
                    ]);
                    //增加用户金额
                    $this->update('users', [
                        'cash' => $publisher['cash'] + $cash
                    ], 'uid = ' . intval($publisher['uid']));
                    //记录到流水
                    $this->insert('finance', [
                        'uid' => $publisher['uid'],
                        'add_time' => time(),
                        'type'     => finance_class::TYPE_SHANG,
                        'action'   => finance_class::ACTION_IN,
                        'item_type' => $item_type,
                        'item_id'   => $item_id,
                        'cash'      => $cash,
                        'from_uid'  => intval($from_uid),
                        'balance'   => $this->model('account')->fetch_one('users', 'cash', 'uid = ' . intval($publisher['uid']))
                    ]);
                    break;
            }
        }

        //更新回复打赏数量
        switch ($item_type){
            case 'answer':
                if(is_array($cash)){
                    foreach ($cash as $key => $val){
                        $this->update('answer', [
                            'shang_cash' => $this->sum('shang', 'cash', "item_type = 'answer' AND item_id = " . intval($key)),
                        ], 'answer_id = ' . intval($key));
                    }
                }else{
                    $this->update('answer', [
                        'shang_cash' => $this->sum('shang', 'cash', "item_type = 'answer' AND item_id = " . intval($item_id)),
                    ], 'answer_id = ' . intval($item_id));
                }

                break;
            case 'sortout':
                //更新整理文章的打赏金额
                $this->update('sortout', [
                    'shang_cash' => $this->sum('question_shang', 'cash', 'type = \'sortout\' AND sortout_id = ' . intval($item_id))
                ], 'id = ' . intval($item_id));

                //计算打赏平均值
                $this->update('users', [
                    'avg_shang'    => intval($this->sum('finance', 'cash', 'type = ' . finance_class::TYPE_SHANG . ' AND action = ' . finance_class::ACTION_OUT . ' AND (item_type = \'article\' OR item_type = \'sortout\') AND uid = ' . intval($from_uid)) / $this->count('finance', 'type = ' . finance_class::TYPE_SHANG . ' AND action = ' . finance_class::ACTION_OUT . ' AND (item_type = \'article\' OR item_type = \'sortout\') AND uid = ' . intval($from_uid)))
                ], 'uid = ' . intval($from_uid));
                break;
            case 'article':
                //更新整理文章的打赏金额
                $this->update('article', [
                    'shang_cash' => $this->sum('shang', 'cash', "item_type = 'article' AND item_id = " . intval($item_id))
                ], 'id = ' . intval($item_id));

                //计算打赏平均值
                $this->update('users', [
                    'avg_shang'    => intval($this->sum('finance', 'cash', 'type = ' . finance_class::TYPE_SHANG . ' AND action = ' . finance_class::ACTION_OUT . ' AND (item_type = \'article\' OR item_type = \'sortout\') AND uid = ' . intval($from_uid)) / $this->count('finance', 'type = ' . finance_class::TYPE_SHANG . ' AND action = ' . finance_class::ACTION_OUT . ' AND (item_type = \'article\' OR item_type = \'sortout\') AND uid = ' . intval($from_uid)))
                ], 'uid = ' . intval($from_uid));
                break;
            case 'article_comment':
                //更新整理文章的打赏金额
                $this->update('article_comments', [
                    'shang_cash' => $this->sum('shang', 'cash', "item_type = 'article_comment' AND item_id = " . intval($item_id))
                ], 'id = ' . intval($item_id));
                break;
            case 'sortout_comment':
                //更新整理文章的打赏金额
                $this->update('sortout_comments', [
                    'shang_cash' => $this->sum('shang', 'cash', "item_type = 'sortout_comment' AND item_id = " . intval($item_id))
                ], 'id = ' . intval($item_id));
                break;
        }

        return true;
    }
}