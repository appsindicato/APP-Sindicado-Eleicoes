'use strict';
/**
* Controller da Recuperacao de Senha
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('RecoveryChangePasswordCtrl', function($state, $translate, $scope, ToastFactory, $timeout, UserResource, AuthenticationService, $stateParams) {
    AuthenticationService.ClearCredentials();
    $scope.step = 2;
    $scope.user = new UserResource();
    $scope.user.$getEmailChange({id: $stateParams.id, secret: $stateParams.token}, function(data){}, function(data){
      ToastFactory.error($translate.instant("recovery.authenticate_fail"));
      $state.transitionTo('login');
    });

    $scope.changePassword = function(){
      if($scope.user.new_password != undefined && $scope.user.new_password.length > 0){
        if($scope.user.confirm_password != undefined && $scope.user.confirm_password.length > 0){      
          if($scope.user.new_password != $scope.user.confirm_password){
            ToastFactory.error($translate.instant("signup.validation.password_match"));
          }else{
            $scope.newUser = new UserResource();
            $scope.newUser.password = $scope.user.new_password;
            $scope.newUser.$passwordReset({id: $stateParams.id, secret: $stateParams.token},function(data){
                ToastFactory.success($translate.instant("recovery.success"));
                $scope.user.requested = 1;
                $timeout(function(){$state.transitionTo('login')}, 1000);
              },
              function(err){
                ToastFactory.error($translate.instant("recovery.generic_error"));
                $state.transitionTo('login');
            });
          }
        }else{
          ToastFactory.error($translate.instant("signup.validation.confirm_password"));
        }
      }else{
        ToastFactory.error($translate.instant("signup.validation.password"));
      }
    }
});