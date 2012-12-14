<?php
class Jii extends CComponent
{
	public  $config;
	
	private $_jsonizer;
	
	private $_bindings	= array();

	private $_models 	= array();
	
	private $_params 	= array();
	
	private $_urls 		= array();

	private $_functions = array();
	
	private $_script 	= 'jii-min-0.0.4.js';

	public function init()
	{
		if (!isset($this->config['script'])) {
			$this->config['script'] = $this->_script;
		}

		// publish jii script
		$jii_js = Yii::app()->assetManager->publish(Yii::getPathOfAlias('application.components.Jii.js') . DIRECTORY_SEPARATOR .  $this->config['script']);
		
		// registers jii
		Yii::app()->clientScript->registerScriptFile($jii_js, CClientScript::POS_END);
		
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
	* Allows to add custom function to be executed after document is ready
	* @params string $function javascript anonymous function
	*/
	public function addBindings($function)
	{		
		$this->_bindings[] = $function;
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
	
	/**
	 * The following to ensure that each script is rendered wherever you add it to your view
	 * @return string $javascript javascript code
	 */
	public function getScript()
	{
		$models = $params = $urls = $functions = $bindings = '';
		
		if (!empty($this->_params)) {
			foreach($this->_params as $name => $data) {				
				$params .= 'jii.params.' . $name .' = ' . $data .  ';';
			}
					
		}
		
		if (!empty($this->_urls)) {
			foreach($this->_urls as $name => $data) {				
				$urls .= 'jii.urls.' . $name .' = ' . $data .  ';';
			}
					
		}
		
		if (!empty($this->_functions)) {
			foreach($this->_functions as $name => $data) {				
				$functions .= 'jii.functions.' . $name .' = ' . $data .  ';';
			}
					
		}
		
		if (!empty($this->_models)) {
			foreach($this->_models as $name => $data) {				
				$models .= 'jii.models.' . $name .' = new jii.Model(' . $data . ');';
			}
		}

		if (!empty($this->_bindings)) {
			foreach ($this->_bindings as $binding) {
				$bindings .= 'jii.bindings.bindings.push(' . $binding .');' . PHP_EOL;
			}
		}
		
		// clears everything after each call
		$this->_params 		= array();
		$this->_urls 		= array();
		$this->_models 		= array();
		$this->_bindings 	= array();
		
		return $urls . PHP_EOL . $params . PHP_EOL . $functions . PHP_EOL . $models . PHP_EOL . $bindings;	
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