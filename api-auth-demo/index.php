<?php
/**
 * @author ganiks@qq.com
 * @date 2014-08-28
 */
include_once('AuthService.php');
$options = array(
	'appKey'=>'kj7swf8o7d7r2',                   //�����ƿ�����ƽ̨����� AppKey
	'appSecret'=>'GsZyWb09hNTnh',                //�����ƿ�����ƽ̨����� AppSecret
	'userId'=>'1',                   //�û� Id
	'deviceId'=>'',                 //�豸��ʾ
	'format'=>'json',               //���ظ�ʽ ������ json ���� xml
	'name'=>'ganiks',                     //�û����ƣ���󳤶� 128 �ֽ�
	'portraitUri'=>'/user/portraitUri/1'               //�û�ͷ�� URL����󳤶� 1024 �ֽ�
);
$p = new AuthService($options);
$ret = $p->request();
print_r($ret);
