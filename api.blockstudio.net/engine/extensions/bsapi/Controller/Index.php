<?php
	namespace Controller;

	class Index
	{
		function __construct()
		{
			\Application\WebApp::i()->header('Content-Type: application/javascript');
		}
		
		function actionIndex()
		{
			return "{'error': 'Query is empty'}";
		}
	}