'use strict';

app.
	factory("CityResource",function($resource,urlApi){
		return $resource(urlApi+'city/:id',{id:'@_id'},{
		});
	})