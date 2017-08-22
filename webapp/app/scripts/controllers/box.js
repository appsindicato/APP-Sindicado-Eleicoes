'use strict';
/**
* Controller da Urna
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('ListBoxCtrl', function($scope, $rootScope, BoxResource, $translate, $mdDialog, UrlQueryNotation, $filter,$q,$timeout,CoreResource,CityResource,SmartTableFactory) {
    $rootScope.page = {
      title : 'page.box.title',
      subtitle: 'page.box.subtitle'
    }
    $scope.itemsByPage = 50;
    $timeout(function(){
        $scope.cores = CoreResource.query({},function(){
        $scope.citys = CityResource.query();
      });
     },1000);

    $scope.displayed = [];
    $scope.callServer = function callServer(tableState) {
      $scope.isLoading = true;
      var pagination = tableState.pagination;
      var start = pagination.start || 0;     // This is NOT the page number, but the index of item in the list that you want to use to display the table.
      var number = pagination.number || 10;  // Number of entries showed per page.
      var p = SmartTableFactory.prepareFields(tableState);
      SmartTableFactory.getPage(BoxResource,start, number, tableState).then(function (result) {
        $scope.displayed = result;
        BoxResource.count(p,function(d){
          tableState.pagination.numberOfPages = Math.ceil(d.total / number);
        });//set the number of pages so the pagination can update
        $scope.notFound = false;
        $scope.isLoading = false;
      },function(){
        $scope.isLoading = false;
        $scope.notFound = true;
      });
    };

    $scope.open = function (){
     $mdDialog.show({
          templateUrl: 'views/tmpl/modal/box.html',
          parent: angular.element(document.body),
          clickOutsideToClose:true,
          controller: function($scope, $filter, BoxResource, CityResource, $translate, UrlQueryNotation, CoreResource){
            $scope.box = new BoxResource();
            $scope.cores = CoreResource.query({},function(){
              $scope.citys = CityResource.query();
            });

            $scope.$watch('box.city', function(newValue, oldValue) {
              if(newValue && newValue.id){
                $scope.box.city_id = newValue.id;
              }
            });

            $scope.$watch('box.core', function(newValue, oldValue) {
              if(newValue && newValue.id){
                $scope.box.core_id = newValue.id;
              }  
            });
            
            $scope.save = function (){
              $scope.box.$save(
                function(response){
                  sweetAlert($translate.instant('model.success'),$translate.instant('model.box.create'), "success");
                  $scope.data = BoxResource.query();
                  $scope.cancel();
                }, 
                function(response){
                  var m = "";
                  angular.forEach(response.data.error, function(e){
                    m += $translate.instant(e) + "<br/>";
                    
                  });
                  sweetAlert($translate.instant('model.error'), m, "error");
                });
            };

            $scope.cancel = function(){
              $mdDialog.cancel();
            };
          }
      });

    }

    $scope.delete = function (_id){
      swal({
        title:  $translate.instant('model.delete'),
        text:   $translate.instant('model.box.delete.ask'),
        type:   "warning",
        showCancelButton: true,
        confirmButtonColor: "#d9534f",
        confirmButtonText: $translate.instant('other.delete'),
        cancelButtonText: $translate.instant('other.cancel')
      }).then(function(){
          BoxResource.delete({id: _id}, function(){
            sweetAlert($translate.instant('model.success'),$translate.instant('model.box.delete.confirm'), "success");
            $scope.data = BoxResource.query();
          },function(){
            sweetAlert($translate.instant('model.error'), $translate.instant('model.error'), "error");
          });
      });      
    }

    $scope.suspendKey = function(id){
      BoxResource.suspendKey({id: id}, 
        function(response){
          sweetAlert($translate.instant('model.success'),$translate.instant('model.box.delete.key'), "success");
        }, 
        function(response){
          sweetAlert($translate.instant('model.error'), $translate.instant('model.error'), "error");
      });
    };
  });
