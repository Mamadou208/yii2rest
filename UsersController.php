<?php

namespace app\controllers;

use yii\rest\ActiveController;

use yii\filters\auth\HttpBasicAuth;
use yii\helpers\ArrayHelper;
#use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasciAuth;
#use yii\filters\auth\HttpBearerAuth;
#use yii\filters\auth\QueryParamAuth;


class UsersController extends ActiveController
{
    public $modelClass = 'app\models\User2';

	public function behaviors()
	{
		return ArrayHelper::merge(parent::behaviors(), [
			'authenticator' => [
				'class' => HttpBasicAuth::className(),
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
}
