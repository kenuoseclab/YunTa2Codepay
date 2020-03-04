<?php

function CodePay_ConfigOption(){
    return array(
        'codepay_id' => array(
            'label' => '您的码支付ID',
            'type' => 'number',
            'placeholder' => '您的码支付ID',
        ),
        'codepay_key' => array(
            'label' => '您的码支付密钥',
            'type' => 'text',
            'placeholder' => '您的码支付密钥',
        ),
        'gateway' => array(
            'label' => '选用支付渠道',
            'type' => 'select',
            'option' => array(
                '支付宝' => '1',
                '微信' => '3',
                'QQ钱包' => '2',
            ),
        ),
    );
}

function CodePay_ProcessOrder($config, $params){
    $codepay_id=$config['codepay_id'];
    $codepay_key=$config['codepay_key']; 
	$http = 'http://';
	if ($_SERVER['HTTPS'] == "on") {
		$http = 'https://';
    }

    $data = array(
        "id" => $codepay_id,//你的码支付ID
        "pay_id" => $params['orderid'], //唯一标识 可以是用户ID,用户名,session_id(),订单ID,ip 付款后返回
        "type" => $config['gateway'],//1支付宝支付 3微信支付 2QQ钱包
        "price" => $params['money'],//金额100元
        "param" => "",//自定义参数
        "notify_url"=> $http . $_SERVER['HTTP_HOST'] . "/payment/pay_notify.php?yunta_gateway=CodePay",
        "return_url"=> $http . $_SERVER['HTTP_HOST'] . "/payment/pay_return.php?yunta_gateway=CodePay",
    ); //构造需要传递的参数

    ksort($data); //重新排序$data数组
    reset($data); //内部指针指向数组中的第一个元素

    $sign = ''; //初始化需要签名的字符为空
    $urls = ''; //初始化URL参数为空

    foreach ($data AS $key => $val) { //遍历需要传递的参数
        if ($val == ''||$key == 'sign') continue; //跳过这些不参数签名
        if ($sign != '') { //后面追加&拼接URL
            $sign .= "&";
            $urls .= "&";
        }
        $sign .= "$key=$val"; //拼接为url参数形式
        $urls .= "$key=" . urlencode($val); //拼接为url参数形式并URL编码参数值

    }
    $query = $urls . '&sign=' . md5($sign .$codepay_key); //创建订单所需的参数
    $url = "http://api2.xiuxiu888.com/creat_order/?{$query}"; //支付页面

    header("Location:{$url}"); //跳转到支付页面
    return '正在跳转';
}

function CodePay_ProcessNotify($getdata, $config){
    ksort($getdata); //排序post参数
    reset($getdata); //内部指针指向数组中的第一个元素
    $codepay_key=$config['codepay_key']; //这是您的密钥
    $sign = '';//初始化
    foreach ($getdata AS $key => $val) { //遍历POST参数
        if ($val == '' || $key == 'sign') continue; //跳过这些不签名
        if ($sign) $sign .= '&'; //第一个字符串签名不加& 其他加&连接起来参数
        $sign .= "$key=$val"; //拼接为url参数形式
    }
    if (!$getdata['pay_no'] || md5($sign . $codepay_key) != $getdata['sign']) { //
        return array(
            'status' => 'fail',
            'msg' => '不合法的数据',
        );
    } else { //合法的数据
        return array(
            'status' => 'success',
            'msg' => '验证成功',
            'orderid' => $getdata['pay_id'],
        );
    }
}

function CodePay_ProcessReturn($getdata, $config){
    ksort($getdata); //排序post参数
    reset($getdata); //内部指针指向数组中的第一个元素
    $codepay_key=$config['codepay_key']; //这是您的密钥
    $sign = '';//初始化
    foreach ($getdata AS $key => $val) { //遍历POST参数
        if ($val == '' || $key == 'sign') continue; //跳过这些不签名
        if ($sign) $sign .= '&'; //第一个字符串签名不加& 其他加&连接起来参数
        $sign .= "$key=$val"; //拼接为url参数形式
    }
    if (!$getdata['pay_no'] || md5($sign . $codepay_key) != $getdata['sign']) { //
        return array(
            'status' => 'fail',
            'msg' => '不合法的数据',
        );
    } else { //合法的数据
        return array(
            'status' => 'success',
            'msg' => '验证成功',
            'orderid' => $getdata['pay_id'],
        );
    }
}
?>