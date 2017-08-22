'use strict';
  
app
.factory('UrlQueryNotation',
    ['$httpParamSerializerJQLike', function ($httpParamSerializerJQLike) {
        var service = {};
        service.toQuery = function (fields, limit, offset) {
            service.response = '';
            angular.forEach(fields, function(v, k){
                service.response += k+':'+v + ',';
            });
            service.response = service.response.replace(/,\s*$/, "")
            if(parseInt(limit) > 0){
                service.response += '&limit='+limit;
            }
            if(parseInt(offset) > 0){
                service.response += '&offset='+offset;
            }
            return service.response.trim();
        };
        return service;
    }]);
