'use strict';
/**
* Controller dos Eleitores
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app.
	factory("VoterResource",function($resource,urlApi){
		return $resource(urlApi+'voter/:id',{id:'@_id'},{
			count : {
				method : 'GET',
				url: urlApi+'voter/count'
			}
		});
	})