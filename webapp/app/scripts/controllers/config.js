'use strict';
/**
* Controller da Configuracao
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('ConfigCtrl', function($scope, $rootScope, Upload, Base64, urlApi, $cookies, $translate, ImportResource, ConfigResource) {
    $rootScope.page = {
      title : 'page.config.title',
      subtitle: 'page.config.subtitle'
    }
    $scope.config = new ConfigResource();
    $scope.uploadFiles = function($file, type){
    	$scope.import = {};
    	if($file){
                swal.queue([{
                  title: $translate.instant('config.import_action.title'),
                  confirmButtonText: $translate.instant('config.import_action.button.yes'),
                  cancelButtonText: $translate.instant('config.import_action.button.cancel'),
                  showLoaderOnConfirm: true,
                  preConfirm: function () {
                    return new Promise(function (resolve) {
                        $scope.upload = Upload.upload({
                            url: urlApi + 'upload/file',
                            headers: {'Authorization': $cookies.get('BasicAuth')}, 
                            data: {file: $file}
                        }).then(function(response){
                            if(response.status == 201){
                                switch(type) {
                                    case 'core':
                                        ImportResource.core({filename: response.data.name}, 
                                            function(data){
                                                swal.insertQueueStep($translate.instant('core.import.success'))
                                                resolve();
                                            },
                                            function(response){
                                                swal.insertQueueStep($translate.instant('core.import.fail'));
                                                $scope.import.error = true;
                                                $scope.import.link = response.data.link;
                                                resolve();
                                        });
                                    break;
                                    case 'coreZone':
                                        ImportResource.coreZone({filename: response.data.name}, 
                                            function(data){
                                                swal.insertQueueStep($translate.instant('core_zone.import.success'))
                                                resolve();
                                            },
                                            function(response){
                                                swal.insertQueueStep($translate.instant('core_zone.import.fail'));
                                                $scope.import.error = true;
                                                $scope.import.link = response.data.link;
                                                resolve();
                                        });
                                    break;
                                    case 'city':
                                       ImportResource.city({filename: response.data.name}, 
                                            function(data){
                                                swal.insertQueueStep($translate.instant('city.import.success'))
                                                resolve();
                                            },
                                            function(response){
                                                swal.insertQueueStep($translate.instant('city.import.fail'));
                                                $scope.import.error = true;
                                                $scope.import.link = response.data.link;
                                                resolve();
                                        });
                                    break;
                                    case 'voter':
                                        ImportResource.voter({filename: response.data.name}, 
                                            function(data){
                                                swal.insertQueueStep($translate.instant('voter.import.success'))
                                                resolve();
                                            },
                                            function(response){
                                                swal.insertQueueStep($translate.instant('voter.import.fail'));
                                                $scope.import.error = true;
                                                $scope.import.link = response.data.link;
                                                resolve();
                                        });
                                    break;
                                }   
                            }else{
                                swal.insertQueueStep($translate.instant('modal.error'));
                                $scope.import.error = true;
                                $scope.import.link = response.data.link;
                                resolve();
                            }
                            
                        });
                    })
                  }
                }]);
        }
        $scope.open1 = function() {
            $scope.popup1.opened = true;
        };

    }

    $scope.configSave = function () {
        if( $scope.config.date1 && $scope.config.date2){
            $scope.config.$save(
                function(response){
                    sweetAlert($translate.instant('model.success'),$translate.instant('model.config.data.success'), "success");                        
                },
                function(response){
                    sweetAlert($translate.instant('model.error'),$translate.instant('model.config.data.error'), "error");
                }
            );
        }
    }
  });
