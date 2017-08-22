'use strict';

app.
	factory("UserResource",function($resource,urlApi){
		return $resource(urlApi+'user/:id',{id:'@_id'},{
			update : {
				method : 'PUT'
			},
			get :{
				method: 'GET'
			},
			save: {
				method: 'POST'
			},
			delete:{
				method: 'DELETE'
			},
			count : {
				method : 'GET',
				url: urlApi+'user/count'
			}
		});
	})