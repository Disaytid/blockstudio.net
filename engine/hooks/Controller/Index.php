<?php
	
	namespace Controller;
	
	class Index
	{
		function actionIndex()
		{
			//return (new \Templater\Native('mainpage'))->render();
			header('Location: /auth/login');
			return '';
		}
	}