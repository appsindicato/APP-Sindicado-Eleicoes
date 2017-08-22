'use strict';
app
	.directive('header',function(){
		return {
	        templateUrl:'scripts/directives/header/header.html',
	        // restrict: 'E',
	        scope: {title: '@title'},
	        replace: true,
	        controller:function($scope){
	      	}
		}
	});