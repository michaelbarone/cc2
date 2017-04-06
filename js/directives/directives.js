'use strict';


app.directive('chat', function () {
	return {
		restrict: 'EAC', /* E = element, A = attribute, C = class, M = comment */
		/*
		scope: {
			//@ reads the attribute value, = provides two-way binding, & works with functions
			title: '@'
		},
		template: '<div>{{ myVal }}</div>',
		controller: chatCtrl,   //Embed a custom controller in the directive 
		*/
		templateUrl: 'partials/chat.html',
		link: function ($scope) { }
	}
});

app.directive('onLongPress', function($timeout) {
	return {
		restrict: 'A',
		link: function($scope, $elm, $attrs) {
			$elm.bind('touchstart', function(evt) {
				/* Locally scoped variable that will keep track of the long press */
				$scope.longPress = true;

				/* We'll set a timeout for 600 ms for a long press */
				$timeout(function() {
					if ($scope.longPress) {
						evt.preventDefault();
						/*
						If the touchend event hasn't fired,
						apply the function given in on the element's on-long-press attribute 
						*/
						$scope.$apply(function() {
							$scope.$eval($attrs.onLongPress)
						});
					}
				}, 600);
			});

			$elm.bind('touchend', function(evt) {
				/* Prevent the onLongPress event from firing */
				$scope.longPress = false;
				/* If there is an on-touch-end function attached to this element, apply it */
				if ($attrs.onTouchEnd) {
					$scope.$apply(function() {
						$scope.$eval($attrs.onTouchEnd)
					});
				}
			});			
			
			$elm.bind('mousedown', function(evt) {
				/* Locally scoped variable that will keep track of the long press */
				$scope.longPress = true;

				/* We'll set a timeout for 600 ms for a long press */
				$timeout(function() {
					if ($scope.longPress) {
						evt.preventDefault();
						/*
						If the touchend event hasn't fired,
						apply the function given in on the element's on-long-press attribute 
						*/
						$scope.$apply(function() {
							$scope.$eval($attrs.onLongPress)
						});
					}
				}, 600);
			});			
			
			$elm.bind('mouseup', function(evt) {
				/* Prevent the onLongPress event from firing */
				$scope.longPress = false;
				/* If there is an on-touch-end function attached to this element, apply it */
				if ($attrs.onTouchEnd) {
					$scope.$apply(function() {
						$scope.$eval($attrs.onTouchEnd)
					});
				}
			});
		}
	};
});