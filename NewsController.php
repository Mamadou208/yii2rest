<?php

namespace app\controllers;

use app\controllers\rest\ActiveController;

//use app\controllers\filters\auth\HttpBasicAuth;
use yii\helpers\ArrayHelper;
use app\controllers\filters\auth\CompositeAuth;
//use app\controllers\filters\auth\HttpBearerAuth;
use app\controllers\filters\auth\QueryParamAuth;

use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\web\response;

use yii\filters\AccessControl;

class NewsController extends ActiveController
{
    public $modelClass = 'app\models\News';
	public $serializer = [
		'class' => 'app\controllers\rest\Serializer',
		'collectionEnvelope' => 'items',
    ];

	public function behaviors()
	{
		$pb = ArrayHelper::merge(parent::behaviors(), [
			'verbFilter' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'index'  => ['get'],
					'view'   => ['get'],
					'create' => ['get', 'post'],
					'update' => ['get', 'put', 'post'],
					'delete' => ['post', 'delete'],
				],
			],
			/*
			'contentNegotiator' => [
				'class' => ContentNegotiator::className(),
				'formats' => [
					'text/html' => Response::FORMAT_HTML,
					#'application/json' => Response::FORMAT_JSON,
				],
			],
			 */
			'authenticator' => [
				#����ط�ʹ��`ComopositeAuth` �����֤
				'class' => CompositeAuth::className(),
				#`authMethods` �е�ÿһ��Ԫ�ض�Ӧ���� һ�� ��֤��ʽ�������һ�� ��������
				'authMethods' => [
					//HttpBasicAuth::className(),
					//HttpBearerAuth::className(),
					QueryParamAuth::className(),
				]
			],
			'access' => [
				'class' => AccessControl::className(),
				'only' => ['view'],
				'rules' => [
					[
						'actions' => ['view'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		]);
		return $pb;
	}

	public function checkAccess()
	{
	}
}
