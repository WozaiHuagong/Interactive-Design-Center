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

class ajax extends AWS_CONTROLLER
{
    public function get_access_rule()
    {
        $rule_action['rule_type'] = "white"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查

        $rule_action['actions'] = array(

        );

        return $rule_action;
    }

    public function setup()
    {
        HTTP::no_cache_header();
    }

    public function save_charge_action(){
        if($_POST['cash'] < 1){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('充值金额不能小于1元')));
        }

        if($_POST['type'] == 'shang'){
            $type = finance_class::TYPE_SHANG;
        }else{
            $type = finance_class::TYPE_AD;
        }

        $charge = $this->model('finance')->save_charge($this->user_id, $_POST['cash'] * 100, $type);

        H::ajax_json_output(AWS_APP::RSM([
            'url' => get_js_url('/payment/pay/' . $charge['order_id'])
        ], 1, null));
    }

    public function save_withdraw_action(){
        if(!$this->user_info['verified']){
            H::redirect_msg(AWS_APP::lang()->_t('发布广告需要认证,请先认证再来发布'), get_js_url('/account/setting/verify/'));
            H::ajax_json_output(AWS_APP::RSM([
                'url' => get_js_url('account/setting/verify/')
            ], '1', AWS_APP::lang()->_t('提现需要认证,请先认证再申请提现')));
        }
        if($_POST['cash'] < 1){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('提现金额不能小于1元')));
        }

        if(!$_POST['pay_account'] OR !$_POST['pay_name']){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('收款账号不能为空哦')));
        }

        if($_POST['type'] == 'shang'){
            $type = finance_class::TYPE_SHANG;
            $cash = $this->user_info['cash'];
            $withdraw_percent = get_setting('shang_withdraw_percent');
        }else{
            $type = finance_class::TYPE_AD;
            $cash = $this->user_info['ad_cash'];
            $withdraw_percent = get_setting('ad_withdraw_percent');
        }

        if(($_POST['cash'] * 100) > $cash){
            H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('提现金额不能超过' . ($cash/100) . '元')));
        }

        //增加如果没有广告收入,只提现余额,则不收手续费
        $has_ad_income = $this->model('ad')->count('finance', 'uid = ' . $this->user_id . ' AND action = ' . finance_class::ACTION_IN . ' AND type = ' . finance_class::TYPE_AD . ' AND item_type = \'ad\'');
        $has_ad_output = $this->model('ad')->count('finance', 'uid = ' . $this->user_id . ' AND action = ' . finance_class::ACTION_OUT . ' AND type = ' . finance_class::TYPE_AD . ' AND item_type = \'ad\'');
        if(!$has_ad_income AND $has_ad_output){
            $real_cash = ($_POST['cash'] * 100);
        }else{
            $real_cash = $_POST['cash'] * (100 - $withdraw_percent);
        }


        $withdraw_id = $this->model('finance')->insert('withdraw', [
            'uid' => $this->user_id,
            'type' => $type,
            'add_time' => time(),
            'cash'     => ($_POST['cash'] * 100),
            'real_cash' => $real_cash,
            'status'   => 'wait',
            'info'     => htmlspecialchars($_POST['info']),
            'pay_account' => htmlspecialchars($_POST['pay_account']),
            'pay_name' => htmlspecialchars($_POST['pay_name']),
            'order_id' => '8' . date('ymdHis') . substr(microtime(), 2, 5),
        ]);
        //
        if($withdraw_id){
            if($_POST['type'] == 'shang'){
                $user_update = [
                    'cash' => ($cash - ($_POST['cash'] * 100))
                ];
            }else{
                $user_update = [
                    'ad_cash' => ($cash - ($_POST['cash'] * 100))
                ];
            }
            $this->model('account')->update('users', $user_update, 'uid = ' . $this->user_id);
        }

        H::ajax_json_output(AWS_APP::RSM([
            'url' => get_js_url('/home/finance/type-' . $_POST['type'])
        ], 1, null));
    }
}
