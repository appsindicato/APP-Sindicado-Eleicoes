'use strict';

app.
	factory("PlaqueResource",function($resource,urlApi){
		return $resource(urlApi+'plaque/:id',{id:'@_id'},{
			get :{
				method: 'GET'
			},
			update:{
				method: 'PUT'
			},
			delete:{
				method: 'DELETE'
			},
			count : {
				method : 'GET',
				url: urlApi+'plaque/count'
			}
		});
	})