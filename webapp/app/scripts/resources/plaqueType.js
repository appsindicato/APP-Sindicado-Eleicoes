'use strict';

app.
	factory("PlaqueTypeResource",function($resource,urlApi){
		return $resource(urlApi+'plaque_type/:id',{id:'@_id'},{
			get :{
				method: 'GET'
			}
		});
	})