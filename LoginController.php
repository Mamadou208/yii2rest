<?php

namespace app\controllers;

use Yii;
use yii\web\controller;
use app\controllers\filters\auth\HttpBasicAuth;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UrlManager;
//use app\controllers\filters\auth\CompositeAuth;
//use app\controllers\filters\auth\HttpBearerAuth;
//use app\controllers\filters\auth\QueryParamAuth


class LoginController extends Controller
{
	public function behaviors()
    {
        return [
            'access' => [
                'class' => HttpBasicAuth::className(),
            ],
        ];
    }

	public function actionIndex()
	{
		$id = Yii::$app->getUser()->identity->id;
		$username = Yii::$app->getUser()->identity->username;
		$access_token = Yii::$app->getUser()->identity->access_token;
		$array = [
			'id'=>$id,
			'username'=>$username,
			'access_token'=>$access_token,
		];
		return json_encode($array);
	}
}

