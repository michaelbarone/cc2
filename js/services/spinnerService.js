'use strict';

app.factory('spinnerService', function($rootScope){
	$rootScope.spinnerCount=0;
	return{
		add:function(){
			$rootScope.spinnerCount=$rootScope.spinnerCount+1;
		},
		remove:function(){
			if($rootScope.spinnerCount>0){
				$rootScope.spinnerCount=$rootScope.spinnerCount-1;
			}
		}
	};
});