'use strict';
/**
* Controller dos Candidatos
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('ListPoliticianCtrl', function($scope, $rootScope, PoliticianResource, $uibModal, $translate,SmartTableFactory,PoliticianOfficeResource) {
    $rootScope.page = {
      title : 'page.politician.title',
      subtitle: 'page.politician.subtitle'
    }

    $scope.politician_office = PoliticianOfficeResource.query();
    $scope.itemsByPage = 50;
    $scope.displayed = [];
    $scope.callServer = function callServer(tableState) {
      $scope.isLoading = true;
      var pagination = tableState.pagination;
      var start = pagination.start || 0;     // This is NOT the page number, but the index of item in the list that you want to use to display the table.
      var number = pagination.number || 10;  // Number of entries showed per page.
      var p = SmartTableFactory.prepareFields(tableState);
      SmartTableFactory.getPage(PoliticianResource,start, number, tableState).then(function (result) {
        $scope.displayed = result;
        PoliticianResource.count(p,function(d){
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
          templateUrl: 'views/tmpl/modal/politician.html',
          size: 'md',
          controller: function($scope,$uibModalInstance,$filter, $translate, PoliticianResource, PoliticianOfficeResource){
            $scope.politician_office = PoliticianOfficeResource.query();
            $scope.politician = new PoliticianResource();
            $scope.save = function (){
              $scope.politician.$save(
                function(response){
                  sweetAlert($translate.instant('model.success'),$translate.instant('model.politician.create'), "success");
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
          $scope.data = PoliticianResource.query();
      }, function () {
      });

    }

    $scope.edit = function (_id){
      PoliticianResource.get({id: _id}, function(data){
          var modalInstance = $uibModal.open({
          animation: true,
          ariaLabelledBy: 'modal-title',
          ariaDescribedBy: 'modal-body',
          templateUrl: 'views/tmpl/modal/politician.html',
          size: 'md',
          controller: function($scope,$uibModalInstance,$filter, PoliticianResource, PoliticianOfficeResource, $translate){
            $scope.politician_office = PoliticianOfficeResource.query();
            $scope.politician = new PoliticianResource();
            $scope.politician.first_name = data.first_name;
            $scope.politician.last_name = data.last_name;
            $scope.politician.email = data.email ;
            $scope.politician.document = data.document;
            $scope.politician.candidate_office_id = data.candidate_office_id;
            $scope.politician.id = data.id; 

            $scope.save = function (){
              $scope.politician.$update({id : $scope.politician.id},
                function(response){
                  $scope.data = PoliticianResource.query();
                  sweetAlert($translate.instant('model.success'),$translate.instant('model.politician.update'), "success");
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
          $scope.data = PoliticianResource.query();
      }, function () {
      });
    },
    function(){
    });
  }
  $scope.delete = function (_id){

      swal({
        title:  $translate.instant('model.delete'),
        text:   $translate.instant('model.politician.delete.ask'),
        type:   "warning",
        showCancelButton: true,
        confirmButtonColor: "#d9534f",
        confirmButtonText: $translate.instant('other.delete'),
        cancelButtonText: $translate.instant('other.cancel')
      }).then(function(){
          PoliticianResource.delete({id: _id}, function(){
            sweetAlert($translate.instant('model.success'),$translate.instant('model.user.delete.confirm'), "success");
            $scope.data = PoliticianResource.query();
          },function(){
            sweetAlert($translate.instant('model.error'), $translate.instant('model.error'), "error");
          });
      });      
    }
    	
  });
