'use strict';

app.
	factory("PoliticianOfficeResource",function($resource,urlApi){
		return $resource(urlApi+'candidate_office/:id',{id:'@_id'},{
			update : {
				method : 'PUT',
				url : urlApi+'candidate_office/:id',
				params: {id: '@_id'}
			},
			get :{
				method: 'GET'
			},
			save: {
				method: 'POST'
			},
			delete:{
				url : urlApi+'candidate_office/:id',
				method: 'DELETE'
			}
		});
	})