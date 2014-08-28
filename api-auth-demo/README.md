api-auth-demo
================

PHP implementation of auth service for Rong Cloud.

php 使用：（请参照官网文档手册：http://docs.rongcloud.cn/server.html）
```php
require_once('AuthService.php');
$options = array(
    'appKey'=>'',                   //从融云开发者平台申请的 AppKey
    'appSecret'=>'',                //从融云开发者平台申请的 AppSecret
    'userId'=>'',                   //用户 Id
    'deviceId'=>'',                 //设备标示
    'format'=>'json',               //返回格式 仅限于 json 或者 xml
    'name'=>'',                     //用户名称，最大长度 128 字节
    'portraitUri'=>''               //用户头像 URL，最大长度 1024 字节
);
$p = new AuthService($options);
$ret = $p->request();
print_r($ret);
```
