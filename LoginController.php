<?php

namespace app\controllers;

use Yii;
use yii\web\controller;
//use app\controllers\filters\auth\HttpBasicAuth;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\LoginForm2;
//use app\controllers\filters\auth\CompositeAuth;
//use app\controllers\filters\auth\HttpBearerAuth;
//use app\controllers\filters\auth\QueryParamAuth


class LoginController extends Controller
{
	/**
	 * this @var set to false is very important
	 * otherwise, you'll get 400 http error when you want to request -X POST
	 */
	public $enableCsrfValidation = false;

	public function behaviors()
	{
		return [
			'verbFilter' => [
                'class' => VerbFilter::className(),
				'actions' => [
					'index' => ['POST'],
				],
            ]
		];
	}

	/**
	 * for http basic auth
	 * instead of post params auth
	 */
	public function behaviors2()
    {
        return [
            'access' => [
                'class' => HttpBasicAuth::className(),
            ],
        ];
    }

	public function actionIndex()
	{
        $model = new LoginForm2();
		$post_params = ['LoginForm2' => Yii::$app->request->post()];
        if ($model->load($post_params) && $model->login()) {
			$user = $model->getUser();
			$user->updateAccessToken();

			$id = $user->id; 
			$username = $user->username; 
			$access_token = $user->access_token; 
			$array = [
				'id'=>$id,
				'username'=>$username,
				'access_token'=>$access_token,
			];
        } else {
			Yii::$app->getResponse()->setStatusCode(401);
			$array = [
				'code'=>401,
				'msg'=>'Unauthorized, please check your username and password'
			];
        }
		return json_encode($array);
	}
}

