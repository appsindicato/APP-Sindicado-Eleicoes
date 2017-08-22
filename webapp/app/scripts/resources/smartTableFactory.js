'use strict';

app.
	factory("SmartTableFactory",function($resource,urlApi){

    var st = {
    	prepareFields : function (params){
	      var p = {q:"",limit:0,offset:0};
	      if(params.search && params.search.predicateObject){
	        angular.forEach(params.search.predicateObject, function(v,k){
	          p.q += k+":"+v+",";
	        });
	        p.q = p.q.substr(0, p.q.length - 1);;
	      }
	      if(params.pagination){
	        if(params.pagination.number){
	          p.limit = params.pagination.number ;
	        }
	        if(params.pagination.start){
	          p.offset = params.pagination.start;
	        }
	      }
	      return p;
	    },
	    getPage : function(Resource,start, number, params) {
	      // var deferred = $q.defer();
	      var p = st.prepareFields(params);
	      var resultQuery = Resource.query(p);
	      return resultQuery.$promise;
    	}
    }
		return st;
	});