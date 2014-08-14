<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\controllers\rest;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\ActiveRelationTrait;

/**
 * UpdateAction implements the API endpoint for updating a model.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UpdateAction extends Action
{
    /**
     * @var string the scenario to be assigned to the model before it is validated and updated.
     */
    public $scenario = Model::SCENARIO_DEFAULT;

    /**
     * Updates an existing model.
     * @param string $id the primary key of the model.
     * @return \yii\db\ActiveRecordInterface the model being updated
     * @throws \Exception if there is any error when updating the model
     */
    public function run($id)
    {
        /* @var $model ActiveRecord */
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        $model->scenario = $this->scenario;
		/*
		 *
		 * x-www-form-urlencoded key=>value
		 * image mmmmmmmm
		 * link  nnnnnnnnnn
		 * newsItem[title]=>ttttttttttt , don't use newsItem["title"]
		 * newsItem[body]=>bbbbbbbbbbb
		 * don't use newsItem=>array("title":"tttttt","body":"bbbbbbb")
		 * don't use newsItem=>{"title":"ttttttt","body":"bbbbbbbb"}
		 *
		 */
		if(0 === strpos(Yii::$app->getRequest()->getContentType(), 'application/json'))
		{
			//when request Content-Type is JSON
			$requestBody = Yii::$app->getRequest()->getRawBody();
			$requestBody = json_decode($requestBody, true);
		}else{
			$requestBody = Yii::$app->getRequest()->getBodyParams();
		}
		$model->load($requestBody, '');
		$itemParam = array_diff_key($requestBody, $model->attributes);
		if($model->save())
		{
			if($itemParam)
			{
				//if request content include itemModel related params
				$itemModel = array_keys($itemParam)[0];
				$itemRelation = $model->relations(); 
				if($itemModel == $itemRelation)
				{
					if($model->$itemModel)
					{
						//if itemModel was created when ites parent model created
						$model->$itemModel->load($itemParam[$itemModel], '');
						$model->$itemModel->save();
					}
					else
					{
						//if itemModel was not created when its parent model created
						$modelItem = str_ireplace($this->controller->id, $model->relations(), $this->modelClass);
						$modelItem = new $modelItem;
						$modelItem->load($itemParam[$itemModel], '');
						$foreignKey = $model->foreignKey();
						$id = implode(',', array_values($model->getPrimaryKey(true)));
						$modelItem->$foreignKey = $id;
						$modelItem->save();
					}
				}
			}
		}

        return $model;
    }
}
