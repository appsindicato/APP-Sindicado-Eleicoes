'use strict';
/**
* Controller da Cidade
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('ListCityCtrl', function($scope, $rootScope, CityResource) {
    $rootScope.page = {
      title : 'page.city.title',
      subtitle: 'page.city.subtitle'
    }
    $scope.data = CityResource.query();
  });
