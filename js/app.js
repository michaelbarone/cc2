'use strict';
// Declare app level module which depends on filters, and services
var app= angular.module('myApp', ['ngRoute','ngResource','ngScrollTo']);
app.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/login', {templateUrl: 'partials/login.html', controller: 'loginCtrl'});
  $routeProvider.when('/dashboard', {templateUrl: 'partials/dashboard.html', controller: 'dashboardCtrl'});
  $routeProvider.otherwise({redirectTo: '/login'});
}]);



app.run(function($rootScope, $location, loginService){
	var routespermission=['/dashboard'];  //route that require login
	$rootScope.$on('$routeChangeStart', function(){
		if( routespermission.indexOf($location.path()) !=-1)
		{
			var connected=loginService.islogged();
			connected.then(function(msg){
				if(msg.data==="failedAuth" || !msg.data) $location.path('/login');
			});
		}
	});
});