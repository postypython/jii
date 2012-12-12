Jii 0.0.3beta 
===============================
Javascript library for Yii
You can use it to convert PHP variables (numbers, strings, booleans, array, objects) to their Javascript equivalents.
A *jii* object will be created on the javascript global scope and it will contain everything you add.
Jii object has the following form:
```javascript
jii = {
    params: {},
    models: {},
    urls: {},
    functions: {}
}	
```
## Jii 0.0.3beta additions
You can now configure jii to add *Knockout js* support:
```php
'components' => array(
    ...
    jii' => array(
        'class' => 'Jii',
        'config' => array(
            'lib' => 'ko',
        ),
    ),
    ...
)
```
Knockout js support includes the following functions:
```javascript
// each function accept one Model as argument
jii.utils.observable();
jii.utils.observableArray();
```

## Direct model or data provider jsonization
You can directly encode models - and their relations - as well as data provider in the following way:
```php
$model = new Model();
Yii::app()->jii->addModel('model', $model);
```

## Jii Models
Each of the models you will add, will be represented by a Javascript object providing the following methods:
```javascript
	// number of items
	jii.models.model_name_1.count();

	// if model is an array of models you can add items in the following way
	jii.models.model_name_1.add({object});

	// returns the first object matching selected attribute value
	jii.models.model_name_1.findByAttribute({attribute: "name", value: value});
```

You can add params, models, urls or functions as follows. Notice that type casting from PHP to Javascript is available only for params.
## Configuring Yii
Copy Jii.php to /path/to/application/protected/components and add the following lines to your config/main.php:
```php
    'components' => array(
        ...
	    'jii' => array(
		    'class' => 'Jii',
	    ),
        ...
    ),
```

## Sample usage
```php
	
	// adding params
	Yii::app()->jii->addParam('integer', 10);

	Yii::app()->jii->addParam('unsigned_integer', -10);

	Yii::app()->jii->addParam('unsigned_float', 451.239873);

	Yii::app()->jii->addParam('signed_float', -309.0092927);

	Yii::app()->jii->addParam('bool_false', false);

	Yii::app()->jii->addParam('bool_true', true);

	Yii::app()->jii->addParam('string', '<h1>Title</h1><a href="#">link</a>');
	
	Yii::app()->jii->addParam('associative_array', array('goofy' => 3409879, '+349287//' => '<a>link</a>'));

	Yii::app()->jii->addParam('numeric_array', array(0, 1, -39, -938.2223, '<a href="#">Test</a>', true));

	Yii::app()->jii->addParam('object', $object);

	// adding urls
	Yii::app()->jii->addUrl('view_test_url', $this->createUrl('test/view', array('id' => 1)));

	// adding functions
	Yii::app()->jii->addFunction('function', 'function(){ alert("This is an alert!"); }');
	
```
## Add Jii to your dynamic page
Adding Jii to your page can be done as follows:
```php
	Yii::app()->clientScript->registerScript('jii', Yii::app()->jii->getScript(), CClientScript::POS_END);
```

## Adding CActiveRecord to Jii
As regards CActiveRecord instances (or class instances inheriting from CModel), you can select which attribute you want Javascript have access to by adding the following method to each of the CActiveRecord class files you wish to convert ...

```php
	...
	public function getJsonizeables()
	{
		return array(
			'attribute_label_1',
			'attribute_label_3',
			'attribute_label_4',
		);
	}
	...
```
... and then you can use the following code to add it to Jii (support for CActiveDataProvider has been added):
```php
	$jsonized_model = Yii::app()->jii->jsonize($model);

	// adding models to jii
	Yii::app()->jii->addModel('model', $jsonized_model);
```
