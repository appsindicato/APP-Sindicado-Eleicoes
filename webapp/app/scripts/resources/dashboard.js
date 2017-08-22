'use strict';
app.
	factory("DashboardResource",function($resource,urlApi){
		return $resource(urlApi+'dashboard',{},{
			dashboard: {
				method: 'GET'
			}
		});
	});