<?php
/**
 * @author ganiks@qq.com
 * @date 2014-08-28
 */
include_once('AuthService.php');
$options = array(
	'appKey'=>'kj7swf8o7d7r2',                   //从融云开发者平台申请的 AppKey
	'appSecret'=>'GsZyWb09hNTnh',                //从融云开发者平台申请的 AppSecret
	'userId'=>'1',                   //用户 Id
	'deviceId'=>'',                 //设备标示
	'format'=>'json',               //返回格式 仅限于 json 或者 xml
	'name'=>'ganiks',                     //用户名称，最大长度 128 字节
	'portraitUri'=>'/user/portraitUri/1'               //用户头像 URL，最大长度 1024 字节
);
$p = new AuthService($options);
$ret = $p->request();
print_r($ret);
