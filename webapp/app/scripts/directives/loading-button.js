'use strict';

app
  .directive('loadingButton', function() {
    return {
      restrict: 'A', // only activate on element attribute
      scope : {
        loadingButton : '=loadingButton',
        ngDisabled : '=ngDisabled'
      },
      link: function(scope, elem, attrs) {
        var i = angular.element("<i class='fa fa-spin fa-cog fa-fw'></i>");
        i.hide();
        elem.append(i);
        scope.$watch("loadingButton", function() {
          if(scope.loadingButton){
            i.show();
            elem.attr("disabled",true);
          }
          else{
            i.hide();
            if(!scope.ngDisabled){
              elem.attr("disabled",false);
            }
          }
        });

      }
    };
  });