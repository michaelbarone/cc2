'use strict';

app.filter('abvTitle', function(){
	return function (item) {
		
		
		// var myStr = "John P White";
		var myStr = item;
		var matches ='';
		
		var matches = myStr.match(/\b(\w)/g);
		if (/\s/.test(myStr)) {
			// It has any kind of whitespace
			matches = myStr.match(/\b(\w)/g);
			matches = matches.join('');
		} else {
			matches = myStr.substring(0, 3);
		}
		
		
		
		return matches;
	}
	
	
});



