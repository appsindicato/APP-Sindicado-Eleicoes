'use strict';

app
  .directive('loadingContent', function($timeout) {
    return {
      restrict: 'A', // only activate on element attribute
      scope : {
        loadingContent : '=loadingContent'
      },
      link: function(scope, elem, attrs) {
        var i = angular.element("<div class='loading-div-content'><i class='fa fa-spin fa-cog fa-fw'></i></div>");
        i.hide();
        elem.append(i);
        scope.$watch("loadingContent", function() {
          if(scope.loadingContent){
            i.show();
            $timeout(function(){elem.focus()});
          }
          else{
            i.hide();
          }
        });

      }
    };
  });