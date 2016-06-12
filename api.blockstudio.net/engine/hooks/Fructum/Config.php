<?php
	/**
	 * This class includes default configuration. To change it, use hooks or extensions
	 *
	 * @author Mike Chip
	 */
	 
	namespace Fructum;
	
	class Config
	{
		
		const cache = '\Memcache';
		const sql_host = 'disaytid.ru';
		const sql_user = 'blockstudio';
		const sql_password = 'iamsexyand444';
		const sql_database = 'blockstudio';
		const sql_unique = 'id';
		
		const session_handler = 'native';
		const script_time_limit = 0;
		
	}