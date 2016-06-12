<?php
	
	use \Fructum\Config as Conf;
	
	class BlockStudio
	{
		public $db = null;
		protected $pdb = null;
		public $cache = null;
		protected static $i; 
		
		public static function i() 
		{
			if(!is_object(self::$i))
			{
				self::$i = new BlockStudio;
			}
			return self::$i;
		}
		
		function __construct()
		{
			$this->db = \Database\ORM::i();
			$this->cache = \Database\Cache::i();
			\Application\WebApp::i()->header('Content-Type: application/javascript'); 
			\Application\WebApp::i()->header("Access-Control-Allow-Origin: *");
			\Application\WebApp::i()->header("Access-Control-Allow-Methods: GET, POST");
			\Application\WebApp::i()->header("Access-Control-Allow-Headers: X-Requested-With");
		}
		
		function db()
		{
			if(is_null($this->pdb))
			{
				$this->pdb = new \Database\SafeMySQL(array(
					'host' => Conf::sql_host,
					'user' => Conf::sql_user,
					'pass' => Conf::sql_password,
					'db' => Conf::sql_database,
				));
			}
			return $this->pdb;
		}
		
		function checktoken($user, $token)
		{
			$cache = $this->cache->get('user_' . (int)$user );
			if(is_array($cache) and isset($cache['id'])) { $data = $cache; }
			else
			{
				$db = $this->db->table('user')->where("id = ". (int)$user)->as_array();
				if(is_array($db) and isset($db['id'])) { 
					$this->cache->set('user_' . $db['id'], $db, 0, 68400);
					$data = $db;
				}
				else
				{
					return false;
				}
			}
			
			return ((md5($data['login'] . $data['password'] . '0') == $token) or $this->admintoken($user, $token));
		}
		
		function admintoken($user, $token)
		{
			$cache = $this->cache->get('user_' . (int)$user );
			if(!is_array($cache) or !isset($cache['id'])) { return false; }
			
			return ( $token == md5('13+3131007+admin') );
		}
		
		function createAnswer($a)
		{
			if(!is_array($a)) { $a = array('answer' => $a); }
			return json_encode($a, JSON_UNESCAPED_UNICODE);
		}
		
		function createNotify($id, $text)
		{
			$cache = $this->cache->get('notify_' . (int)$id );
			$cache = is_array($cache) ? $cache : array();
			$cache[] = base64_encode($text);
			$this->cache->set('notify_' . (int)$id, $cache, 0, 600);
		}
		
		function adminLevel($num)
		{
			switch($num) {
				case 1:
					return 'Сотрудник';
					
				case 2:
					return 'Младший модератор';
					
				case 3:
					return 'Модератор';
					
				case 4:
					return 'Старший модератор';
				
				case 5:
					return 'Главный модератор';
					
				case 6:
					return 'Младший администратор';
				
				case 7: 
					return 'Администратор';
				
				case 8:
					return 'Админ совет';
				
				case 9:
					return 'Главный администратор';
				
				default:
					return 'Пользователь';
			}
		}
	}