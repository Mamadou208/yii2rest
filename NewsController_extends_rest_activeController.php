<?php

namespace app\controllers;

use yii\rest\ActiveController;

use yii\filters\auth\HttpBasicAuth;
use yii\helpers\ArrayHelper;
use yii\filters\auth\CompositeAuth;
#use yii\filters\auth\HttpBasciAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\web\response;

class NewsController extends ActiveController
{
    public $modelClass = 'app\models\News';
	public $serializer = [
		'class' => 'yii\rest\Serializer',
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
					HttpBasicAuth::className(),
					HttpBearerAuth::className(),
					QueryParamAuth::className(),
				]
			],
		]);
		return $pb;
	}
}
