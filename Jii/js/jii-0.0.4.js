(function (window){
	
		// jii object
	var jii 			= {},
	
		// Model constructor
		Model       	= {};
		 
	// object container for utilities
	jii.utils 		= {};

	// object container for params
	jii.params 		= {};
	
	// object container for Models
	jii.models 		= {};
	
	// object container for urls
	jii.urls		= {};
	
	// object container for functions
	jii.functions 	= {};	

	// object used to contain functions that need to be executed when document is ready
	jii.bindings    = {
		bindings: [],
		apply: function () {
			var s = jii.bindings.bindings.length,
				i = 0;
			for (i=0; i < s; i++) {
				jii.bindings.bindings[i]();
			}
		}
	};
	/*
	* Copyright 2010, John Resig
	* Dual licensed under the MIT or GPL Version 2 licenses.
	* http://jquery.org/license
	*/
	// Cleanup functions for the document ready method
	// attached in the bindReady handler
	if ( document.addEventListener ) {
	DOMContentLoaded = function() {
	    document.removeEventListener( "DOMContentLoaded", DOMContentLoaded, false );
	};

	} else if ( document.attachEvent ) {
	DOMContentLoaded = function() {
	    // Make sure body exists, at least, in case IE gets a little overzealous 
	            if ( document.readyState === "complete" ) {
	        document.detachEvent( "onreadystatechange", DOMContentLoaded ); 
	    }
	    };
	}

	// Catch cases where $(document).ready() is called after the
	// browser event has already occurred.
	if ( document.readyState === "complete" ) {	    
	    // Handle it asynchronously to allow scripts the opportunity to delay ready
		//return setTimeout( jQuery.ready, 1 );
	    // ^^ you may want to call *your* function here, similarly for the other calls to jQuery.ready
	    setTimeout( jii.bindings.apply, 1 );
	}

	// Mozilla, Opera and webkit nightlies currently support this event
	if ( document.addEventListener ) {
	    // Use the handy event callback
		document.addEventListener( "DOMContentLoaded", DOMContentLoaded, false );
		// A fallback to window.onload, that will always work
		//window.addEventListener( "load", jQuery.ready, false );
	    window.addEventListener( "load", jii.bindings.apply, false );
	 // If IE event model is used
	 } else if ( document.attachEvent ) {
	        // ensure firing before onload,
	        // maybe late but safe also for iframes
	        document.attachEvent("onreadystatechange", DOMContentLoaded);

	        // A fallback to window.onload, that will always work
	        window.attachEvent( "onload", jii.bindings.apply );

	 }

	// Jii Model constructor
	Model = function(){	 
		
		var array = arguments[0] instanceof Array  ? true : false;
		
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
	Model.prototype.findByAttribute = function(){
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
				if (typeof source[0] !== "undefined") {
					for (var attr in source[0]) {
						if (source[0][attribute] === value) {
							return source[0];
							break;
						}	
					}
				} else if (source[attribute] === value) {
					return source; 
				}
			}
			
			
			return null;
	}
		
	if (typeof ko !== undefined) {
		// follow some utilities
		jii.utils.observable = function(){
			if (typeof ko ==="undefined") {
				throw Error("ko has not been found");	
			}
			return ko.observable(arguments[0]);
		}
			
		jii.utils.observableArray = function(){
			if (typeof ko ==="undefined") {
				throw Error("ko has not been found");	
			}
			return ko.observableArray(arguments[0].toJS());
		}

		jii.utils.getObservable = function(){
			if (typeof ko ==="undefined") {
				throw Error("ko has not been found");	
			}

			if (isArray(arguments[0])){
				return ko.observableArray(arguments[0]);
			} else {
				return ko.observable(arguments[0]);
			}
		}
	}
	// add Model constructor to jii
	jii.Model = Model;

	// makes jii available on the global scope
	window.jii = jii;

}(window));