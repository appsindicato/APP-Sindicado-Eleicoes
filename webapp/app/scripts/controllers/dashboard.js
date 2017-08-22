'use strict';
/**
* Controller da Dashboard
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('DashboardCtrl', function($scope, $rootScope) {
    $rootScope.page = {
      title : 'page.dashboard.title',
      subtitle: 'page.dashboard.subtitle'
    }
  });
