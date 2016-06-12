BlockStudio.helpers = {};

BlockStudio.helpers.userInfo = function(id, callback) {
	if(localStorage.getItem('blockstudio_data' + id) != null) {
		callback( JSON.parse( localStorage.getItem('blockstudio_data' + id) ) );
	}
	else {
		BlockStudio.query('user', 'data', {'id': id}, function(data) {
			localStorage.setItem('blockstudio_data' + id, data)
			callback( JSON.parse(data) );
		});
	}
};