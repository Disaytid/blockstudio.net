<?php
	
	namespace Controller;
	
	class Resources
	{
	
		function actionCats() {
		
			$cache = BS::i()->cache->get('resource_categories');
			if(is_array($cache) and !isset($_GET['dropcache'])) { return BS::i()->createAnswer($cache); }
			
			$result = json_encode( unserialize( file_get_contents( 'http://resources.blockstudio.net/files.php' ) ) );
			
			BS::i()->cache->set('resource_categories', $result, 0, 3000);
			
			return BS::i()->createAnswer($result);
			
		}
	
	}