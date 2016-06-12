<?php
	namespace Controller;
	use BlockStudio as BS;

	class User
	{
		#http://maps.google.com/maps/api/geocode/json?address=49.838513299999995,%2023.973829&sensor=false
		function actionExists()
		{
			if(!isset($_GET['id'])) { return BS::i()->createAnswer(false); }
			
			$cache = BS::i()->cache->get('user_' . (int)$_GET['id'] );
			if(is_array($cache) and isset($cache['id'])) { return BS::i()->createAnswer(true); }
			
			$db = BS::i()->db->table('user')->where("id = ". (int)$_GET['id'])->as_array();
			if(is_array($db) and isset($db['id'])) { 
				BS::i()->cache->set('user_' . $db['id'], $db, 0, 68400);
				return BS::i()->createAnswer(true); 
			}
			
			return BS::i()->createAnswer(false);
		}
		
		function actionId()
		{
			if(!isset($_GET['login'])) { return BS::i()->createAnswer(false); }
			
			if(strpos($_GET['login'], ' ')) { 
				return BS::i()->createAnswer(false);
			}
		
			$db = BS::i()->db->table('user')->where("`login` = '" . $_GET['login'] . "'")->as_array();
			if(is_array($db) and isset($db['id'])) { 
				BS::i()->cache->set('user_' . $db['id'], $db, 0, 68400);
				return BS::i()->createAnswer( $db['id'] ); 
			}
			
			return BS::i()->createAnswer(false);
		}
		
		function actionSet()
		{
			if(!BS::i()->checktoken(@$_GET['id'], @$_GET['token'])) { return BS::i()->createAnswer(false); }
			
			$cache = BS::i()->cache->get('user_' . (int)$_GET['id'] );
			if(is_array($cache) and isset($cache['id']) and !isset($_GET['dropcache'])) { $data = $cache; }
			else
			{
				$db = BS::i()->db->table('user')->where("id = ". (int)$_GET['id'])->as_array();
				if(is_array($db) and isset($db['id'])) { 
					BS::i()->cache->set('user_' . $db['id'], $db, 0, 68400);
					$data = $db;
				}
				else
				{
					return BS::i()->createAnswer(false);
				}
			}
			
			$answer = array('answer' => true);
			$change = array();
			$ignore = array('id', 'token');
			
			foreach($_GET as $k => $v) {
				if(in_array($k, $ignore)) { continue; }
				
				switch($k) {
					case 'password':
						if(strlen($v) >= 6 and strlen($v) < 40) {
							$change['password'] = md5($v);
							$answer[$k] = $v;
						}
						break;
					case 'email':
						if(filter_var($v, FILTER_VALIDATE_EMAIL)) {
							$change['email'] = $v;
							$answer[$k] = $v;
						}
						break;
					case 'wmr':
						if(strlen($v) == 13 and strstr($v, 'R')) {
							$change['wmr'] = $v;
							$answer[$k] = $v;
						}
						break;
					case 'checkwm':
						if(strlen($data['wmr']) == 13 and strlen($data['wmid']) == 12 and is_numeric($data['wmid'])) {
							$change['checkwm'] = '1';
							$answer[$k] = $v;
						}
						break;
					case 'pin':
						if(strlen($v) == 8 and is_numeric($v)) {
							$change['pin'] = $v;
							$answer[$k] = $v;
						}
						break;
					case 'wmid':
						if(strlen($v) == 12 and is_numeric($v)) {
							$change['wmid'] = $v;
							$answer[$k] = $v;
						}
						break;
					case 'avatar':
						if(is_array(@getimagesize($v))) {
							$change['avatar'] = $v;
							$answer[$k] = $v;
						}
						break;
					case 'socials':
						$change['socials'] = serialize(json_decode($v));
						$answer[$k] = $v;
						break;
					case 'info':
						$change['info'] = serialize(json_decode($v));
						$answer[$k] = $v;
						break;
					default:
						if(BS::i()->admintoken($_GET['id'], $_GET['token']) == true) {
							$change[$k] = $v;
							$answer[$k] = $v;
						}
						break;
				}
			}
			
			$data = array_merge($data, $change);
			
			if(count($change) <= 0) {
				return BS::i()->createAnswer( array('answer' => false) );
			}
			
			$query = BS::i()->db()->query("UPDATE `user` SET ?u WHERE `id` = ?i", $change, $_GET['id']);
			
			if(is_array($data)) {
				BS::i()->cache->set('user_' . (int)$_GET['id'], $data, 0, 68400);
			}
			else {
				BS::i()->cache->set('user_' . (int)$_GET['id'], array(), 0, 1);
			}
			
			return BS::i()->createAnswer($answer);
		}
		
		function actionContacts()
		{
			if(!BS::i()->checktoken(@$_GET['id'], @$_GET['token'])) { return BS::i()->createAnswer(false); }
			
			$cache = BS::i()->cache->get('contacts_' . (int)$_GET['id'] );
			if(is_array($cache) and !isset($_GET['dropcache'])) { $data = $cache; }
			else
			{
				$db = BS::i()->db->table('contacts')->where("id = ". (int)$_GET['id'])->as_array();
				if(is_array($db) and isset($db['id'])) { 
					BS::i()->cache->set('contacts_' . $_GET['id'], @json_decode($db['items']), 0, 68400);
					$data = @json_decode($db['items']);
				}
				else
				{
					BS::i()->cache->set('contacts_' . $_GET['id'], array(), 0, 68400);
					BS::i()->db()->query('INSERT INTO `contacts` (`id`, `items`) VALUES (?i, ?s)', $_GET['id'], '[]');
					$data = array();
				}
			}
			
			if(isset($_GET['add']) and is_numeric($_GET['add'])) {
				$data[] = $_GET['add'];
				BS::i()->cache->set('contacts_' . $_GET['id'], array_values($data), 0, 68400);
				BS::i()->db()->query('UPDATE `contacts` SET `items` = ?s WHERE `id` = ?i', json_encode(array_values($data)), $_GET['id']);
				return BS::i()->createAnswer( array('answer' => true, 'added' => $_GET['add']) );
			}
			
			if(isset($_GET['remove']) and is_numeric($_GET['remove'])) {
				unset( $data[ array_search($_GET['remove'], $data) ] );
				BS::i()->cache->set('contacts_' . $_GET['id'], array_values($data), 0, 68400);
				BS::i()->db()->query('UPDATE `contacts` SET `items` = ?s WHERE `id` = ?i', json_encode(array_values($data)), $_GET['id']);
				return BS::i()->createAnswer( array('answer' => true, 'removed' => $_GET['remove']) );
			}
			
			return BS::i()->createAnswer( array('answer' => true, 'id' => $_GET['id'], 'items' => $data) );
		}
		
		function actionData()
		{
			if(!isset($_GET['id'])) { return BS::i()->createAnswer(false); }
			
			$cache = BS::i()->cache->get('user_' . (int)$_GET['id'] );
			if(is_array($cache) and isset($cache['id']) and !isset($_GET['dropcache'])) { $data = $cache; }
			else
			{
				$db = BS::i()->db->table('user')->where("id = ". (int)$_GET['id'])->as_array();
				if(is_array($db) and isset($db['id'])) { 
					BS::i()->cache->set('user_' . $db['id'], $db, 0, 68400);
					$data = $db;
				}
				else
				{
					return BS::i()->createAnswer(false);
				}
			}
			
			if(isset($_GET['token']) and BS::i()->checktoken($data['id'], $_GET['token']) == true)
			{
				return BS::i()->createAnswer( array(
					'id' => $data['id'],
					'login' => $data['login'],
					'avatar' => $data['avatar'],
					'wmid' => $data['wmid'],
					'ban' => $data['ban'],
					'wmr' => $data['wmr'],
					'privilege' => $data['privilege'],
					'status' => BS::i()->adminLevel($data['privilege']),
					'blocks' => $data['blocks'],
					'outblocks' => $data['outblocks'],
					'email' => $data['email'],
					#'info' => @unserialize($data['info']),
					#'socials' => @unserialize($data['socials']),
					'checkwm' => $data['checkwm'],
					'ispin' => isset($data['pin']) ? '1' : '0'
				) );
			}
			else
			{
				return BS::i()->createAnswer( array(
					'id' => $data['id'],
					'login' => $data['login'],
					'status' => BS::i()->adminLevel($data['privilege']),
					'ban' => $data['ban'],
					#'info' => @unserialize($data['info']),
					#'socials' => @unserialize($data['socials']),
					'checkwm' => $data['checkwm'],
					'avatar' => $data['avatar']
				) );
			}
		}
		
		function actionRegister()
		{
			if(!isset($_GET['email']) or !isset($_GET['login']) or !isset($_GET['password'])) { return BS::i()->createAnswer(false); }
			if(strlen($_GET['login']) > 25 or strlen($_GET['login']) < 5) { return BS::i()->createAnswer(false); }
			if(strlen($_GET['password']) > 40 or strlen($_GET['password']) < 6) { return BS::i()->createAnswer(false); }
			
			if(!filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) { return BS::i()->createAnswer(false); }
			
			BS::i()->db()->query("INSERT INTO `user`(`email`, `login`, `password`, `last_ip`, `reg_ip`) VALUES (?s, ?s, ?s, ?s, ?s)", $_GET['email'], $_GET['login'], md5($_GET['password']), @$_SERVER['REMOTE_ADDR'], @$_SERVER['REMOTE_ADDR']);
		
			return BS::i()->createAnswer( BS::i()->db()->insertId() );
		}
		
		function actionLogin()
		{
			if(!isset($_GET['login']) or !isset($_GET['password'])) { return BS::i()->createAnswer(false); }
			
			$db = BS::i()->db->table('user')->rows( array('login' => $_GET['login'], 'password' => md5($_GET['password'])) );
			
			if(isset($db[0]['id']))
			{
				$db = $db[0];
				BS::i()->db()->query('UPDATE `user` SET `last_ip` = ?s WHERE `id` = ?i', @$_SERVER['REMOTE_ADDR'], $db['id']);
				$db['last_ip'] = @$_SERVER['REMOTE_ADDR'];
				BS::i()->cache->set('user_' . $db['id'], $db, 0, 68400);
				return BS::i()->createAnswer( array(
					'id' => $db['id'],
					'token' => md5($db['login'] . $db['password'] . 0),
					'expires_in' => 0
				));
			}
			
			return BS::i()->createAnswer(false);
		}
		
		function actionMessages()
		{
			if(!BS::i()->checktoken(@$_GET['id'], @$_GET['token'])) { return BS::i()->createAnswer(false); }
			
			$cache = BS::i()->cache->get('messages_' . (int)$_GET['id'] );
			
			if(isset($_GET['polling']))
			{
				while(true) 
				{
					$cache = BS::i()->cache->get('newmessages_' . (int)$_GET['id'] );
					if(is_array($cache)) {
						BS::i()->cache->set('newmessages_' . (int)$_GET['id'], null, 0, 10);
						return BS::i()->createAnswer( array('answer' => $cache['id'], 'message' => $cache['message']) );
					}
					continue;
				}
			}
			
			if(isset($_GET['new']))
			{
				if(isset($_GET['to']) and is_numeric($_GET['to']) and $_GET['to'] >= 1 and strlen($_GET['message']) < 3000)
				{
					$cache = is_array($cache) ? $cache : array();
					
					$message = trim( strip_tags( htmlspecialchars( $_GET['message'] ) ) );
					
					BS::i()->db()->query("INSERT INTO `messages` (`id`, `from`, `to`, `message`, `time`) VALUES(NULL, ?i, ?i, ?s, ?i)", $_GET['id'], $_GET['to'], base64_encode($message), time());
					if(!is_numeric(BS::i()->db()->insertId()))
						return BS::i()->createAnswer(false); 
					
					$cache[] = array(
						'id' => BS::i()->db()->insertId(),
						'from' => $_GET['id'],
						'to' => $_GET['to'],
						'message' => $message,
						'time' => time()
					);
					
					BS::i()->cache->set('messages_' . (int)$_GET['id'], $cache, 0, 43200);
					
					$hcache = BS::i()->cache->get('messages_' . (int)$_GET['to']);
					$hcache = is_array($hcache) ? $hcache : array();
					$hcache[] = array(
						'id' => BS::i()->db()->insertId(),
						'from' => $_GET['id'],
						'to' => $_GET['to'],
						'message' => $message,
						'time' => time()
					);
					BS::i()->cache->set('messages_' . (int)$_GET['to'], $hcache, 0, 43200);
					
					BS::i()->createNotify($_GET['to'], 'Новое личное сообщение');
					BS::i()->cache->set('newmessages_' . (int)$_GET['to'], array('id' => $_GET['id'], 'message' => $message), 0, 60);
					
					return BS::i()->createAnswer(BS::i()->db()->insertId());
				}
				else {
					return BS::i()->createAnswer(false);
				}
			}
			
			if(is_array($cache) and !isset($_GET['dropcache']))
			{
				return BS::i()->createAnswer($cache);
			}
			
			$ans = array();
			$query = BS::i()->db()->query("SELECT * FROM `messages` WHERE `from` = ?i OR `to` = ?i OR `to` = ?i ORDER BY `time` ASC LIMIT 0,350", @$_GET['id'], @$_GET['id'], -1);
			
			while( $a = BS::i()->db()->fetch($query) )
			{
				$ans[] = array(
					'id' => $a['id'],
					'from' => $a['from'],
					'to' => $a['to'],
					'message' => base64_decode($a['message']),
					'time' => $a['time']
				);
			}
			
			BS::i()->cache->set('messages_' . (int)$_GET['id'], $ans, 0, 43200);
			
			return BS::i()->createAnswer($ans);
		}
		
		function actionNotify()
		{
			set_time_limit(0);
			
			if(!BS::i()->checktoken(@$_GET['id'], @$_GET['token'])) { return BS::i()->createAnswer(false); }
			
			if(isset($_GET['add']))
			{
				BS::i()->createNotify($_GET['id'], $_GET['add']);
				return BS::i()->createAnswer( true );
			}
			
			while(true)
			{
				$cache = BS::i()->cache->get('notify_' . (int)$_GET['id'] );
				if(is_array($cache) and count($cache) >= 1)
				{
					BS::i()->cache->set('notify_' . (int)$_GET['id'], array(), 0, 600);
					$res = array();
					foreach($cache as $k => $v) {
						$res[$k] = base64_decode($v);
					}
					return BS::i()->createAnswer( $res );
				}
				sleep(0.5);
				continue;
			}
			
			return BS::i()->createAnswer(true);
		}
		
		function actionToken()
		{
			return BS::i()->createAnswer( BS::i()->checktoken(@$_GET['id'], @$_GET['token']) );
		}
	}