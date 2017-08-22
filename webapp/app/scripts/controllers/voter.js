'use strict';
app
	.filter('VoterStatus', function() {
		return function(text) {
		return text == 1 ? 'Apto' :  'Inapto';
		}
	})
	.filter('VoterTransit', function() {
		return function(text) {
		return text == 1 ? 'Sim' :  'Não';
		}
	})
  .controller('ListVoterCtrl', function($scope, $rootScope, VoterResource, UrlQueryNotation,CoreResource,CityResource, $timeout, SmartTableFactory) {
    $rootScope.page = {
      title : 'page.voter.title',
      subtitle: 'page.voter.subtitle'
    }

    $scope.itemsByPage = 50;
    $timeout(function(){
        $scope.cores = CoreResource.query({},function(){
        $scope.citys = CityResource.query();
      });
     },1500);

    $scope.displayed = [];
    $scope.callServer = function callServer(tableState) {
      $scope.isLoading = true;
      var pagination = tableState.pagination;
      var start = pagination.start || 0;     // This is NOT the page number, but the index of item in the list that you want to use to display the table.
      var number = pagination.number || 10;  // Number of entries showed per page.
      var p = SmartTableFactory.prepareFields(tableState);
      SmartTableFactory.getPage(VoterResource,start, number, tableState).then(function (result) {
        $scope.displayed = result;
        VoterResource.count(p,function(d){
          tableState.pagination.numberOfPages = Math.ceil(d.total / number);
        });//set the number of pages so the pagination can update
        $scope.notFound = false;
        $scope.isLoading = false;
      },function(){
        $scope.isLoading = false;
        $scope.notFound = true;
      });
    };

    // $scope.data = VoterResource.query({q: UrlQueryNotation.toQuery({}, 25)});
  });
