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
/*	
	$scope.modalContent=[];
	var modalReturnData = [];
	var roomid = initdata.roomid;
	var id = initdata.id;
	$scope.checkInitData = function(){
			
			if($scope.modalOpen 
				&& $scope.$parent.room_addons[0][roomid][id]['displayInfo']['info'] 
			){
				$timeout(function() {
					$scope.checkInitData();
				}, 5000);
			} else {
				if($scope.modalOpen) {
					$scope.modalOpen=0;
					console.log("Modal Closed: No Data");
					close("Closed",250);
				}
			}

	};
	
	$timeout(function() {
		$scope.checkInitData();
	}, 150);
	
	*/

	
	
	
	/*
	delete this below file if this code gets removed
	$http.get('data/getRoomAddonDataExtended.php?data='+encodeURIComponent(JSON.stringify(data)))
	.success(function(modalReturnData) {
		$scope.modalContent = modalReturnData;
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
	*/
}]);