'use strict';

app.
	factory("ConfigResource",function($resource,urlApi){
		return $resource(urlApi+'configure/date',{},{
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
			}
		});
	})