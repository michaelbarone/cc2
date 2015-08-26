'use strict';

var app= angular.module('ControlCenter', ['ngRoute','ngResource','ngIdle','ngScrollTo','inform','ngAnimate']);
app.config(['$routeProvider','informProvider','KeepaliveProvider', 'IdleProvider', function($routeProvider,informProvider,KeepaliveProvider, IdleProvider) {
	// routes
	$routeProvider.when('/login', {templateUrl: 'partials/login.html', controller: 'loginCtrl'});
	$routeProvider.when('/dashboard', {templateUrl: 'partials/dashboard.html', controller: 'dashboardCtrl'});
	//$routeProvider.otherwise({redirectTo: '/dashboard'});
	
	// notifications
	var informDefaults = {
		/* default time to live for each notification */
		ttl: 4000,
		/* default type of notification */
		type: 'info'
	};
	informProvider.defaults(informDefaults);
	
	// ngidle settings
	IdleProvider.idle(20);
	IdleProvider.timeout(60);
	KeepaliveProvider.interval(10);
}]);



app.run(function($rootScope, $location, loginService, Idle){
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
	
	Idle.watch();  // start idle check
});

/*
app.directive('resize', function ($window) {
    return function (scope, element) {
        var w = angular.element($window);
        scope.getWindowDimensions = function () {
            return {
                'h': w.height(),
                'w': w.width()
            };
        };
        scope.$watch(scope.getWindowDimensions, function (scope) {
			var thisid = scope.userdata.linkSelected;
			var myEl = document.getElementById(thisid);		
			myEl.scrollIntoView();

        }, true);

        w.bind('resize', function () {
            scope.$apply();
        });
    }
})*/