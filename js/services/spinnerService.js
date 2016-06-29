'use strict';

app.factory('spinnerService', function($rootScope){
	$rootScope.spinnerCount=0;
	var spinnerTimout='';
	function resetTimeout(){
		clearTimeout(spinnerTimout);
		var spinnerTimout = setTimeout(function(){ $rootScope.spinnerCount=0; }, 10000);
	}
	
	
	
	return{
		add:function(){
			$rootScope.spinnerCount=$rootScope.spinnerCount+1;
			resetTimeout();
		},
		remove:function(){
			if($rootScope.spinnerCount>0){
				$rootScope.spinnerCount=$rootScope.spinnerCount-1;
				resetTimeout();
			} else {
				clearTimeout(spinnerTimout);
			}
		},
		clear:function(){
			$rootScope.spinnerCount=0;
		}
	};
});



