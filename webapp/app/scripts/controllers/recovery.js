'use strict';
/**
* Controller da Recuperação de Senha
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('RecoveryCtrl', function($translate,$scope,ToastFactory, $timeout, UserResource, AuthenticationService,$state) {
    AuthenticationService.ClearCredentials();
    $scope.user = new UserResource();
    $scope.user.email = "";
    $scope.step=1;

    $scope.requestCode = function(){
      if($scope.user.email=="" || $scope.user.email==undefined){
          ToastFactory.error($translate.instant("signup.validation.email"));
      }else{
        $scope.user.$recoveryCode(function(data){
            $state.go("login");
            ToastFactory.success($translate.instant("recovery.send_success"),'',7000);
          },
          function(err){
            ToastFactory.error(err.data.error);
        });
      }
    }
});