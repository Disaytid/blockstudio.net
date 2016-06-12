<?php
	
	namespace Controller;
	use \Templater\Native as TPL;
	
	class Auth
	{
		function actionLogin()
		{
			return (new TPL('login'))->render();
		}
		
		function actionRegister()
		{
			return (new TPL('register'))->render();
		}
	}