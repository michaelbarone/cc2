'use strict';

app.filter('abvTitle', function(){
	return function (item) {

		var myStr = item;
		var matches ='';
		
		var matches = myStr.match(/\b(\w)/g);
		if (/\s/.test(myStr)) {
			// It has any kind of whitespace
			matches = myStr.match(/\b(\w)/g);
			matches = matches.join('');
		} else {
			matches = myStr.substring(0, 2).charAt(0).toUpperCase() + myStr.substring(0, 2).charAt(1).toLowerCase();
		}	
		
		return matches;
	}
});

app.filter('noHTTP', function(){
	return function (item) {

		var myStr = item;

		var urlNoProtocol = myStr.replace(/^https?\:\/\//i, "");

		return urlNoProtocol;
	}
});

app.filter('orderObjectBy', function() {
  return function(items, field, reverse) {
    var filtered = [];
    angular.forEach(items, function(item) {
      filtered.push(item);
    });
    filtered.sort(function (a, b) {
      return (a[field] > b[field] ? 1 : -1);
    });
    if(reverse) filtered.reverse();
    return filtered;
  };
});

app.filter('positive', function() {
	return function(input) {
		if (!input) {
			return 0;
		}
		return Math.abs(input);
	};
});