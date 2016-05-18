<?php
	
	namespace Controller;
	use \Templater\Native as TPL;
	
	class Cabinet
	{	
		function __call($m, $a)
		{
			$m = lcfirst(str_replace('action', '', $m));
			
			$page = (new TPL('page_' . $m));
			
			if(isset($_GET['embed']) and $_GET['embed'] == 1)
				return $page->render();
			
			if(file_exists($page->path))
				return (new TPL('main'))->set('content', $page->render())->set('onlyheaders', (isset($_GET['embed']) and @$_GET['embed'] == 2) )->render();
			else
				\Application\WebApp::i()->error(404);
		}
	}