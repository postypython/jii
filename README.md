jii
===

Javascript variables and object wrapper for Yii.
You can use it to convert Php variables (numbers, strings, booleans, array, objects) that you need to pass to Javascript.
A global Jii object will be created and it will contains what you added.
Jii object has the following form:
```javascript
	var Jii = {
		params: {},
		models: {},
		urls: {}
	}	
```

You can add params, models, or urls as follows.

Copy Jii.php to /path/to/application/protected/components

Add the following lines to your config/main.php:
```php
	'jii' => array(
		'class' => 'Jii',
	),
```

Sample usage:
```php
	
	Yii::app()->jii->addParam('integer', 10);

	Yii::app()->jii->addParam('unsigned_integer', -10);

	Yii::app()->jii->addParam('unsigned_float', 451.239873);

	Yii::app()->jii->addParam('signed_float', -309.0092927);

	Yii::app()->jii->addParam('bool_false', false);

	Yii::app()->jii->addParam('bool_true', true);

	Yii::app()->jii->addParam('string', '<h1>Title</h1><a href="#">link</a>');
	
	Yii::app()->jii->addUrl('view_test_url', $this->createUrl('test/view', array('id' => 1)));

	Yii::app()->jii->addParam('associative_array', array('pippo' => 3409879, '+349287//' => '<a>link</a>'));

	Yii::app()->jii->addParam('numeric_array', array(0, 1, -39, -938.2223, '<a href="#">Prova</a>', true));

	Yii::app()->jii->addParam('object', $object);
	
```

Adding Jii to your page can be done as follows:
```php
Yii::app()->clientScript->registerScript('jii', Yii::app()->jii->getScript(), CClientScript::POS_END);
```

---CActiveRecord
As regards model instances, you can select which attributes you want Javascript have access to by adding the following method to the model instance ...

```php
	...
	public function getJsonizeables()
	{
		return array(
			'attribute1',
			'attribute3',
			'attribute4',
		);
	}
	...
```
... and then use the following to add it to Jii:
```php
	$jsonized_model = Yii::app()->jii->jsonize($model);

	Yii::app()->jii->addModel('model', $jsonized_model);
```