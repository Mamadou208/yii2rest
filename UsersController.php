<?php

namespace app\controllers;

use yii\rest\ActiveController;

use yii\filters\auth\HttpBasicAuth;
use yii\helpers\ArrayHelper;
use yii\filters\auth\CompositeAuth;
#use yii\filters\auth\HttpBasciAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;


class UsersController extends ActiveController
{
    public $modelClass = 'app\models\User';
}
