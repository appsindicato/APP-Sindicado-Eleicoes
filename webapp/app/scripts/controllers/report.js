'use strict';
/**
* Controller dos Relatórios
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
app
  .controller('ReportCtrl', function($scope, $rootScope,$timeout,CoreResource,CityResource,urlApi,ToastFactory,$translate,$http,cfpLoadingBar) {
    $rootScope.page = {
      title : 'page.report.title',
      subtitle: 'page.report.subtitle'
    }

    $timeout(function(){
        $scope.cores = CoreResource.query({},function(){
        $scope.citys = CityResource.query();
      });
     },1000);

    function downloadPdf (name,result){
        var fileName = name+".pdf";
        var a = document.createElement("a");
        document.body.appendChild(a);
        a.style = "display: none";
        var file = new Blob([result.data], {type: 'application/pdf'});
        var fileURL = window.URL.createObjectURL(file);
        a.href = fileURL;
        a.download = fileName;
        a.click();
    }
    $scope.submitPlaque = function(){
    	cfpLoadingBar.start();
    	$http.post(urlApi+"report",{core_id:$scope.core}, { responseType: 'arraybuffer' }).then(function(result){
    		downloadPdf("chapas",result);
    		cfpLoadingBar.complete();
    	});
    }

    $scope.submitRep = function(){
    	if(!$scope.city){
            ToastFactory.error($translate.instant("report.error.city"));
    	}
    	else{
    		cfpLoadingBar.start();
	    	$http.post(urlApi+"report/adviser",{city_id:$scope.city}, { responseType: 'arraybuffer' }).then(function(result){
	    		downloadPdf("representantes",result)
    			cfpLoadingBar.complete();
	    	});
    	}
    }
  });
