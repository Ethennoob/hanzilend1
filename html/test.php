<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>Examples</title>
<meta name="description" content="">
<meta name="keywords" content="">
<link href="" rel="stylesheet">
</head>
<body>
<br />
<font size='1'><table class='xdebug-error xe-uncaught-exception' dir='ltr' border='1' cellspacing='0' cellpadding='1'>
<tr><th align='left' bgcolor='#f57900' colspan="5"><span style='background-color: #cc0000; color: #fce94f; font-size: x-large;'>( ! )</span> Fatal error: Uncaught exception 'Exception' with message '查询语句：select `id`,`withdraw_name`,`user_id`,`alipay`,`openid`,`money`,`add_time`,`confirm_time` from `withdrawals_apply` where  is_on = 1 and concat(alipay,withdrawals_name,money) like "%马莲%" order by add_time asc limit 0, 15     错误提示：Unknown column 'withdraw_name' in 'field list'' in E:\wamp\www\hanzilend\System\database\BaseTable.class.php on line <i>945</i></th></tr>
<tr><th align='left' bgcolor='#f57900' colspan="5"><span style='background-color: #cc0000; color: #fce94f; font-size: x-large;'>( ! )</span> Exception: 查询语句：select `id`,`withdraw_name`,`user_id`,`alipay`,`openid`,`money`,`add_time`,`confirm_time` from `withdrawals_apply` where  is_on = 1 and concat(alipay,withdrawals_name,money) like "%马莲%" order by add_time asc limit 0, 15     错误提示：Unknown column 'withdraw_name' in 'field list' in E:\wamp\www\hanzilend\System\database\BaseTable.class.php on line <i>945</i></th></tr>
<tr><th align='left' bgcolor='#e9b96e' colspan='5'>Call Stack</th></tr>
<tr><th align='center' bgcolor='#eeeeec'>#</th><th align='left' bgcolor='#eeeeec'>Time</th><th align='left' bgcolor='#eeeeec'>Memory</th><th align='left' bgcolor='#eeeeec'>Function</th><th align='left' bgcolor='#eeeeec'>Location</th></tr>
<tr><td bgcolor='#eeeeec' align='center'>1</td><td bgcolor='#eeeeec' align='center'>0.0001</td><td bgcolor='#eeeeec' align='right'>240864</td><td bgcolor='#eeeeec'>{main}(  )</td><td title='E:\wamp\www\hanzilend\html\index.php' bgcolor='#eeeeec'>..\index.php<b>:</b>0</td></tr>
<tr><td bgcolor='#eeeeec' align='center'>2</td><td bgcolor='#eeeeec' align='center'>0.0003</td><td bgcolor='#eeeeec' align='right'>276832</td><td bgcolor='#eeeeec'>System\Entrance::action(  )</td><td title='E:\wamp\www\hanzilend\html\index.php' bgcolor='#eeeeec'>..\index.php<b>:</b>4</td></tr>
<tr><td bgcolor='#eeeeec' align='center'>3</td><td bgcolor='#eeeeec' align='center'>0.0151</td><td bgcolor='#eeeeec' align='right'>372656</td><td bgcolor='#eeeeec'>System\Entrance::start(  )</td><td title='E:\wamp\www\hanzilend\System\Entrance.class.php' bgcolor='#eeeeec'>..\Entrance.class.php<b>:</b>30</td></tr>
<tr><td bgcolor='#eeeeec' align='center'>4</td><td bgcolor='#eeeeec' align='center'>0.0158</td><td bgcolor='#eeeeec' align='right'>474208</td><td bgcolor='#eeeeec'>System\Router::router(  )</td><td title='E:\wamp\www\hanzilend\System\Entrance.class.php' bgcolor='#eeeeec'>..\Entrance.class.php<b>:</b>61</td></tr>
<tr><td bgcolor='#eeeeec' align='center'>5</td><td bgcolor='#eeeeec' align='center'>0.0158</td><td bgcolor='#eeeeec' align='right'>474336</td><td bgcolor='#eeeeec'>System\Router::defaultConf(  )</td><td title='E:\wamp\www\hanzilend\System\Router.class.php' bgcolor='#eeeeec'>..\Router.class.php<b>:</b>8</td></tr>
<tr><td bgcolor='#eeeeec' align='center'>6</td><td bgcolor='#eeeeec' align='center'>0.0162</td><td bgcolor='#eeeeec' align='right'>559624</td><td bgcolor='#eeeeec'>AppMain\controller\Api\DistributorController->searchWithdraw(  )</td><td title='E:\wamp\www\hanzilend\System\Router.class.php' bgcolor='#eeeeec'>..\Router.class.php<b>:</b>24</td></tr>
<tr><td bgcolor='#eeeeec' align='center'>7</td><td bgcolor='#eeeeec' align='center'>0.0187</td><td bgcolor='#eeeeec' align='right'>1007496</td><td bgcolor='#eeeeec'>System\BaseClass->getOnePageData(  )</td><td title='E:\wamp\www\hanzilend\AppMain\controller\Api\DistributorController.class.php' bgcolor='#eeeeec'>..\DistributorController.class.php<b>:</b>124</td></tr>
<tr><td bgcolor='#eeeeec' align='center'>8</td><td bgcolor='#eeeeec' align='center'>0.0212</td><td bgcolor='#eeeeec' align='right'>1009664</td><td bgcolor='#eeeeec'><a href='http://www.php.net/function.call-user-func-array:{E:\wamp\www\hanzilend\System\BaseClass.class.php:286}' target='_new'>call_user_func_array:{E:\wamp\www\hanzilend\System\BaseClass.class.php:286}</a>
(  )</td><td title='E:\wamp\www\hanzilend\System\BaseClass.class.php' bgcolor='#eeeeec'>..\BaseClass.class.php<b>:</b>286</td></tr>
<tr><td bgcolor='#eeeeec' align='center'>9</td><td bgcolor='#eeeeec' align='center'>0.0212</td><td bgcolor='#eeeeec' align='right'>1010448</td><td bgcolor='#eeeeec'>System\database\BaseTable->get(  )</td><td title='E:\wamp\www\hanzilend\System\BaseClass.class.php' bgcolor='#eeeeec'>..\BaseClass.class.php<b>:</b>286</td></tr>
<tr><td bgcolor='#eeeeec' align='center'>10</td><td bgcolor='#eeeeec' align='center'>0.0215</td><td bgcolor='#eeeeec' align='right'>1012408</td><td bgcolor='#eeeeec'>System\database\BaseTable->logError(  )</td><td title='E:\wamp\www\hanzilend\System\database\BaseTable.class.php' bgcolor='#eeeeec'>..\BaseTable.class.php<b>:</b>748</td></tr>
<tr><td bgcolor='#eeeeec' align='center'>11</td><td bgcolor='#eeeeec' align='center'>0.0240</td><td bgcolor='#eeeeec' align='right'>1012360</td><td bgcolor='#eeeeec'>System\database\BaseTable->degbugLog(  )</td><td title='E:\wamp\www\hanzilend\System\database\BaseTable.class.php' bgcolor='#eeeeec'>..\BaseTable.class.php<b>:</b>354</td></tr>
</table></font>













</body>
</html>