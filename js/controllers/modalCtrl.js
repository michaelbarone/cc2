'use strict';

app.controller('ModalController', ['$scope','close','$http', 'Carousel', '$timeout', 'data', 'spinnerService', function($scope,close,$http,Carousel,$timeout,data,spinnerService) {
	$scope.closeModal = function() {
		if($scope.modalOpen) {
			//$scope.modalContent=[];
			$scope.modalOpen=0;
			close("Closed",250);
		}
	};

	$scope.initdata=data;
	$scope.Carousel = Carousel;
	$scope.modalOpen=1;
	$scope.confirmDelete=false;

	$scope.password="";
	$scope.passwordConfirm="";
	$scope.currentPassword="";
	
}]);