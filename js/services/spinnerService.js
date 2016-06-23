'use strict';

app.factory('spinnerService', function($rootScope){
	$rootScope.spinnerCount=0;
	var spinnerTimout='';
	function resetTimeout(){
		clearTimeout(spinnerTimout);
		var spinnerTimout = setTimeout(function(){ $rootScope.spinnerCount=0; }, 30000);
	}
	
	
	
	return{
		add:function(){
			resetTimeout();
			$rootScope.spinnerCount=$rootScope.spinnerCount+1;
		},
		remove:function(){
			if($rootScope.spinnerCount>0){
				resetTimeout();
				$rootScope.spinnerCount=$rootScope.spinnerCount-1;
			} else {
				clearTimeout(spinnerTimout);
			}
		}
	};
});



