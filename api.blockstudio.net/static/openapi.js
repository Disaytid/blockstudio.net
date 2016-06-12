var BlockStudio = {
	'url': 'http://blockstudio.net/',
	'server_name': 'blockstudio.net',
	'api_url': 'http://api.blockstudio.net',
	'title': 'Block Studio',
	'script_version': '1452865252',
	'contact': 'office@disaytid.ru'
};

BlockStudio.config = {
	'cors': false,
	'user': 0,
	'token': '0'
};



BlockStudio.isSupporting = function() { 
	if(typeof $.get != 'function')
	{ 
		return false;
	}
	return true;
};

BlockStudio.prepare = function() {
	token = localStorage.getItem('token');
	user = localStorage.getItem('user');
	if(typeof token != 'string' || typeof user != 'number')
	{
		return false;
	}
	return true;
};

BlockStudio.query = function(cat, method, params, callback) {
	prefix = BlockStudio.config.cors ? BlockStudio.api_url : '';
	$.get(prefix + '/' + cat + '/' + method, params, callback, 'text');
}

BlockStudio.squery = function(cat, method, params, callback) {
	if(!params.id)
		params.id = localStorage.getItem('id');
	
	if(!params.token)
		params.token = localStorage.getItem('token');
	
	BlockStudio.query(cat, method, params, callback);
}