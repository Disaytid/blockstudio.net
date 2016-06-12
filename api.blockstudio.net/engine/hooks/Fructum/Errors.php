<?php
	/**
	 * Fructum error handlers
	 * 
	 * @version 1.0
	 * @author Mike Chip
	 */
	
	namespace Fructum;
	
	class Errors
	{
		
		/**
		 * Handler for all errors
		 *
		 * @param int $errno
		 * @param string $errstr
		 * @param string $errfile
		 * @param int $errline
		 * @param mixed $errcontext
		 */
		public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
		{
			$text = "Error #{$errno}: {$errstr} [File {$errfile} in line {$errline}]";
			throw new Exception($text);
		}
		
		/**
		 * Handler for all uncaught exceptions
		 *
		 * $param object $e
		*/
		public static function exception_handler($e)
		{
			header('Content-Type: application/javascript'); 
			header("Access-Control-Allow-Origin: *");
			header("Access-Control-Allow-Methods: GET, POST");
			header("Access-Control-Allow-Headers: X-Requested-With");
			$text = (isset($e->ex_code) ? 'Error '.$e->ex_code : 'Internal Server Error');
			die( json_encode( array('error' => $text, 'answer' => false) ) );
		}
		
	}