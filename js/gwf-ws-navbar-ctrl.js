'use strict';
angular.module('gwf5').
controller('GWFWSNavbarCtrl', function($rootScope, $scope, GWFWebsocketSrvc, GWFErrorSrvc) {
	$scope.data.connection = {
		state: false,
	};
	
	$scope.connect = function() {
		console.log('GWFWSNavbarCtrl.connect()');
		GWFWebsocketSrvc.connect()['catch']($scope.connectionFailed);
	};
	
	$rootScope.$on('gws-ws-open', function(event) {
		console.log('GWFWSNavbarCtrl.$on-gws-ws-open()', event);
		$scope.data.connection.state = true;
	});
	$rootScope.$on('gws-ws-disconnect', function(event) {
		$scope.data.connection.state = false;
	});
	
	$scope.connectionFailed = function(error) {
		GWFErrorSrvc.showError('Cannot connect to websocket server.', 'Websocket');
	};
	
});
