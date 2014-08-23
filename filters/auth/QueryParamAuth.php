<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\controllers\filters\auth;

use Yii;
use yii\web\UnauthorizedHttpException;

/**
 * QueryParamAuth is an action filter that supports the authentication based on the access token passed through a query parameter.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
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
			if ($identity) 
			{
				$userIP = Yii::$app->request->userIP;
				$now_time =  strtotime('now');
				$last_login_time = $identity->last_login_time;
				$diff_time = $now_time - strtotime($last_login_time);
				/**
				 * check ip and time
				 */
				if ($userIP == $identity->last_login_ip) 
					if ($diff_time < Yii::$app->params['restapi']['tokenExpire']) 
					{
						return $identity;
					} else {
						throw new UnauthorizedHttpException(Yii::t('yii', 'You are requesting with an expired access token, please request for a new one.'));
				} else {
					throw new UnauthorizedHttpException(Yii::t('yii', 'You are requesting with an invalid access token (ip changed), please request for a new one.'));
				}
			}else{
				$this->handleFailure($response);
			}
		}else{
			throw new UnauthorizedHttpException(Yii::t('yii', 'You are requesting with a wrong-formatted access token.'));
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
}
