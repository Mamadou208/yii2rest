@(yii-restful-api)

Yii2 RESTful API DEV for Related Models
===

前文：[Yii2 Restful API 原理分析](http://www.cnblogs.com/ganiks/p/yii2-restful-api-mechanism.html)

> 通过 `yii2\rest\ActiveController` 可以方便的用几行代码创建针对某个资源的api，但是默认是最简单的逻辑，并不支持对 `relatedModel`的操作。这里做一个扩展。
> GitHub 项目地址： https://github.com/Ganiks/yii2rest
> 原创文章，转载请注明： http://www.cnblogs.com/ganiks/
>还是建议将 \yii\rest\ 拷贝出来另作一个命名空间，在这个基础上做扩展

针对 `view create update delete` 操作，下面尝试实现一个`common 通用的`方案

view
---
```
    public function run($id)
    {
        $model = $this->findModel($id);
        return $model;
    }
```
这里`action`不需要做任何修改了，因为在 `model`中定义的`fields`已经可以同时将`relatedModel`数据返回了。

```
	public function getNewsItem()
	{
		return $this->hasOne(NewsItem::className(), ['header_id' => 'id']);
	}

    /**
     * @inheritdoc
	 * 如果没有指定fields ,默认返回这里定义的所有字段
	 * 如果指定了fields则只返回指定的字段
     */
	public function fields()
	{
		return [
			'id',
			'image',
			'link',
			'newsItem',
		];
	}
```

create
---
```
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass([
            'scenario' => $this->scenario,
        ]);

		#NewsItem || newsItem 不区分大小写
		$modelItem = str_ireplace($this->controller->id, $model->relations(), $this->modelClass);
		$modelItem = new $modelItem;

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
		$modelItemParam = Yii::$app->getRequest()->getBodyParams()[$model->relations()];
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
```

create方法的关键是得到`relatedModel`的全路径 `app\models\relatedModel`
```
@$this->controller->id=news
@model->relations()=newsItem
@this->modelClass=app\models\news
$modelItem = str_ireplace($this->controller->id, $model->relations(), $this->modelClass);
```

这里的 `model->relations()` 是自己在 `app\model\NewsItem`中定义的，后面还要用到：
```
    /**
     * @inheritdoc
	 * 自定义参数，让 $model->relations 获取到关联的 model 
     */
	public function relations()
	{
		return 'newsItem';
	}
```


update
---
```
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
		$params = Yii::$app->getRequest()->getBodyParams();
		$model->load($params, '');
		$itemParam = array_diff_key($params, $model->attributes);
		$itemModel = array_keys($itemParam)[0];
		$itemRelation = $model->relations(); 
		if($model->save())
		{
			if($itemParam && $itemModel == $itemRelation) {
				$model->$itemModel->load($itemParam[$itemModel], '');
				$model->$itemModel->save();
			}
		}

        return $model;
    }
```


delete
---
```
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
```

-----------
