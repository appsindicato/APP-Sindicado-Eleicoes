'use strict'
app.factory('ToastFactory', function($translate, toastr) {
    var _self = this;

	function _default(tag, type, position, time, target) {
	    var options = {};
	    options.type= type;
	    if(position){
		    options.position= position;
	    }
	    if(time){
	    	options.time= time;
	    }
	    if(target){
	    	options.target= target;
	    }

  		_self.msg ="";
	  	if(tag instanceof Array){
	  		angular.forEach(tag,function(t){
	  			if(t != undefined){
			    	_self.msg += $translate.instant(t)+"<br/>";
	  			}
	  		});
	  	}else{
	    	_self.msg = $translate.instant(tag);
	  	}
	  	toastr[type](_self.msg);
	}

	_self.success = function(tag, position, time, target) {
    	_default(tag, 'success', position, time, target);
    }
    _self.error = function(tag, position, time, target) {
    	_default(tag, 'error', position, time, target);
    }
    _self.info = function(tag, position, time, target) {
    	_default(tag, 'info', position, time, target);
    }
  	return _self;
});