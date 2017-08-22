'use strict';

app.
	factory("ImportResource",function($resource,urlApi){
		return $resource(urlApi,{filename:'@_filename'},{
			core : {
				method: 'POST',
				url: urlApi + 'core/import'
			},
			coreZone :{
				method: 'POST',
				url: urlApi + 'core_zone/import'
			},
			voter: {
				method: 'POST',
				url: urlApi + 'voter/import'
			},
			city:{
				method: 'POST',
				url: urlApi + 'city/import'
			}
		});
	})