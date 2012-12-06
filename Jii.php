<?php
class Jii extends CComponent
{
	private  $_jsonizer;
	
	private $_obj = 'var Jii = {params: {{params}}, models: {{models}}, urls: {{urls}}, functions: {{functions}}}';
	
	private $_models = array();
	
	private $_params = array();
	
	private $_urls = array();

	private $_functions = array();
	
	public function init()
	{
		$this->_jsonizer = new Jsonizer();
	}
	
	public function jsonize($models)
	{
		return $this->_jsonizer->jsonize($models);
	}

	public function addModel($name, $data)
	{
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
		if (is_object($value) || $this->_isAssoc($value)) {

			$this->_params[$name] = json_encode($value);

		} else {

			if (!is_array($value)) {

				$this->_params[$name] = $this->_toJsPrimitive($value);

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
				$params .= "$name: " . $data . ',' . PHP_EOL;
			}
			$params = substr($params, 0, -2);
		}

		if (!empty($this->_models)) {
			foreach($this->_models as $name => $data) {
				$models .= "$name: " . $data . ',' . PHP_EOL;
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
						if (is_array($related_models)) {
								if (!empty($related_models)) {
									foreach($related_models as $related) {
										$modelArray[$relation][] = $this->_jsonizeOne($related);
								 	}
								} else {
									$modelArray[$relation][] = array();
								}

						} else {
							$modelArray[$relation] = $this->_jsonizeOne($related_models);
						}
						 
					}
				}
			}
		}
	
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
		
		foreach ($models as $model) {
			$modelArray[] = $this->_jsonizeOne($model);
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
		if (is_array($data)) {
			return json_encode($this->_jsonize($data));
		} else {
			return json_encode($this->_jsonizeOne($data));
		}
		
	}	
	
}