'use strict';

app.factory('spinnerService', function($rootScope){
	$rootScope.spinnerArray=[];
	var spinnerTimout='';
	function resetTimeout(){
		clearTimeout(spinnerTimout);
		var spinnerTimout = setTimeout(function(){ $rootScope.spinnerArray=[]; }, 30000);
	}
	return{
		add:function(func=null,limit=0){
			if(limit>0){
				var index = $rootScope.spinnerArray.indexOf(func);
				if(index>-1){
					//already added
					return;
				}				
			}
			console.log("Spinner: added "+func);
			$rootScope.spinnerArray.push(func);
			resetTimeout();
		},
		remove:function(func=null){
			if($rootScope.spinnerArray.length>0){
				var index = $rootScope.spinnerArray.indexOf(func);
				if(index>-1){
					$rootScope.spinnerArray.splice(index, 1);
					console.log("Spinner: removed "+func);
				}
				resetTimeout();
			} else {
				clearTimeout(spinnerTimout);
			}
		},
		clear:function(){
			console.log("Spinner: clear all");
			$rootScope.spinnerArray=[];
		}
	};
});



