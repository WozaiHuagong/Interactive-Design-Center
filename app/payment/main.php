<?php

/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2017/3/28
 * Time: 下午5:57
 */

class main extends AWS_CONTROLLER
{
    public function get_access_rule()
    {
        $rule_action['rule_type'] = "black"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查

        $rule_action['actions'] = array(
            'charge',
            'withdraw',
        );

        return $rule_action;
    }

    public function setup() {}

    public function charge_action(){
        if($_GET['type'] == 'shang'){
            $crumb_name = '打赏充值';
        }else{
            $crumb_name = '广告充值';
        }
        //$this->model('finance')->set_ok_charge('617041915113614277', 1, 111131);
        TPL::assign('crumb_name', $crumb_name);
        $this->crumb($crumb_name, '/payment/charge');
        TPL::output('payment/charge');
    }

    public function pay_action(){
        if(!is_digits($_GET['id'])){
            H::redirect_msg('交易失败，如有疑问请联系客服人员');
        }
        if(!$charge = $this->model('finance')->fetch_row('charge', 'order_id = \'' . $_GET['id'] . '\'')){
            H::redirect_msg('交易失败，如有疑问请联系客服人员');
        }
        if($charge['status'] != 'wait'){
            H::redirect_msg('交易失败，如有疑问请联系客服人员, 订单号: ' . $_GET['id']);
        }

        TPL::assign('notify_url', get_js_url('/payment/notify/alipay/'));
        TPL::assign('partner', get_setting('alipay_partner'));
        TPL::assign('seller_email', get_setting('alipay_seller_email'));
        TPL::assign('order_id', $charge['order_id']);
        TPL::assign('amount', number_format($charge['cash'] / 100,2));
        TPL::assign('return_url', get_js_url('/payment/callback/alipay/'));
        TPL::assign('order_name', 'yesfoo');
        TPL::assign('show_url', base_url());
        TPL::assign('sign', $this->model('payment_alipay')->createSign($charge['order_id'], 'yesfoo', number_format($charge['cash'] / 100,2), get_js_url('/payment/callback/alipay/')));

        TPL::output('payment/pay');
    }

    public function withdraw_action(){

        if($_GET['type'] == 'shang'){
            $crumb_name = '打赏提现';
            $cash = $this->user_info['cash'];
        }else{
            $crumb_name = '广告提现';
            $cash = $this->user_info['ad_cash'];
        }

        TPL::assign('cash', number_format($cash/100,2));
        TPL::assign('crumb_name', $crumb_name);
        $this->crumb($crumb_name, '/payment/withdraw/');

        TPL::output('payment/withdraw');
    }
}