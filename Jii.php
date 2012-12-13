<?php
class Jii extends CComponent
{
	private  $_jsonizer;
	
	private $_obj = '(function (){
		
		window["jii"] = {}; 
		
		window["jii"]["utils"] = {};

		window["jii"]["Model"] = function(){	 
			
			var array = arguments[0] instanceof Array ? true : false;
			
			this.count = function(){
				var counter = 0;
			 	if (array) {
					for (var attr in this) {
						if (isNaN(parseInt(attr, 10)) === false) {
							counter++;
						}
					}
				} else {
					counter = 1;
				}
				return counter;
			};

			/**
			* Returns the Javascript representation of the Model object
			* @param Model The model to decode
			* @return object js_object the decoded model
			*/
			this.toJS = function() {
				var js_object = {};

				for (var attr in this) {
					if (typeof this[attr] !== "function") {
						js_object[attr] = this[attr];
					}
				}
				return js_object;
			}

			// performs some initialization taks
			if (array) {
				this.add = function(){
					this[this.count()] = arguments[0];
				};
			}
			
			for (var attr in arguments[0]) {
				this[attr] = arguments[0][attr];
			}
		}
		
		/**
		* function findByAttribute
		* @param object {attribute: "attr", value: val}
		* @return Model
		*/
		window["jii"]["Model"].prototype.findByAttribute = function(){
			if (typeof arguments[0] === "undefined") {
				throw Error("You must provide both attribute and value");
			}		 
			var source 	  = this,
				cnt 	  = this.count(),
				i 		  = 0,
				attribute = arguments[0].attribute,
				value	  = arguments[0].value;

				if (cnt > 1) {
					for (i=0; i < cnt; i++) {					 
						if (source[i][attribute] === value) {
							return source[i];
							break;
						}
					}
				} else {		 
					if (source[attribute] === value) {
						return source; 
					}
				}
				
				
				return null;
		}
		
		window["jii"].params = {{params}};
		
		window["jii"].models = {{models}};
		
		window["jii"].urls	 = {{urls}};
		
		window["jii"].functions = {{functions}};				
		
	}())';		
	
	private $_ko_utils = '
		window["jii"].utils.observable = function(){
			if (typeof ko ==="undefined") {
				throw Error("ko has not been found");	
			}
			return ko.observable(arguments[0]);
		}
		
		window["jii"].utils.observableArray = function(){
			if (typeof ko ==="undefined") {
				throw Error("ko has not been found");	
			}
			return ko.observableArray(arguments[0].toJSON());
		}

		window["jii"].utils.getObservable = function(){
			if (typeof ko ==="undefined") {
				throw Error("ko has not been found");	
			}

			if (isArray(arguments[0])){
				return ko.observableArray(arguments[0]);
			} else {
				return ko.observable(arguments[0]);
			}
		}
	';

	private $_models = array();
	
	private $_params = array();
	
	private $_urls = array();

	private $_functions = array();
	
	public $config;

	public function init()
	{
		$this->_jsonizer = new Jsonizer();
	}
	
	public function jsonize($models)
	{
		return $this->_jsonizer->jsonize($models);
	}

	/**
	* Adds a model to Jii
	* @param string $name the name of the jii object property
	* @param mixed $data the model to be added
	*/
	public function addModel($name, $data)
	{
		// if we cannot decode JSON $data, we try to jsonize
		if (json_decode($data) === null) {			 
			$data = $this->jsonize($data);
		}
		 
		$this->_models[$name] = $data;
	}

	public function addFunction($name, $code)
	{
		$this->_functions[$name] = $code;
	}
	
	/**
	* Converts a Php variable into a Javscript one
	*/
	public function addParam($name, $value)
	{		 
		if (!is_array($value)) {

			$this->_params[$name] = $this->_toJsPrimitive($value);

		} else {
			
			if (is_object($value) || $this->_isAssoc($value)) {
	
				$this->_params[$name] = json_encode($value);
	
			} else {

				$array = '[{items}]';

				$items_string = '';

				foreach ($value as $item) {
					$items_string .= $this->_toJsPrimitive($item) . ',';
				}

				$items_string = substr($items_string, 0, -1);

				$array = str_replace('{items}', $items_string, $array);

				$this->_params[$name] = $array;


			}
		}
		

	}
	
	private function _toJsPrimitive($value)
	{
		return CJavaScript::encode($value);
	}

	private function _isAssoc($arr)
	{
	    return array_keys($arr) !== range(0, count($arr) - 1);
	}
	public function addUrl($label, $url)
	{
		$this->_urls[$label] = '"' . htmlspecialchars($url) . '"';
	}
	
	public function getScript()
	{
		$models = $params = $urls = $functions = '';
		
		if (!empty($this->_params)) {
			foreach($this->_params as $name => $data) {				
				$params .= "$name: " . $data .  ',' . PHP_EOL;
			}
			$params = substr($params, 0, -2);
		}

		if (!empty($this->_models)) {
			foreach($this->_models as $name => $data) {				
				$models .= $name .': new window["jii"].Model(' . $data . '),' . PHP_EOL;
			}
			$models = substr($models, 0, -2);			
		}

		if (!empty($this->_urls)) {
			foreach($this->_urls as $name => $data) {
				$urls .= "$name: " . $data . ',' . PHP_EOL;
			}
			$urls = substr($urls, 0, -2);
		}

		if (!empty($this->_functions)) {
			foreach($this->_functions as $name => $code) {
				$functions .= "$name: " . $code . ',' . PHP_EOL;
			}
			$functions = substr($functions, 0, -2);
		}

		$this->_obj = str_replace(array('{models}', '{params}', '{urls}', '{functions}'), array($models, $params, $urls, $functions), $this->_obj);

		// add utils based on configuration
		$lib = isset($this->config['lib']) ? $this->config['lib'] : null;

		if ($lib !== null) {
			switch ($lib) {
				case 'ko':
					$this->_obj .= $this->_ko_utils;
				break;
			}
		}

		return $this->_obj;
	}
}

class Jsonizer
{	
	
	/**
	* Converts a CActiveRecord instance into an array
	* @param CActiveRecord $model
	* @return array $model
	*/
	private function _jsonizeOne($model)
	{
		
		// for each model we store only jsonizeables attributes
		$attributes 	= array();
		$jsonizeables 	= array();

		// we select which attributes must be jsonized
		if (method_exists($model, 'getJsonizeables')) {
			$attributes = $model->getJsonizeables();
		
		// we get all model attributes if no jsonizeables attributes have been found
		} else {
			$attributes = array_keys($model->getAttributes());
		}

		// we encode each attribute into a javascript variable
		foreach ($attributes as $attribute_name) {
			$jsonizeables[$attribute_name] = $model->$attribute_name;
		}
		 
		// basic inheritance detection
		if ($this->_isParent('CModel', $model)) {
			$modelArray = $jsonizeables;			
			if (method_exists($model, 'relations')) {
				$relations = array_keys($model->relations());

				foreach ($relations as $relation) {
					if ($model->hasRelated($relation)) {

						$related_models = $model->getRelated($relation);
						
						if ($related_models !== null) {
							
							if (is_array($related_models)) {
								
									if (!empty($related_models)) {
										foreach($related_models as $related) {
	 
											$modelArray[$relation][] = $this->_jsonizeOne($related);
											// print_r($this->_jsonizeOne($related));
									 	}
									} else {

										$modelArray[$relation][] = array();
										
									}

							} else {
								 
								// print_r($related_models->getAttributes());
								$modelArray[$relation] = $this->_jsonizeOne($related_models);
							}

						} else {
							
							$modelArray[$relation] = null;

						}
						 
					}
				}
			}
		}
	
		// print_r($modelArray);
		return $modelArray;			
	}	
	
	/**
	* Converts an array of CActiveRecord instances into a php array
	* @param array $models
	* @return array 
	*/
	private function _jsonize($models)
	{	
		$modelArray = array();
		$i = 0;
		 
		foreach ($models as $model) {
			$model->getAttributes();
			$modelArray[$i++] = $this->_jsonizeOne($model);
		}
		
		return $modelArray;
	}
	
	private function _isParent($classname, $child)
	{
		while(($parent = get_parent_class($child)) !== false) {
			if ($parent === $classname) {
				return true;
			}
			$child = $parent;
		}
		return false;
	}
	
	/**
	* Converts CModel instances into JSON objects
	* @param CModel $data
	* @return string $json
	*/
	public function jsonize($data)
	{
		// support for CActiveDataProvider
		if ($data instanceof CActiveDataProvider) {
			$data = $data->getData();
		}
		
		if (is_array($data)) {
			return json_encode($this->_jsonize($data));
		} else {
			return json_encode($this->_jsonizeOne($data));
		}
		
	}	
		
}