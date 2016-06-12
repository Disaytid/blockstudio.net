<?php
	namespace Controller;
	use BlockStudio as BS;
	
	class Fructum
	{
		/* INSERT INTO `blockstudio`.`fructum` (`id`, `title`, `describe`, `github`, `cloudshop`, `author`, `mark`, `date`) VALUES ('1', 'Framework', 'САМ ФРЕЙМВОРК ЁУ', 'http://github.com/ProjectRaptor/Fructum', 'http://клаудшоп.рф/1', '2', '5', '1000');  */
		function actionModules()
		{
			$cache = BS::i()->cache->get('fructum_modules');
			if(is_array($cache) and count($cache) >= 1 and !isset($_GET['dropcache'])) {
				return BS::i()->createAnswer($cache);
			}
			else {
				$list = BS::i()->db()->getAll("SELECT * FROM `fructum` WHERE `mark` > 0 ORDER BY `mark` DESC");
				if(is_array($list) and count($list) >= 1) {
					BS::i()->cache->set('fructum_modules', $list, 0, 68400);
					return BS::i()->createAnswer($list);
				}
				else {
					return BS::i()->createAnswer(false);
				}
			}
		}
		
		function actionVersions()
		{
			$cache = BS::i()->cache->get('fructum_vers');
			if(is_string($cache) and !empty($cache) >= 1 and !isset($_GET['dropcache'])) {
				return BS::i()->createAnswer($cache);
			}
			else {
				$url = 'https://api.github.com/repos/ProjectRaptor/Fructum/releases';
				$options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
				$context  = stream_context_create($options);
				$data = json_encode(json_decode(file_get_contents($url, false, $context), true));
				BS::i()->cache->set('fructum_vers', $data, 0, 3600);
				return $data;
			}
		}
	}