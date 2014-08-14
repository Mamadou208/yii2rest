<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\controllers\rest;

use Yii;
use yii\base\Model;
use yii\helpers\Url;

/**
 * CreateAction implements the API endpoint for creating a new model from the given data.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CreateAction extends Action
{
    /**
     * @var string the scenario to be assigned to the new model before it is validated and saved.
     */
    public $scenario = Model::SCENARIO_DEFAULT;
    /**
     * @var string the name of the view action. This property is need to create the URL when the mode is successfully created.
     */
    public $viewAction = 'view';

    /**
     * Creates a new model.
     * @return \yii\db\ActiveRecordInterface the model newly created
     * @throws \Exception if there is any error when creating the model
     */
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass([
            'scenario' => $this->scenario,
        ]);

		if(0 === strpos(Yii::$app->getRequest()->getContentType(), 'application/json'))
		{
			$requestBody = Yii::$app->getRequest()->getRawBody();
			$requestBody = json_decode($requestBody, true);
		}else{
			$requestBody = Yii::$app->getRequest()->getBodyParams();
		}
		#NewsItem || newsItem 不区分大小写
		$modelItem = str_ireplace($this->controller->id, $model->relations(), $this->modelClass);
		$modelItem = new $modelItem;

        $model->load($requestBody, '');
		$modelItemParam = $requestBody[$model->relations()];
		$modelItem->load($modelItemParam, '');
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
			$modelItem->header_id = $id;
			$modelItem->save();
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        }

        return $model;
    }
}
