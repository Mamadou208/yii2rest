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
		$newsItem = Yii::$app->getRequest()->getBodyParams()['newsItem'];
		/*
			Array
			(
				[title] => ttttttttttt
				[body] => bbbbbbbbbbb
			)
		 */
		$model->newsItem->load($newsItem, '');
		#$model->newsItem->load(Yii::$app->getRequest()->getBodyParams(), '');
		#print_R($model->newsItem);exit;
		#print_R($model->newsItem);exit;
		if($model->save())
		{
			$model->load(Yii::$app->getRequest()->getBodyParams(), '');
			$model->newsItem->save();
		}

        return $model;
    }
}
