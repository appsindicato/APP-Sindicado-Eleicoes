'use strict';
/**
* Controller do Núcleo
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('ListCoreCtrl', function($scope, $rootScope, CoreResource,$timeout,CityResource,SmartTableFactory) {
    $rootScope.page = {
      title : 'page.core.title',
      subtitle: 'page.core.subtitle'
    }

    $scope.itemsByPage = 50;
    $timeout(function(){
        $scope.citys = CityResource.query();
     },1000);


    $scope.displayed = [];
    $scope.callServer = function callServer(tableState) {
      $scope.isLoading = true;
      var pagination = tableState.pagination;
      var start = pagination.start || 0;     // This is NOT the page number, but the index of item in the list that you want to use to display the table.
      var number = pagination.number || 10;  // Number of entries showed per page.
      var p = SmartTableFactory.prepareFields(tableState);
      SmartTableFactory.getPage(CoreResource,start, number, tableState).then(function (result) {
        $scope.displayed = result;
        CoreResource.count(p,function(d){
          tableState.pagination.numberOfPages = Math.ceil(d.total / number);
        });//set the number of pages so the pagination can update
        $scope.notFound = false;
        $scope.isLoading = false;
      },function(){
        $scope.isLoading = false;
        $scope.notFound = true;
      });
    };
  });
