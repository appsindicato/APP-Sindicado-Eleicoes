'use strict';
/**
* Controller do Login
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('LoginCtrl', function($translate,$scope,$cookies,$state,AuthenticationService,ToastFactory, $timeout, $rootScope, $mdDialog) {
    AuthenticationService.ClearCredentials();
    $scope.user = {};
    $scope.user.email = "";
    $scope.user.password = "";
    $rootScope.globals.currentUser = {};
    $rootScope.globals.currentUser.credits = 0;
    $scope.step = 'email';
    
    $scope.newUser = $rootScope.newUser;
    $rootScope.newUser = false;

    $timeout(function(){
      $("#email").focus();
    },200);

    $scope.valid = function(){
      if($scope.selectedIndex==1){
        var vl= 1;
        angular.forEach($scope.user, function(v,k){
          if(v=="" || v==undefined){
            vl= 0;
            ToastFactory.error($translate.instant("signup.validation."+k))
          }
        });
        $timeout(function(){
          if(vl){$scope.login()}}, 100);
      }
      else{
        if($scope.user.email){
          $scope.selectedIndex = 1;
          $timeout(function(){
            $("#password").focus();
          },200); 
        }
        else{
          ToastFactory.error($translate.instant("signup.validation.email"));
        }
      }
    }

  	$scope.login = function(){
      $scope.loading=true;
          
      AuthenticationService.Login($scope.user.email, $scope.user.password, function(response) {
          if(response.status==200){
              if(response.data.id>0){                
                AuthenticationService.SetCredentials(response.data.id, response.data.profile,$scope.user.email, $scope.user.password);
                ToastFactory.success($translate.instant('login.success'));
                $rootScope.globals.currentUser.credits = parseInt(response.data.credits);
                $rootScope.globals.currentUser.name = response.data.first_name +' '+ response.data.last_name;
                $state.transitionTo('app.home');
              }
              else{
                $scope.loading=false;
                response.message = $translate.instant('login.fail');
              }
          } else {
              if(response.status==401){
                  response.message = $translate.instant('login.fail');
              }
              else{
                  response.message = response.message = $translate.instant('connection.fail');
              }
              $scope.loading=false;
              ToastFactory.error(response.message);
              AuthenticationService.ClearCredentials();
          }
      });
  	}
});