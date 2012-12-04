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
```