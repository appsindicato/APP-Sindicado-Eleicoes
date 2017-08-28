'use strict';
/**
* Inicia o aplicativo WEB, define as configuracoes iniciais e as rotas das views
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
var app = angular
  .module('app', [
    'ngAnimate',
    'ngCookies',
    'ngResource',
    'ngSanitize',
    'picardy.fontawesome',
    'ui.bootstrap', 
    'ui.router',
    'ui.utils',
    'angular-loading-bar',
    'angularMoment',
    'ui.utils.masks',
    'ngMask',
    'ngMaterial',
    'pascalprecht.translate',
    'angular.morris',
    'hljs',
    'chart.js',
    'ui.slimscroll',
    'tmh.dynamicLocale',
    'appTemplates',
    '720kb.socialshare',
    'ngMdIcons',
    'ngFileUpload',
    'toastr',
    'smart-table'
  ])
  //URl da API
  .value('urlApi','http://localhost/urna/sistema/api/')
  .factory('httpRequestInterceptor', function ($q, $location) {
      return {
          'responseError': function(rejection) {
              if(rejection.status === 401 || rejection.status === 403){
                  $location.path('/login');
              }
              return $q.reject(rejection);
           }
       };
  })
  .config(function(toastrConfig) {
    angular.extend(toastrConfig, {
      autoDismiss: true,
      containerId: 'toast-container',
      maxOpened: 0,
      newestOnTop: true,
      positionClass: 'toast-top-center',
      preventDuplicates: false,
      preventOpenDuplicates: false,
      allowHtml: true,
      target: 'body',
      closeButton: true,
      timeOut: 5000,
      progressBar: true
    });
  })
  .config(['$translateProvider', function($translateProvider) {
      $translateProvider.useStaticFilesLoader({
        prefix: 'languages/',
        suffix: '.json'
      });
      $translateProvider.useLocalStorage();
      $translateProvider.preferredLanguage('pt_br');
      $translateProvider.useSanitizeValueStrategy(null);
  }])
  .config(function(tmhDynamicLocaleProvider) {
    tmhDynamicLocaleProvider.localeLocationPattern('scripts/angular/i18n/angular-locale_{{locale}}.js');
  })
.config(function ($mdThemingProvider) {
    var customPrimary = {
        '50': '#a8bac3',
        '100': '#99aeb8',
        '200': '#8aa2ae',
        '300': '#7a96a3',
        '400': '#6b8a99',
        '500': '#607D8B',
        '600': '#566f7c',
        '700': '#4b626d',
        '800': '#41545e',
        '900': '#36474f',
        'A100': '#b7c6cd',
        'A200': '#c6d2d8',
        'A400': '#d5dee2',
        'A700': '#2c3940',
        'contrastDefaultColor': 'light'
    };
    $mdThemingProvider
        .definePalette('customPrimary', 
                        customPrimary);

    var customAccent = {
        '50': '#192d29',
        '100': '#233d38',
        '200': '#2c4d47',
        '300': '#355d55',
        '400': '#3e6e64',
        '500': '#487e73',
        '600': '#5a9e91',
        '700': '#69a99d',
        '800': '#79b3a7',
        '900': '#89bcb2',
        'A100': '#5a9e91',
        'A200': '#518E82',
        'A400': '#487e73',
        'A700': '#99c5bc',
        'contrastDefaultColor': 'light'
    };
    $mdThemingProvider
        .definePalette('customAccent', 
                        customAccent);

    var customWarn = {
        '50': '#ffa9a9',
        '100': '#ff9090',
        '200': '#ff7676',
        '300': '#ff5d5d',
        '400': '#ff4343',
        '500': '#FF2A2A',
        '600': '#ff1010',
        '700': '#f60000',
        '800': '#dc0000',
        '900': '#c30000',
        'A100': '#ffc3c3',
        'A200': '#ffdcdc',
        'A400': '#fff6f6',
        'A700': '#a90000',
        'contrastDefaultColor': 'light'
    };
    $mdThemingProvider
        .definePalette('customWarn', 
                        customWarn);

    var customBackground = {
        '50': '#fdfdfd',
        '100': '#f0f0f0',
        '200': '#e3e3e3',
        '300': '#d6d6d6',
        '400': '#cacaca',
        '500': '#BDBDBD',
        '600': '#b0b0b0',
        '700': '#a3a3a3',
        '800': '#979797',
        '900': '#8a8a8a',
        'A100': '#ffffff',
        'A200': '#ffffff',
        'A400': '#ffffff',
        'A700': '#7d7d7d',
        'contrastDefaultColor': 'light'
    };
    $mdThemingProvider
        .definePalette('customBackground', 
                        customBackground);

   $mdThemingProvider.theme('default')
       .primaryPalette('customPrimary')
       .accentPalette('customAccent')
       .warnPalette('customWarn')
       .backgroundPalette('customBackground')
})
.config(function ($provide) {
    $provide.decorator('dateFilter', function ($delegate) {
        return function () {
            // Check if the date format argument is not provided
            if (!arguments[1]) { 
                arguments[1] = 'dd MMMM @ HH:mm:ss';
            }
            if(arguments[0]){
              if(arguments[0]<9999999999999){ // check if unix timestamp
                arguments[0] = arguments[0]*1000;
              }
            }
            var value = $delegate.apply(null, arguments);
            return value;
        };
    })
})
  .config(['$httpProvider',function($httpProvider){
    $httpProvider.interceptors.push('httpRequestInterceptor');
  }])
  .run(['$rootScope', '$state', '$stateParams', '$location', '$cookies', '$http','tmhDynamicLocale', function($rootScope, $state, $stateParams, $location, $cookies, $http,tmhDynamicLocale) {
      $rootScope.globals = {};
      $rootScope.globals.currentUser = {};
      $rootScope.globals.currentUser.credits = 1;
      if($cookies.get("BasicAuth")!==""){
          $http.defaults.headers.common.Authorization = $cookies.get("BasicAuth");

      }
      $rootScope.$on('$locationChangeStart', function (event, next, current) {
      });
      tmhDynamicLocale.set('pt-br');
  }])
  .config(['$stateProvider','$urlRouterProvider',function ($stateProvider,$urlRouterProvider) {

    $urlRouterProvider.otherwise('/login');

    $stateProvider
      .state('app', {
        url:'/app',
        templateUrl: 'views/tmpl/pages/main.html',
        controller: 'MainCtrl'
    }) 
    .state('app.home',{
      templateUrl:'views/tmpl/pages/home.html',
      url:'/home',
      controller : "DashboardCtrl"
    })
    .state('login',{
      templateUrl:'views/tmpl/pages/login.html',
      url:'/login',
      controller : "LoginCtrl"
    })
    .state('recovery',{
      templateUrl:'views/tmpl/pages/recovery.html',
      url:'/recovery',
      controller : "RecoveryCtrl"
    })
    .state('recoveryChangePassword',{
      templateUrl:'views/tmpl/pages/recovery.html',
      url:'/password_recovery/:id/:token',
      controller : "RecoveryChangePasswordCtrl"
    })
    //user
    .state('app.addUser',{
      url:'/user',
      controller: 'UserCtrl',
      templateUrl:'views/tmpl/modal/user.html'
    })
    .state('app.listUser',{
      url:'/listuser',
      controller: 'ListUserCtrl',
      templateUrl:'views/tmpl/pages/listUser.html'
    })
    //box
    .state('app.listBox',{
      url:'/listbox',
      controller: 'ListBoxCtrl',
      templateUrl:'views/tmpl/pages/listBox.html'
    })
    .state('app.addBox',{
      url:'/box',
      controller: 'BoxCtrl',
      templateUrl:'views/tmpl/pages/listBox.html'
    })
    //plaque
    .state('app.addPlaque',{
      url:'/addplaque',
      controller: 'PlaqueCtrl',
      templateUrl:'views/tmpl/pages/plaque.html'
    })
    .state('app.listPlaque',{
      url:'/listplaque',
      controller: 'ListPlaqueCtrl',
      templateUrl:'views/tmpl/pages/listPlaque.html'
    })
    //politician
    .state('app.addPolitician',{
      url:'/addpolitician',
      controller: 'PoliticianCtrl',
      templateUrl:'views/tmpl/pages/politician.html'
    })
    .state('app.listPolitician',{
      url:'/listpolitician',
      controller: 'ListPoliticianCtrl',
      templateUrl:'views/tmpl/pages/listPolitician.html'
    })
    //Core
    .state('app.listCore',{
      url:'/core',
      controller: 'ListCoreCtrl',
      templateUrl:'views/tmpl/pages/listCore.html'
    })
    //others
    .state('app.listCity',{
      url:'/listCity',
      controller: 'ListCityCtrl',
      templateUrl:'views/tmpl/pages/listCity.html'
    })
    .state('app.report',{
      url:'/report',
      controller: 'ReportCtrl',
      templateUrl:'views/tmpl/pages/report.html'
    })
    .state('app.listVoter',{
      url:'/listvoter',
      controller: 'ListVoterCtrl',
      templateUrl:'views/tmpl/pages/listVoter.html'
    })
    .state('app.config',{
      url:'/config',
      controller: 'ConfigCtrl',
      templateUrl:'views/tmpl/pages/config.html'
    })
    .state('app.send',{
      url:'/send',
      controller: 'SendCtrl',
      templateUrl:'views/tmpl/pages/send.html'
    });
  }]);
