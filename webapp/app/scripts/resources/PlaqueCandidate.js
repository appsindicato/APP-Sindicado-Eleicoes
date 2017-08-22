'use strict';

app.
	factory("PlaqueCandidateResource",function($resource,urlApi){
		return $resource(urlApi+'plaque_candidate/:id',{id:'@_id'},{
			get :{
				method: 'GET'
			},
			update:{
				method: 'PUT'
			},
			query: {
				method: 'GET',
				isArray: true,
				url: urlApi+'plaque_candidate/?q=:q',
				params: {q: '@_q'}
			},
			delete:{
				method: 'DELETE'
			}
		});
	})