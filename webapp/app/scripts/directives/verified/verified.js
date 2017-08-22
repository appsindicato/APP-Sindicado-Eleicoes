'use strict';
app
	.directive('verified',function($translate,$rootScope){
		return {
	        templateUrl:'scripts/directives/verified/verified.html',
	        restrict: 'E',
	        scope: false,
	        transclude: false,
	        replace: true,
    		link: function($scope, elem, attr, ctrl) {

    		}
		}
	});