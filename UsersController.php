<?php

namespace app\controllers;

use yii\rest\ActiveController;

use app\controllers\filters\auth\HttpBasicAuth;
use yii\helpers\ArrayHelper;
//use app\controllers\filters\auth\CompositeAuth;
//use app\controllers\filters\auth\HttpBearerAuth;
//use app\controllers\filters\auth\QueryParamAuth


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
