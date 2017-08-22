'use strict';
/**
* Controller Principal da Urna
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('MainCtrl', function($scope,$timeout,$mdSidenav,$log, $rootScope, UserResource, $cookies, ToastFactory,$mdMedia,$cookieStore, $translate, $state) {
    /**
     * funcoes do layout
     */
    /**
     * Sidebar Toggle & Cookie Control
     */
    var mobileView = 1400;
    var iconOpen = "menu";
    var iconClose = "arrow_forward";
    $scope.iconSideBar=iconOpen;
    $scope.getWidth = function() {
        return window.innerWidth;
    };

    $scope.$watch($scope.getWidth, function(newValue, oldValue) {
        if (newValue >= mobileView) {
            iconOpen = "menu";
            iconClose = "arrow_forward";
            if (angular.isDefined($cookieStore.get('toggle'))) {
                $scope.toggle = ! $cookieStore.get('toggle') ? false : true;
            } else {
                $scope.toggle = true;
            }
        } else {
            iconOpen = "menu";
            iconClose = "menu";
            $scope.toggle = false;
        }
        if($scope.toggle){
          $scope.iconSideBar=iconOpen;
        }
        else{
          $scope.iconSideBar=iconClose;
        }
    });

    $scope.toggleSidebar = function() {
        $scope.toggle = !$scope.toggle;
        $cookieStore.put('toggle', $scope.toggle);
        if($scope.toggle){
          $scope.iconSideBar=iconOpen;
        }
        else{
          $scope.iconSideBar=iconClose;
        }
    };

    window.onresize = function() {
        $scope.$apply();
    };

    /******* */
    
    $scope.$mdMedia = $mdMedia;
    $scope.toggleLeft = buildDelayedToggler('left');
    /**
     * Supplies a function that will continue to operate until the
     * time is up.
     */
    function debounce(func, wait, context) {
      var timer;
      return function debounced() {
        var context = $scope,
            args = Array.prototype.slice.call(arguments);
        $timeout.cancel(timer);
        timer = $timeout(function() {
          timer = undefined;
          func.apply(context, args);
        }, wait || 10);
      };
    }

    /**
     * Build handler to open/close a SideNav; when animation finishes
     * report completion in console
     */
    function buildDelayedToggler(navID) {
      return debounce(function() {
        // Component lookup should always be available since we are not using `ng-if`
        $mdSidenav(navID)
          .toggle()
          .then(function () {
          });
      }, 200);
    }

    function buildToggler(navID) {
      return function() {
        // Component lookup should always be available since we are not using `ng-if`
        $mdSidenav(navID)
          .toggle()
          .then(function () {
          });
      }
    }

    $scope.closeSideBar = function(){
        $mdSidenav("left")
          .toggle();
    }
  });
