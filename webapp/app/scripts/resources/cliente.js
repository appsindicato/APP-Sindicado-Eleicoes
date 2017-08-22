'use strict';

app.
	factory("ClienteResource",function($resource,urlApi){
		return $resource(urlApi+'cliente/:id',{id:'@_id'},{
			update : {
				method: 'PUT'
			}
		});
	})