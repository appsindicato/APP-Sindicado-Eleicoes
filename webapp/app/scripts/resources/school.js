'use strict';

app.
	factory("SchoolResource",function($resource,urlApi){
		return $resource(urlApi+'school/:id',{id:'@_id'},{
			get :{
				method: 'GET'
			}
		});
	})