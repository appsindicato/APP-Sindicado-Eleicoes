'use strict';
  
app
.factory('AuthenticationService',
    ['Base64', '$http', '$cookies', '$rootScope', '$timeout','urlApi',
    function (Base64, $http, $cookies, $rootScope, $timeout,urlApi) {
        var service = {};
        service.Login = function (username, password, callback) {
            $http({ 
                method : "GET",
                url : urlApi+'login', 
                headers: {
                    'Authorization': 'Basic '+ Base64.encode(username + ':' + password)
                }
            })
            .then(function (response) {
                callback(response);
            })
            .catch( function (response){                    
                callback(response);
            });
            // .finally( function(response){
            //     callback(response);
            // });
 
        };
  
        service.SetCredentials = function (id,role_id,username,password) { 
            $rootScope.globals = {
                currentUser: {
                    id: id,
                    role_id: role_id
                }
            };
            $cookies.put("id",$rootScope.globals.currentUser.id);
            $cookies.put("role_id",$rootScope.globals.currentUser.role_id);
            $cookies.put("BasicAuth",'Basic '+Base64.encode(username + ':' + password));
            $http.defaults.headers.common.Authorization = 'Basic '+Base64.encode(username + ':' + password);

        };

        service.ClearCredentials = function () { 
            delete $cookies.remove('id');
            delete $cookies.remove('role_id');
            delete $cookies.remove('BasicAuth');
            delete $http.defaults.headers.common.Authorization;
            // $cookies.put("role_id",$rootScope.globals.currentUser.role_id);
            // $cookies.put("BasicAuth",'Basic '+Base64.encode(username + ':' + password));
            // $http.defaults.headers.common.Authorization = 'Basic '+Base64.encode(username + ':' + password);

        };
  
        return service;
    }])
  
.factory('Base64', function () {
    /* jshint ignore:start */
  
    var keyStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
  
    return {
        encode: function (input) {
            var output = "";
            var chr1, chr2, chr3 = "";
            var enc1, enc2, enc3, enc4 = "";
            var i = 0;
  
            do {
                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);
  
                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;
  
                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }
  
                output = output +
                    keyStr.charAt(enc1) +
                    keyStr.charAt(enc2) +
                    keyStr.charAt(enc3) +
                    keyStr.charAt(enc4);
                chr1 = chr2 = chr3 = "";
                enc1 = enc2 = enc3 = enc4 = "";
            } while (i < input.length);
  
            return output;
        },
  
        decode: function (input) {
            var output = "";
            var chr1, chr2, chr3 = "";
            var enc1, enc2, enc3, enc4 = "";
            var i = 0;
  
            // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
            var base64test = /[^A-Za-z0-9\+\/\=]/g;
            if (base64test.exec(input)) {
                window.alert("There were invalid base64 characters in the input text.\n" +
                    "Valid base64 characters are A-Z, a-z, 0-9, '+', '/',and '='\n" +
                    "Expect errors in decoding.");
            }
            input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
  
            do {
                enc1 = keyStr.indexOf(input.charAt(i++));
                enc2 = keyStr.indexOf(input.charAt(i++));
                enc3 = keyStr.indexOf(input.charAt(i++));
                enc4 = keyStr.indexOf(input.charAt(i++));
  
                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;
  
                output = output + String.fromCharCode(chr1);
  
                if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
                }
                if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
                }
  
                chr1 = chr2 = chr3 = "";
                enc1 = enc2 = enc3 = enc4 = "";
  
            } while (i < input.length);
  
            return output;
        }
    };
  
    /* jshint ignore:end */
});
