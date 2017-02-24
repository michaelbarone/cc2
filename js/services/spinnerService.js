'use strict';

app.factory('spinnerService', function($rootScope){
	$rootScope.spinnerArray=[];
	var spinnerTimout='';
	function resetTimeout(){
		clearTimeout(spinnerTimout);
		var spinnerTimout = setTimeout(function(){ $rootScope.spinnerArray=[]; }, 15000);
	}
	return{
		add:function(func=null){
			console.log("Spinner: added "+func);
			$rootScope.spinnerArray.push(func);
			resetTimeout();
		},
		remove:function(func=null){
			if($rootScope.spinnerArray.length>0){
				console.log("Spinner: removed "+func);
				var index = $rootScope.spinnerArray.indexOf(func);
				$rootScope.spinnerArray.splice(index, 1);
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



