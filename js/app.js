'use strict';

var app= angular.module('ControlCenter', ['ngRoute','ngResource','ngScrollTo','inform','ngAnimate']);
app.config(['$routeProvider','informProvider', function($routeProvider,informProvider) {
	$routeProvider.when('/login', {templateUrl: 'partials/login.html', controller: 'loginCtrl'});
	$routeProvider.when('/dashboard', {templateUrl: 'partials/dashboard.html', controller: 'dashboardCtrl'});
	//$routeProvider.otherwise({redirectTo: '/dashboard'});
	var informDefaults = {
		/* default time to live for each notification */
		ttl: 4000,
		/* default type of notification */
		type: 'info'
	};
	informProvider.defaults(informDefaults);  
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