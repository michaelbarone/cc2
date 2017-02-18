'use strict';

var app= angular.module('ControlCenter', ['ngRoute','ngResource','ngIdle','ngScrollTo','inform','ngAnimate','NgModel','angularModalService','ngDraggable','FBAngular','angular-carousel','chart.js']);
app.config(['$routeProvider','$controllerProvider','informProvider','KeepaliveProvider', 'IdleProvider', function($routeProvider,$controllerProvider,informProvider,KeepaliveProvider, IdleProvider) {
	// routes
	app.settingsController = $controllerProvider.register;
	app.dashboardController = $controllerProvider.register;
	app.chatController = $controllerProvider.register;	
	$routeProvider.when('/login', {templateUrl: 'login.tpl', controller: 'loginCtrl'});
	$routeProvider.when('/dashboard', {templateUrl: 'partials/dashboard.html'});
	$routeProvider.when('/settings', {templateUrl: 'partials/settings.html'});
	$routeProvider.when('/servercheck', {templateUrl: 'partials/servercheck.html'});
	$routeProvider.otherwise({redirectTo: '/login'});
	
	// notifications
	var informDefaults = {
		/* default time to live for each notification */
		ttl: 4000,
		/* default type of notification */
		type: 'info'
	};
	informProvider.defaults(informDefaults);
	
	// ngidle settings
	IdleProvider.idle(360);
	IdleProvider.timeout(0);
	KeepaliveProvider.interval(10);
	
	window.oncontextmenu = function(event) {
		event.preventDefault();
		event.stopPropagation();
		return false;
	};
	
}]);



app.run(function($rootScope, $location, loginService, Idle){
	var routespermission=['/dashboard','/settings'];  //route that require login
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

app.filter('trustUrl', function ($sce) {
	return function(url) {
		return $sce.trustAsResourceUrl(url);
	};
});

app.directive('chat', function () {
	return {
		restrict: 'EAC', //E = element, A = attribute, C = class, M = comment         
		//scope: {
			//@ reads the attribute value, = provides two-way binding, & works with functions
		//	title: '@'
		//},
		//template: '<div>{{ myVal }}</div>',
		templateUrl: 'partials/chat.html',
		//controller: chatCtrl   //Embed a custom controller in the directive
		link: function ($scope) { } //DOM manipulation
	}
});

app.directive('onLongPress', function($timeout) {
	return {
		restrict: 'A',
		link: function($scope, $elm, $attrs) {
			$elm.bind('touchstart', function(evt) {
				// Locally scoped variable that will keep track of the long press
				$scope.longPress = true;

				// We'll set a timeout for 600 ms for a long press
				$timeout(function() {
					if ($scope.longPress) {
						evt.preventDefault();
						// If the touchend event hasn't fired,
						// apply the function given in on the element's on-long-press attribute
						$scope.$apply(function() {
							$scope.$eval($attrs.onLongPress)
						});
					}
				}, 600);
			});

			$elm.bind('touchend', function(evt) {
				// Prevent the onLongPress event from firing
				$scope.longPress = false;
				// If there is an on-touch-end function attached to this element, apply it
				if ($attrs.onTouchEnd) {
					$scope.$apply(function() {
						$scope.$eval($attrs.onTouchEnd)
					});
				}
			});			
			
			$elm.bind('mousedown', function(evt) {
				// Locally scoped variable that will keep track of the long press
				$scope.longPress = true;

				// We'll set a timeout for 600 ms for a long press
				$timeout(function() {
					if ($scope.longPress) {
						evt.preventDefault();
						// If the touchend event hasn't fired,
						// apply the function given in on the element's on-long-press attribute
						$scope.$apply(function() {
							$scope.$eval($attrs.onLongPress)
						});
					}
				}, 600);
			});			
			
			$elm.bind('mouseup', function(evt) {
				// Prevent the onLongPress event from firing
				$scope.longPress = false;
				// If there is an on-touch-end function attached to this element, apply it
				if ($attrs.onTouchEnd) {
					$scope.$apply(function() {
						$scope.$eval($attrs.onTouchEnd)
					});
				}
			});
		}
	};
})

app.controller('ModalController', function($scope, close, data, $http, Carousel, $timeout) {
	$scope.modalContent=[];
	$scope.Carousel = Carousel;
	$scope.initdata=data;
	//console.log(data);
	var returndata = [];


	
	
	$http.get('data/getRoomAddonDataExtended.php?data='+encodeURIComponent(JSON.stringify(data)))
	.success(function(returndata) {
		$scope.modalContent = returndata;
	
		$scope.modalOpen=1;

	
	
		$scope.checkInitData = function(){
			//  1   initdata == modalcontent ==>  timeout function
			//  2   initdata != modalcontent ==>  refresh modalcontent?
			//  3   initdata != parentscope
			
			
			
			if($scope.modalOpen 
				&& $scope.modalContent[0] 
				&& $scope.initdata.addontype==$scope.modalContent[0].addontype 
				&& ($scope.modalContent[0].addonType!='mediaplayer' || ($scope.modalContent[0].addonType=='mediaplayer' && $scope.initdata.info==$scope.modalContent[0].title))
			){
				$timeout(function() {
					$scope.checkInitData();
				}, 5000);
			} else {
				$scope.modalContent=[];
				if($scope.modalOpen) {
					$scope.modalOpen=0;
					console.log("Modal Closed: No Data");
					close("Closed",250);
				}
			}
		}
		
		$timeout(function() {
			$scope.checkInitData();
		}, 150);	

	});

		
		
		
		$scope.closeModal = function() {
			if($scope.modalOpen) {
				$scope.modalContent=[];
				$scope.modalOpen=0;
				close("Closed",250);
			}
		};

});