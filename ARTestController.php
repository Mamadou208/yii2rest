<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\TestCustomer;

class ARTestController extends Controller
{
	public function actionIndex()
	{
		$customer = new TestCustomer();
		print_R($customer);exit;
	}
		
	public function actionCreate()
	{
		$customer = new TestCustomer();
		$customer->name = "Ganiks";
		$customer->save();
	}

}
