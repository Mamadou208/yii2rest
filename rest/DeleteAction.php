<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\controllers\rest;

use Yii;

/**
 * DeleteAction implements the API endpoint for deleting a model.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DeleteAction extends Action
{
    /**
     * Deletes a model.
     */
    public function run($id)
    {
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

		$itemRelation = $model->relations(); 
		if($model->delete()) {
			if($itemRelation) {
				$model->$itemRelation->delete();
			}
		}
		
        Yii::$app->getResponse()->setStatusCode(204);
    }
}
