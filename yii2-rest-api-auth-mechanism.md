@(yii-restful-api)

yii2 RESTful API Auth Mechanism
===

相关文章：http://www.cnblogs.com/ganiks/p/Yii2-RESTful-Authentication-and-Authorization.html

```
| | | |~filters/
| | | | |~auth/
| | | | | |-AuthInterface.php
| | | | | |-AuthMethod.php
| | | | | |-CompositeAuth.php
| | | | | |-HttpBasicAuth.php
| | | | | |-HttpBearerAuth.php
| | | | | `-QueryParamAuth.php
```

###一、`HttpBearer` 验证方式：
```
<?php
namespace app\controllers;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;
use yii\helpers\ArrayHelper;
#use yii\filters\auth\CompositeAuth;
#use yii\filters\auth\HttpBasciAuth;
use yii\filters\auth\HttpBearerAuth;
#use yii\filters\auth\QueryParamAuth;

class UsersController extends ActiveController
{
    public $modelClass = 'app\models\User2';

	public function behaviors()
	{
		return ArrayHelper::merge(parent::behaviors(), [
			'authenticator' => [
				'class' => HttpBearerAuth::className(),
				#这个地方使用`ComopositeAuth` 混合认证
				#'class' => CompositeAuth::className(),
				#`authMethods` 中的每一个元素都应该是 一种 认证方式的类或者一个 配置数组
				//'authMethods' => [
					//HttpBasicAuth::className(),
					//HttpBearerAuth::className(),
					//QueryParamAuth::className(),
				//]
			]
		]);
	}

```

###下面来分析相关的源码，看看 yii2/rest 是如何处理权限验证的。

```php
<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

use Yii;
use yii\web\UnauthorizedHttpException;

/**
 * HttpBearerAuth is an action filter that supports the authentication method based on HTTP Bearer token.
 *
 * You may use HttpBearerAuth by attaching it as a behavior to a controller or module, like the following:
 *
 * public function behaviors()
 * {
 *     return [
 *         'bearerAuth' => [
 *             'class' => \yii\filters\auth\HttpBearerAuth::className(),
 *         ],
 *     ];
 * }
 * 
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HttpBearerAuth extends AuthMethod
{
    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get('Authorization');
        if ($authHeader !== null && preg_match("/^Bearer\\s+(.*?)$/", $authHeader, $matches)) {
            $identity = $user->loginByAccessToken($matches[1], get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            }
            return $identity;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Bearer realm=\"{$this->realm}\"");
        throw new UnauthorizedHttpException('You are requesting with an invalid access token.');
    }
}
```

要想顺利通过此验证，需要在 `Header` 中加入一项：
`Authorization:Bearer ganiks-token`

如果没有如上提供正确的`access-token`， 则会得到一个:
```
401 Unauthorized
Www-Authenticate:Bearer realm="api"
```

###二、`HttpBasic` 验证方式：

```php
class HttpBasicAuth extends AuthMethod
{
    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';
    /**
     * @var callable a PHP callable that will authenticate the user with the HTTP basic auth information.
     * The callable receives a username and a password as its parameters. It should return an identity object
     * that matches the username and password. Null should be returned if there is no such identity.
     *
     * The following code is a typical implementation of this callable:
     *
     * function ($username, $password) {
     *     return \app\models\User::findOne([
     *         'username' => $username,
     *         'password' => $password,
     *     ]);
     * }
     *
     * If this property is not set, the username information will be considered as an access token
     * while the password information will be ignored. The [[\yii\web\User::loginByAccessToken()]]
     * method will be called to authenticate and login the user.
     */
    public $auth;


    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $username = $request->getAuthUser();
        $password = $request->getAuthPassword();

        if ($this->auth) {
            if ($username !== null || $password !== null) {
                $identity = call_user_func($this->auth, $username, $password);
                if ($identity !== null) {
                    $user->switchIdentity($identity);
                } else {
                    $this->handleFailure($response);
                }
                return $identity;
            }
        } elseif ($username !== null) {
            $identity = $user->loginByAccessToken($username, get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            }
            return $identity;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Basic realm=\"{$this->realm}\"");
        throw new UnauthorizedHttpException('You are requesting with an invalid access token.');
    }
}
```

如果要顺利通过此验证，有2种方式：
1. `access-token` 作为 `Basci Auth` 的 `username` 一起请求，至于`password`则不必管
2. 定义下 `use yii\filters\auth\HttpBasciAuth`的`$auth`为一个验证的方法，来根据请求中的`username:password`来返回一个 `identity`

默认的， `$auth`是没有定义的，此时，在Header中加入：
`Authorization:Basic Z2FuaWtzLXRva2VuOg==`
这个码是由`username: ganiks-token` 经过base64编码得到的，可以得到正确的响应
反而是用`username:password`（正确的）也无法得到响应

第二种方式，
可以配置  `use yii\filters\auth\HttpBasciAuth`的`$auth` 为一个 `callable`如下：
```
	public $auth;
	public function auth ($username, $password) {
		return \app\models\User2::findOne([
			'username' => $username,
			'password' => $password,
		]);
	}
```

> 这里是个遗留问题，http://www.yiiframework.com/forum/index.php/topic/56916-how-to-define-a-callable-variable-for-a-class/

####下面是一个临时的解决方案，使用 `username:password_hash`验证
```
<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters\auth;

use Yii;
use yii\web\UnauthorizedHttpException;
class HttpBasicAuth extends AuthMethod
{
	public $auth;

	public function auth ($username, $password) {
		return \app\models\User2::findOne([
			'username' => $username,
			'password_hash' => $password,
		]);
	}


    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $username = $request->getAuthUser();
        $password = $request->getAuthPassword();

        if ($this->auth($username, $password)) {
            if ($username !== null || $password !== null) {
                //$identity = call_user_func($this->auth, $username, $password);
                $identity = $this->auth($username, $password);
                if ($identity !== null) {
                    $user->switchIdentity($identity);
                } else {
                    $this->handleFailure($response);
                }
                return $identity;
            }
        } elseif ($username !== null) {
            $identity = $user->loginByAccessToken($username, get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            }
            return $identity;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Basic realm=\"{$this->realm}\"");
        throw new UnauthorizedHttpException('You are requesting with an invalid access token.');
    }
}
```


###三、`QueryParam` 验证方式：

此方式最简单，只需要在请求的URL后面加上`?access-toekn=ganiks-token`即可验证
```
class QueryParamAuth extends AuthMethod
{
    /**
     * @var string the parameter name for passing the access token
     */
    public $tokenParam = 'access-token';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $accessToken = $request->get($this->tokenParam);
        if (is_string($accessToken)) {
            $identity = $user->loginByAccessToken($accessToken, get_class($this));
            if ($identity !== null) {
                return $identity;
            }
        }
        if ($accessToken !== null) {
            $this->handleFailure($response);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        throw new UnauthorizedHttpException(Yii::t('yii', 'You are requesting with an invalid access token.'));
    }
```

###四、`compositeAuth`综合验证方式：
```
class CompositeAuth extends AuthMethod
{
    /**
     * @var array the supported authentication methods. This property should take a list of supported
     * authentication methods, each represented by an authentication class or configuration.
     *
     * If this property is empty, no authentication will be performed.
     *
     * Note that an auth method class must implement the [[\yii\filters\auth\AuthInterface]] interface.
     */
    public $authMethods = [];


    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        return empty($this->authMethods) ? true : parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        foreach ($this->authMethods as $i => $auth) {
            $this->authMethods[$i] = $auth = Yii::createObject($auth);
            if (!$auth instanceof AuthInterface) {
                throw new InvalidConfigException(get_class($auth) . ' must implement yii\filters\auth\AuthInterface');
            }

            $identity = $auth->authenticate($user, $request, $response);
            if ($identity !== null) {
                return $identity;
            }
        }

        if (!empty($this->authMethods)) {
            /* @var $auth AuthInterface */
            $auth = reset($this->authMethods);
            $auth->handleFailure($response);
        }

        return null;
    }
```
