'use strict';
/**
* Controller da Chapas
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('ListPlaqueCtrl', function($scope, $rootScope, $translate, PlaqueResource, $uibModal, $mdDialog, PlaqueCandidateResource, UrlQueryNotation,PlaqueTypeResource,CoreResource,SmartTableFactory) {
    $rootScope.page = {
      title : 'page.plaque.title',
      subtitle: 'page.plaque.subtitle'
    };
    $scope.cores = CoreResource.query({},function(){
      $scope.plaqueType = PlaqueTypeResource.query();
    });
  
    $scope.itemsByPage = 50;  

    $scope.displayed = [];
    $scope.callServer = function callServer(tableState) {
      $scope.isLoading = true;
      var pagination = tableState.pagination;
      var start = pagination.start || 0;     // This is NOT the page number, but the index of item in the list that you want to use to display the table.
      var number = pagination.number || 10;  // Number of entries showed per page.
      var p = SmartTableFactory.prepareFields(tableState);
      SmartTableFactory.getPage(PlaqueResource,start, number, tableState).then(function (result) {
        $scope.displayed = result;
        PlaqueResource.count(p,function(d){
          tableState.pagination.numberOfPages = Math.ceil(d.total / number);
        });//set the number of pages so the pagination can update
        $scope.notFound = false;
        $scope.isLoading = false;
      },function(){
        $scope.isLoading = false;
        $scope.notFound = true;
      });
    };

    $scope.member = function (id){   
      PlaqueCandidateResource.query({q: UrlQueryNotation.toQuery({plaque_id : id})}, function(data){
        if(data.length){
          var message = "";
          angular.forEach(data,function(n){
            message += n.Candidate.first_name +' '+ n.Candidate.last_name + '<br/>';
          });
          swal($translate.instant("page.plaque.title"), message);
        }
      }, function(data){
        swal($translate.instant("page.plaque.title"), $translate.instant("plaque.table.empty"));
      });
    }
      
    $scope.open = function (){
      var modalInstance = $uibModal.open({
          animation: true,
          ariaLabelledBy: 'modal-title',
          ariaDescribedBy: 'modal-body',
          templateUrl: 'views/tmpl/modal/plaque.html',
          size: 'md',
          controller: function($scope,$uibModalInstance,$filter, PlaqueResource, $translate, PlaqueTypeResource){
            $scope.plaque = new PlaqueResource();
            $scope.plaqueType = PlaqueTypeResource.query();
            $scope.save = function (){
              $scope.plaque.$save(
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
          $scope.data = PlaqueResource.query();
      }, function () {
      });
    }

    $scope.addCandidate = function (_id){
      PlaqueResource.get({id: _id}, function(data){
        $mdDialog.show({
          controller: function($scope,$filter, PlaqueResource, $translate, PlaqueCandidateResource, PoliticianResource){
            $scope.plaque_candidate = new PlaqueCandidateResource();
            $scope.candidates = PoliticianResource.query();
            $scope.plaque_candidate.plaque_id = _id;

            $scope.save = function (){
              $scope.plaque_candidate.candidate_id = $scope.plaque_candidate.candidate.id;
              $scope.plaque_candidate.$save(
                function(response){
                  sweetAlert($translate.instant('model.success'),$translate.instant('model.user.update'), "success");
                  $scope.data = PlaqueResource.query();
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
            }           
          },
          templateUrl: 'views/tmpl/modal/plaque_candidate.html',
          parent: angular.element(document.body),
          clickOutsideToClose:true
        })
      },
      function (data){
        //something goes wrong
      });


    }

    $scope.removeCandidate = function (_id){
      PlaqueCandidateResource.query({q: UrlQueryNotation.toQuery({plaque_id : _id})}, function(data){
        var modalInstance = $uibModal.open({
            animation: true,
            ariaLabelledBy: 'modal-title',
            ariaDescribedBy: 'modal-body',
            templateUrl: 'views/tmpl/modal/remove_plaque_candidate.html',
            size: 'md',
            controller: function($scope,$uibModalInstance,$filter, PlaqueResource, $translate, PlaqueTypeResource){
              $scope.data = data;
              $scope.delete = function (_id){
               PlaqueCandidateResource.delete({id : _id},
                function(data){
                  sweetAlert($translate.instant('model.success'),$translate.instant('model.plaque_candidate.delete'), "success");
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
                $uibModalInstance.close();
              };
            },
            resolve: function(){
              return data;
            } 
        });
        modalInstance.result.then(function () {
            $scope.data = PlaqueResource.query();
        }, function () {
        });
      },
      function(data){
        swal($translate.instant('model.error'), $translate.instant('model.plaque_candidate.notfound'), "warning");
      });

    };

  $scope.edit = function (_id){
      PlaqueResource.get({id: _id}, function(data){
          var modalInstance = $uibModal.open({
          animation: true,
          ariaLabelledBy: 'modal-title',
          ariaDescribedBy: 'modal-body',
          templateUrl: 'views/tmpl/modal/plaque.html',
          size: 'md',
          controller: function($scope,$uibModalInstance,$filter, PlaqueResource, $translate, PlaqueTypeResource){
            $scope.plaqueType = PlaqueTypeResource.query();
            $scope.plaque = new PlaqueResource();
            $scope.plaque.name = data.name;
            $scope.plaque.plaque_type_id = data.plaque_type_id;
            $scope.plaque.id = data.id; 
            $scope.plaque.number = data.number; 

            $scope.save = function (){
              $scope.plaque.$update({id : $scope.plaque.id},
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
          $scope.data = PlaqueResource.query();
      }, function () {
      });
    },
    function(){
    });
  }
    $scope.delete = function (_id){

      swal({
        title:  $translate.instant('model.delete'),
        text:   $translate.instant('model.plaque.delete.ask'),
        type:   "warning",
        showCancelButton: true,
        confirmButtonColor: "#d9534f",
        confirmButtonText: $translate.instant('other.delete'),
        cancelButtonText: $translate.instant('other.cancel')
      }).then(function(){
          PlaqueResource.delete({id: _id}, function(){
            sweetAlert($translate.instant('model.success'),$translate.instant('model.plaque.delete.confirm'), "success");
            $scope.data = PlaqueResource.query();
          },function(){
            sweetAlert($translate.instant('model.error'), $translate.instant('model.error'), "error");
          });
      });      
    }
  });
