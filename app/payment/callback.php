<?php
/*
+--------------------------------------------------------------------------
|   Anwsion [#RELEASE_VERSION#]
|   ========================================
|   by Anwsion dev team
|   (c) 2011 - 2012 Anwsion Software
|   http://www.anwsion.com
|   ========================================
|   Support: zhengqiang@gmail.com
|   
+---------------------------------------------------------------------------
*/

class callback extends AWS_CONTROLLER
{
	public $callback_url = '/';
	
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'black';
		$rule_action['actions'] = array();
		
		return $rule_action;
	}
	
	public function setup()
	{
		HTTP::no_cache_header();
	}

	public function alipay_action()
	{		
		$result = $this->model('payment_alipay')->verifyReturn();
        $order = $this->model('finance')->fetch_row('charge', 'order_id = \'' . $_GET['out_trade_no'] . '\'');


        if ($result AND $_GET['total_fee'] == ($order['cash'] / 100))
		{
            if ($order['status'] == 'wait')
            {
                $this->model('finance')->set_ok_charge($order['order_id'], 1, $_GET['trade_no']);
            }

			if($order['type'] == finance_class::TYPE_SHANG){
			    $callback_url = get_js_url('/home/finance/type-shang');
            }else{
                $callback_url = get_js_url('/home/finance/type-ad');
            }
			
			H::redirect_msg('支付成功, 交易金额: ' . $order['cash'] / 100, $callback_url);
		}
		else
		{
			H::redirect_msg('交易失败，如有疑问请联系客服人员，支付宝订单编号：' . $_GET['out_trade_no']);
		}
	}
}