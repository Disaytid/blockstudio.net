<?php
	namespace Controller;
	use BlockStudio as BS;

	class Blog
	{
		
		function actionGet() 
		{
			if(!isset($_GET['id'])) { return BS::i()->createAnswer(false); }
			$cache = BS::i()->cache->get('blog_' . (int)$_GET['id']);
			if(is_array($cache) and !isset($_GET['dropcache'])) {
				return BS::i()->createAnswer($cache);
			}
			
			$result = BS::i()->db()->getAll('SELECT * FROM `blog` WHERE `author` = ?i', $_GET['id']);
			$data = array();
			
			foreach($result as $k => $v)
			{
				$data[$k] = $v;
				$data[$k]['post'] = base64_decode($v['post']);
			}
			
			if(is_array($data)) { $cache = BS::i()->cache->set('blog_' . (int)$_GET['id'], $data, 0, 68400); }
			return BS::i()->createAnswer($data); 
		}
		
		function actionAdd()
		{
			if(!BS::i()->checktoken(@$_GET['id'], @$_GET['token'])) { 
				return BS::i()->createAnswer( array('answer' => false, 'error' => 'Bad user or token') );
			}

			$data = array(
				'title' => isset($_REQUEST['title']) ? strip_tags($_REQUEST['title']) : 'без имени',
				'post' => isset($_REQUEST['post']) ? base64_encode(nl2br($_REQUEST['post'])) : 'PGJyPg==',
				'time' => time(),
				'author' => $_GET['id'],
				'tags' => isset($_REQUEST['tags']) ? strip_tags($_REQUEST['tags']) : 'none'
			);
			
			BS::i()->db()->query('INSERT INTO `blog` (`id`, `title`, `post`, `time`, `author`, `tags`) VALUES (NULL, ?s, ?s, ?i, ?i, ?s)', $data['title'], $data['post'], $data['time'], $data['author'], $data['tags']);
			
			if(!is_numeric(BS::i()->db()->insertId()))
				return BS::i()->createAnswer(false); 
					
			$data['id'] = BS::i()->db()->insertId();
			
			BS::i()->cache->delete('blog_' . (int)$_GET['id']);
			
			return BS::i()->createAnswer($data['id']); 
		}
		
		function actionDelete()
		{
			if(!BS::i()->checktoken(@$_GET['id'], @$_GET['token']) or !isset($_GET['postid'])) { 
				return BS::i()->createAnswer( array('answer' => false, 'error' => 'Bad user, token or post') );
			}
			
			$cache = BS::i()->cache->get('blog_' . (int)$_GET['id'] );
			
			if(is_array($cache) and isset($cache['id']) and !isset($_GET['dropcache'])) { $data = $cache; }
			else
			{
				$db = BS::i()->db()->getAll("SELECT * FROM `blog` WHERE `id` = ?i", @$_GET['postid']);
				if(is_array($db) and isset($db[0]['id'])) { 
					$data = $db[0];
				}
				else
				{
					return BS::i()->createAnswer( array('answer' => false, 'error' => 'Post doesnt exists') );
				}
			}
			
			if($data['author'] != $_GET['id']) {
				return BS::i()->createAnswer( array('answer' => false, 'error' => 'Restricted access') );
			}
			
			BS::i()->cache->delete('blog_' . (int)$_GET['id'] );
			BS::i()->db()->query('DELETE FROM `blog` WHERE `id` = ?i', $_GET['postid']);
			
			return BS::i()->createAnswer( array('answer' => true, 'postid' => $_GET['postid'], 'data' => $data) );
		}
		
		function actionSet()
		{
			if(!BS::i()->checktoken(@$_GET['id'], @$_GET['token']) or !isset($_GET['postid'])) { 
				return BS::i()->createAnswer( array('answer' => false, 'error' => 'Bad user, token or post') );
			}
			
			$cache = BS::i()->cache->get('blog_' . (int)$_GET['id'] );
			if(is_array($cache) and isset($cache['id']) and !isset($_GET['dropcache'])) { $data = $cache; }
			else
			{
				$db = BS::i()->db()->getAll("SELECT * FROM `blog` WHERE `id` = ?i", @$_GET['postid']);
				if(!isset($db[0]))
				{
					return BS::i()->createAnswer( array('answer' => false, 'error' => 'Post doesnt exists') );
				}
				$data = $db[0];
			}
			
			if($data['author'] != $_GET['id']) {
				return BS::i()->createAnswer( array('answer' => false, 'error' => 'Access denied') );
			}
			
			$answer = array('answer' => true);
			$change = array();
			$can = array('title', 'post');
			
			foreach($_GET as $k => $v) {
				if(!in_array($k, $can)) { continue; }
				if($k == 'post') { $v = base64_encode(nl2br($v)); }
				$change[$k] = $v;
				$answer[$k] = $v;
			}
			
			$data = array_merge($data, $change);
			
			if(!is_array($data)) {
				return BS::i()->createAnswer( array('answer' => false, 'error' => 'Cannot merge arrays') );
			}
			
			$query = BS::i()->db()->query("UPDATE `blog` SET ?u WHERE `id` = ?i", $change, $_GET['postid']);
			
			BS::i()->cache->delete('blog_' . (int)$_GET['id']);
			
			return BS::i()->createAnswer($answer);
		}
		
	}