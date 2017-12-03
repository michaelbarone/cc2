'use strict';

var app= angular.module('ControlCenter', ['ngSanitize','ngRoute','ngResource','ngIdle','ngScrollTo','inform','ngAnimate','NgModel','angularModalService','ngDraggable','FBAngular','angular-carousel','chart.js','toggle-switch']);
app.config(['$routeProvider','$controllerProvider','informProvider','KeepaliveProvider', 'IdleProvider', function($routeProvider,$controllerProvider,informProvider,KeepaliveProvider, IdleProvider) {
	/* routes */
	app.settingsController = $controllerProvider.register;
	app.dashboardController = $controllerProvider.register;
	app.chatController = $controllerProvider.register;	
	$routeProvider.when('/login', {templateUrl: 'login.tpl', controller: 'loginCtrl'});
	$routeProvider.when('/dashboard', {templateUrl: 'partials/dashboard.html'});
	$routeProvider.when('/settings', {templateUrl: 'partials/settings.html'});
	$routeProvider.when('/servercheck', {templateUrl: 'partials/servercheck.html'});
	$routeProvider.otherwise({redirectTo: '/login'});
	
	/* notifications */
	var informDefaults = {
		/* default time to live for each notification */
		ttl: 4000,
		/* default type of notification */
		type: 'info'
	};
	informProvider.defaults(informDefaults);
	
	/* ngidle settings */
	IdleProvider.idle(360);
	IdleProvider.timeout(0);
	KeepaliveProvider.interval(10);
	
	window.oncontextmenu = function(event) {
		event.preventDefault();
		event.stopPropagation();
		return false;
	};
	
}]);

app.run(function($rootScope, $location, userService, Idle, cron){
	/* routes that require login */
	var routespermission=['/dashboard','/settings'];
	$rootScope.$on('$routeChangeStart', function(){
		/* clear any existing spinner info from previous route */
		$rootScope.spinnerArray=[];
		if( routespermission.indexOf($location.path()) !=-1)
		{
			var connected=userService.islogged();
			connected.then(function(msg){
				if(msg.data==="failedAuth" || !msg.data) $location.path('/login');
			});
		}
		$rootScope.testrun = 0;
		$rootScope.debug = 0;	
		if($location.search()['command']){
			switch($location.search()['command']){
				case 'test':
					console.log('Test Mode Enabled');
					$rootScope.testrun = 1;
					break;
				case 'debug':
				case 'verbose':
					console.log('Debug Mode Enabled');
					$rootScope.debug = 1;				
					break;
			}
		}
	});
	if($location.path()!="/login"){
		cron.start();
	}
	/* start idle check */
	Idle.watch();
});