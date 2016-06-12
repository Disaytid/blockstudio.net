<?php
	namespace Controller;
	use BlockStudio as BS;

	class News
	{
		function catName($id) {
			$arr = array(
				'0' => 'Uncategorized',
				'1' => 'Block Studio',
				'2' => 'Raptor Game Engine',
				'3' => 'Fructum Framework',
				'4' => 'Disaytid Games',
				'5' => 'CloudShop',
				'6' => 'Zaguglite',
				'7' => 'Block Studio API',
				'100500' => 'Global'
			);
			
			return isset($arr[$id]) ? $arr[$id] : ( $id == 'list' ? array_combine(array_values($arr), array_keys($arr)) : null );
		}
		
		function actionCats()
		{
			return BS::i()->createAnswer( $this->catName('list') );
		}
		
		function actionGet()
		{
			
			$cache = BS::i()->cache->get('bstudio_news');
			if(is_array($cache) and !isset($_GET['dropcache']) and isset($cache['id'])) { return BS::i()->createAnswer($cache); }
			
			$db = BS::i()->db()->getAll('SELECT * FROM `news` WHERE `active` = 1');
			if(is_array($db)) { 
				$res = array();
				
				foreach($db as $v) {
					$res[ $v['id'] ]['id'] = $v['id'];
					$res[ $v['id'] ]['title'] = $v['title'];
					$res[ $v['id'] ]['short'] = $v['short'];
					$res[ $v['id'] ]['full'] = $v['full'];
					$res[ $v['id'] ]['time'] = $v['date'];
					$res[ $v['id'] ]['format'] = date('d.m.Y H:m:s', $v['date']);
					$res[ $v['id'] ]['cat'] = $v['cat'];
					$res[ $v['id'] ]['cat_name'] = $this->catName($v['cat']);
				}
				BS::i()->cache->set('bstudio_news', array_values($res), 0, 68400);
				return BS::i()->createAnswer(array_values($res)); 
			}
			
			return BS::i()->createAnswer($db);
		}
	}