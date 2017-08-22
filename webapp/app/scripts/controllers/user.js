'use strict';
/**
* Controller dos Usuários
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('ListUserCtrl', function($scope, $rootScope, $state, $uibModal, UserResource, UrlQueryNotation, $translate,SmartTableFactory) {
    $rootScope.page = {
      title : 'page.user.title',
      subtitle: 'page.user.subtitle'
    }

    $scope.itemsByPage = 50;

    $scope.displayed = [];
    $scope.callServer = function callServer(tableState) {
      $scope.isLoading = true;
      var pagination = tableState.pagination;
      var start = pagination.start || 0;     // This is NOT the page number, but the index of item in the list that you want to use to display the table.
      var number = pagination.number || 10;  // Number of entries showed per page.
      var p = SmartTableFactory.prepareFields(tableState);
      SmartTableFactory.getPage(UserResource,start, number, tableState).then(function (result) {
        $scope.displayed = result;
        UserResource.count(p,function(d){
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
      var modalInstance = $uibModal.open({
          animation: true,
          ariaLabelledBy: 'modal-title',
          ariaDescribedBy: 'modal-body',
          templateUrl: 'views/tmpl/modal/user.html',
          size: 'md',
          controller: function($scope,$uibModalInstance,$filter, UserResource, $translate){
            $scope.user = new UserResource();
            $scope.save = function (){
              $scope.user.$save(
                function(response){
                  sweetAlert($translate.instant('model.success'),$translate.instant('model.user.create'), "success");
                  $uibModalInstance.close();
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
              $uibModalInstance.close();
            };
          }
      });
      modalInstance.result.then(function () {
        $scope.data = UserResource.query();
      }, function () {
      });
    }

    $scope.edit = function (_id){
      UserResource.get({id: _id}, function(data){
          var modalInstance = $uibModal.open({
          animation: true,
          ariaLabelledBy: 'modal-title',
          ariaDescribedBy: 'modal-body',
          templateUrl: 'views/tmpl/modal/user.html',
          size: 'md',
          controller: function($scope,$uibModalInstance,$filter, UserResource, $translate){
            $scope.user = new UserResource();
            $scope.user.first_name = data.first_name;
            $scope.user.last_name = data.last_name;
            $scope.user.email = data.email ;
            $scope.user.document = data.document;
            $scope.user.role = data.role;
            $scope.user.id = data.id; 

            $scope.save = function (){
              $scope.user.$update({id : $scope.user.id},
                function(response){
                  sweetAlert($translate.instant('model.success'),$translate.instant('model.user.update'), "success");
                  $uibModalInstance.close();
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
              $uibModalInstance.close();
            };
          },
          resolve: function(){
            return data;
          }
      });

      modalInstance.result.then(function () {
          $scope.data = UserResource.query();
      }, function () {
      });
      
    },
      function(){
    });


    }
    $scope.delete = function (_id){

      swal({
        title:  $translate.instant('model.delete'),
        text:   $translate.instant('model.user.delete.ask'),
        type:   "warning",
        showCancelButton: true,
        confirmButtonColor: "#d9534f",
        confirmButtonText: $translate.instant('other.delete'),
        cancelButtonText: $translate.instant('other.cancel')
      }).then(function(){
          UserResource.delete({id: _id}, function(){
            sweetAlert($translate.instant('model.success'),$translate.instant('model.user.delete.confirm'), "success");
            $scope.data = UserResource.query();
          },function(){
            sweetAlert($translate.instant('model.error'), $translate.instant('model.error'), "error");
          });
      });      
    }
    $scope.data = UserResource.query();
  });
