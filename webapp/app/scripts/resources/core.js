'use strict';

app.
	factory("CoreResource",function($resource,urlApi){
		return $resource(urlApi+'core/:id',{id:'@_id'},{
			count : {
				method : 'GET',
				url: urlApi+'core/count'
			}
		});
	})