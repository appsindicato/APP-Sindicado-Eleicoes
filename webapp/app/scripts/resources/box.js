'use strict';

app.
	factory("BoxResource",function($resource,urlApi){
		return $resource(urlApi+'box/:id',{id:'@_id'},{
			update : {
				method : 'PUT',
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
			suspendKey:{
				method: 'GET',
				url: urlApi + 'box/suspend/:id'
			},
			count : {
				method : 'GET',
				url: urlApi+'box/count'
			}
		});
	})