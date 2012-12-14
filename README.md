Jii 0.0.4
===============================
Javascript library for Yii
You can use it to convert PHP variables (numbers, strings, booleans, array, objects) to their Javascript equivalents.
A *jii* object will be created on the javascript global scope and it will contain everything you add.

## Jii 0.0.4 configuration
You can now configure jii to add *Knockout js* support:
## Configuring Yii
Copy Jii directory to /path/to/application/protected/components and add the following lines to your config/main.php:
```php
    'components' => array(
        ...
	    'jii' => array(
		    'class' => 'application.components.Jii.Jii',
		    	// uncomment the following if you do not want to use the minified version
		    	// 'script' => 'jii-0.0.4.js',
	    ),
        ...
    ),
```

## Add Jii to your page
Adding Jii to your page can be done as follows:
```php
	Yii::app()->clientScript->registerScript('jii', Yii::app()->jii->getScript(), CClientScript::POS_END);
```

*Knockout js* support is automatically added to Jii and includes the following functions:
```javascript
// each function accept one Model as argument
jii.utils.observable();
jii.utils.observableArray();
```

### Javascript Models
On the client side, each model that you add with `Yii::app()->jii->addModel()` will expose the following methods:
```javascript
/**
 * Finds one model instance based on the specified attribute
 * @param object {attribute: "attribute_name", value: attribute_value
 * @return object or null if none object is found
*/
jii.models.your_model.findByAttribute();

// the json representation of your model
jii.models.your_model.toJS();

// the number of model instances found
jii.models.your_model.count();
```

### Direct model or data provider jsonization
You can directly encode models - and their relations - as well as data provider in the following way:
```php
$model = new Model();
Yii::app()->jii->addModel('model', $model);
```

You can add params, models, urls or functions as follows. Notice that type casting from PHP to Javascript is available only for params.


## Sample usage
```php
	
	// adding params
	Yii::app()->jii->addParam('integer', 10);

	// adding urls
	Yii::app()->jii->addUrl('view_test_url', $this->createUrl('test/view', array('id' => 1)));

	// adding functions
	Yii::app()->jii->addFunction('function', 'function(){ alert("This is an alert!"); }');
	
```

## Binding functions (since 0.0.4)
You can add functions that will be executed once the page has finished loading in the followin way:
```php
// the following function will be called once the document is ready
Yii::app()->jii->addBindings('function(){
	alert("Page loading has finished");
}');
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
