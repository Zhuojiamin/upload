<?php
!function_exists('readover') && exit('Forbidden');

function MakeButton($seller,$subject,$price,$ordinary_fee,$express_fee,$method){
	global $timestamp,$db_blogurl,$db_charset;
	if ($method==1) {
		$str .= "<a href=\"https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=".rawurlencode(str_replace('&#46;','.',$seller))."&item_name=".rawurlencode($subject)."&item_number=phpw*&amount=$price&no_shipping=0&no_note=1&currency_code=CNY&notify_url=http://www.phpwind.com/pay/payto.php?date=".$_SERVER['HTTP_HOST'].get_date($timestamp,'-YmdHis')."&bn=phpwind&charset=$db_charset\" target=\"_blank\">贝宝支付</a>";
	} elseif ($method==2) {
		$str .= "<a href=\"https://www.alipay.com/payto:$seller?subject=".rawurlencode($subject)."&body=".rawurlencode($body)."&price=$price&ordinary_fee=$ordinary_fee&express_fee=$express_fee&partner=8868&readonly=true\" target=\"_blank\">支付宝支付</a>";
	} elseif ($method==3) {
		$str .= "<a href=\"$db_blogurl/pay99bill.php?merchant_id=$seller&orderid=$timestamp&amount=$price&commodity_info=".rawurlencode($subject)."\">快钱支付</a>";
	}
	return $str;
}
?>