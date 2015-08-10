<?php
	
	/* Finally, A light, permissions-checking logging class. 
	 * 
	 * Author	: Kenneth Katzgrau < katzgrau@gmail.com >
	 * Date	: July 26, 2008
	 * Comments	: Originally written for use with wpSearch
	 * Website	: http://codefury.net
	 * Version	: 1.0
	 *
	 * Usage: 
	 *		$log = new KLogger ( "log.txt" , KLogger::INFO );
	 *		$log->LogInfo("Returned a million search results");	//Prints to the log file
	 *		$log->LogFATAL("Oh dear.");				//Prints to the log file
	 *		$log->LogDebug("x = 5");					//Prints nothing due to priority setting
	*/
	
	class KLogger
	{
		
		const DEBUG 	= 1;	// Most Verbose
		const INFO 		= 2;	// ...
		const WARN 		= 3;	// ...
		const ERROR 	= 4;	// ...
		const FATAL 	= 5;	// Least Verbose
		const OFF 		= 6;	// Nothing at all.
		
		const LOG_OPEN 		= 1;
		const OPEN_FAILED 	= 2;
		const LOG_CLOSED 	= 3;
		
		/* Public members: Not so much of an example of encapsulation, but that's okay. */
		public $Log_Status 	= KLogger::LOG_CLOSED;
		//public $DateFormat	= "Y-m-d h:i:s:u A";
		public $MessageQueue;
	
		private $log_file;
		private $priority = KLogger::INFO;
		
		private $file_handle;
		
		public function __construct( $filepath , $priority )
		{
			if ( $priority == KLogger::OFF ) return;
			
			$this->log_file = $filepath;
			$this->MessageQueue = array();
			$this->priority = $priority;
			
			if ( file_exists( $this->log_file ) )
			{
				if ( !is_writable($this->log_file) )
				{
					$this->Log_Status = KLogger::OPEN_FAILED;
					$this->MessageQueue[] = "The file exists, but could not be opened for writing. Check that appropriate permissions have been set.";
					return;
				}
			}
			
			if ( $this->file_handle = fopen( $this->log_file , "a" ) )
			{
				$this->Log_Status = KLogger::LOG_OPEN;
				$this->MessageQueue[] = "The log file was opened successfully.";
			}
			else
			{
				$this->Log_Status = KLogger::OPEN_FAILED;
				$this->MessageQueue[] = "The file could not be opened. Check permissions.";
			}
			
			return;
		}
		
		public function __destruct()
		{
			if ( $this->file_handle )
				fclose( $this->file_handle );
		}
		
		public function LogInfo($line)
		{
			$this->Log( $line , KLogger::INFO );
		}
		
		public function LogDebug($line)
		{
			$this->Log( $line , KLogger::DEBUG );
		}
		
		public function LogWarn($line)
		{
			$this->Log( $line , KLogger::WARN );	
		}
		
		public function LogError($line)
		{
			$this->Log( $line , KLogger::ERROR );		
		}

		public function LogFatal($line)
		{
			$this->Log( $line , KLogger::FATAL );
		}
		
		public function Log($line, $priority)
		{
			if ( $this->priority <= $priority )
			{
				$browser = $this->ua();
				$status = $this->getTimeLine( $priority );
				$this->WriteFreeFormLine ( "$status $line --- $browser \n" );
			}
		}
		
		public function WriteFreeFormLine( $line )
		{
			if ( $this->Log_Status == KLogger::LOG_OPEN && $this->priority != KLogger::OFF )
			{
			    if (fwrite( $this->file_handle , $line ) === false) {
			        $this->MessageQueue[] = "The file could not be written to. Check that appropriate permissions have been set.";
			    }
			}
		}
		
		private function getTimeLine( $level )
		{
		
			$t = microtime(true);
			$micro = sprintf("%06d",($t - floor($t)) * 1000000);
			$d = new DateTime( date('Y-m-d H:i:s.'.$micro,$t) );

			$time = $d->format("Y-m-d h:i:s.u A");
			//$time = date( $this->DateFormat );
		
			$ip = $this->ip();
		
			switch( $level )
			{
				case KLogger::INFO:
					return "$time - INFO - $ip -->";
				case KLogger::WARN:
					return "$time - WARN - $ip -->";				
				case KLogger::DEBUG:
					return "$time - DEBUG - $ip -->";				
				case KLogger::ERROR:
					return "$time - ERROR - $ip -->";
				case KLogger::FATAL:
					return "$time - FATAL - $ip -->";
				default:
					return "$time - LOG - $ip -->";
			}
		}
		
		/**
		* Detect IP Address
		*
		*/

		private function ip() {
		if (isset($_SERVER)) {
		if(isset($_SERVER['HTTP_CLIENT_IP'])){
		$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])){
		$ip = $_SERVER['HTTP_FORWARDED_FOR'];
		}
		elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else{
		$ip = $_SERVER['REMOTE_ADDR'];
		}
		}
		else
		{
		if (getenv( ‘HTTP_CLIENT_IP’)) {
		$ip = getenv( ‘HTTP_CLIENT_IP’ );
		}
		elseif (getenv(‘HTTP_FORWARDED_FOR’)) {
		$ip = getenv(‘HTTP_FORWARDED_FOR’);
		}
		elseif (getenv(‘HTTP_X_FORWARDED_FOR’)) {
		$ip = getenv(‘HTTP_X_FORWARDED_FOR’);
		}
		else {
		$ip = getenv(‘REMOTE_ADDR’);
		}
		}
		return $ip;
		}
		
		private function ua()
		{
			if(isset($_SERVER['HTTP_USER_AGENT'])) {
				if(stristr($_SERVER['HTTP_USER_AGENT'],"Opera Mini"))
				{
					if(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']))
					{
						$browser = addslashes(strip_tags($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']));
					}
					else
					{
						$browser = addslashes(strip_tags($_SERVER['HTTP_USER_AGENT']));
					}
				}
				else
				{
					$browser = addslashes(strip_tags($_SERVER['HTTP_USER_AGENT']));
				}
			} else {
				$browser = "not set";
			}
			return $browser;
		}
		
		
	}


?>