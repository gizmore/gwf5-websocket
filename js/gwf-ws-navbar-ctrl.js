'use strict';
angular.module('gwf5').
controller('GWFWSNavbarCtrl', function($scope, GWFWebsocketSrvc) {
	$scope.data.connection = {
		state: false,
	};
	
	$scope.connect = function() {
		console.log('GWFWSNavbarCtrl.connect()');
		GWFWebsocketSrvc.connect();
	};
});
