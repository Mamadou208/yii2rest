20140823 Fix bug about validating same ip
===

After login successfully, client's ip saved into db should be got before the curl request, otherwise the ip got in  basic auth process would always be the api server's ip.

What's more, the ip should be transmitted by a GET params to api server's basic auth, instead of POST params, cause it is a GET action to get user's access-token

```
//Client LoginForm.php
$user_ip = Yii::$app->request->userIP;

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ; 
curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
curl_setopt($ch, CURLOPT_URL, $apiHost.'/users/'.$id.'?userIP='.$user_ip)

//Server REST Basic Auth
$user_ip = $request->queryParams['userIP'];

$identity->updateAccessToken($user_ip);


//Server models\User2.php
	public function updateAccessToken($user_ip)
	{
        $this->access_token = Yii::$app->security->generateRandomString();
		$this->last_login_time = date('Y-m-d H:i:s', strtotime('now'));
		//$this->last_login_ip = Yii::$app->request->userIP;
		$this->last_login_ip = $user_ip;
		$this->save();
	}

```

