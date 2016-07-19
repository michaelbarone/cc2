'use strict';

app.factory('spinnerService', function($rootScope){
	$rootScope.spinnerCount=0;
	var spinnerTimout='';
	function resetTimeout(){
		clearTimeout(spinnerTimout);
		var spinnerTimout = setTimeout(function(){ $rootScope.spinnerCount=0; }, 10000);
	}
	
	
	
	return{
		add:function(func=null){
			console.log("Spinner: added "+func)
			$rootScope.spinnerCount=$rootScope.spinnerCount+1;
			resetTimeout();
		},
		remove:function(func=null){
			if($rootScope.spinnerCount>0){
				console.log("Spinner: removed "+func)
				$rootScope.spinnerCount=$rootScope.spinnerCount-1;
				resetTimeout();
			} else {
				clearTimeout(spinnerTimout);
			}
		},
		clear:function(){
			console.log("Spinner: clear all")
			$rootScope.spinnerCount=0;
		}
	};
});



