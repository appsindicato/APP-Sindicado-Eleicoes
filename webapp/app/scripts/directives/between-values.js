'use strict';

app.
  filter('betweenValues', function() {
    return function(arr,value,initField,endField) {
      var out = [];
      angular.forEach(arr, function(obj){
        if(value>=obj[initField] && value<=obj[endField]){
          out.push(obj);
        }
      });
      return out;
    }
  });