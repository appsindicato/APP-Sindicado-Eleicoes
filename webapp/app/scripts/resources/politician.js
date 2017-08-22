'use strict';

app.
	factory("PoliticianResource",function($resource,urlApi){
		return $resource(urlApi+'candidate/:id',{id:'@_id'},{
			update : {
				method : 'PUT',
				url : urlApi+'candidate/:id',
				params: {id: '@_id'}
			},
			get :{
				method: 'GET'
			},
			save: {
				method: 'POST'
			},
			delete:{
				url : urlApi+'candidate/:id',
				method: 'DELETE'
			},
			count : {
				method : 'GET',
				url: urlApi+'box/count'
			}
		});
	})