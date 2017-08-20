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

class notify extends AWS_CONTROLLER
{

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
		$result = $this->model('payment_alipay')->verifyNotify();

		$order = $this->model('finance')->fetch_row('charge', 'order_id = \'' . $_POST['out_trade_no'] . '\'');

		if ($result AND $_POST['total_fee'] == ($order['cash'] / 100))
		{
			if ($order['status'] == 'wait')
			{
			    $this->model('finance')->set_ok_charge($order['order_id'], 1, $_POST['trade_no']);
			}

			$result = 'success';
		}
		else
		{
			$result = 'fail';
		}

		exit($result);
	}
}