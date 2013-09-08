<?php

//die(ini_get('default_socket_timeout'));


class Tor {


	public $run_status = FALSE;
	public $open_status = FALSE;

	private $mysock = FALSE;
	private $errno = 0;
	private $errstr = "";

	private $path_to_torrc = ""; // you don't need to edit this

	private $controlport_password = ""; // you DO need to edit this

        private $start = NULL;
        private $timeout = 1;


	function __construct() {
		$this->path_to_torrc = $this->get_path_to_torrc();
                ini_set('default_socket_timeout', $this->timeout);
		if($this->test()) {
			$this->run_status = TRUE;
			if(!$this->open_status) {
				return $this->open();
			}
		}
                return false;
	}


	function get_path_to_torrc() {
		if(file_exists("./.work/path_to_torrc")) {
			$lines = file("./.work/path_to_torrc");
			foreach($lines as $line) {
				$line = trim($line);
				if(strlen($line) > 0) return $line ;
			}
		}
	}


	function set_path_to_torrc($path) {
		$file = fopen("./.work/path_to_torrc","w");
		fputs($file,$path);
		fclose($file);
	}


	function test() {
		$does_tor_run = FALSE;
		exec("ps ax | grep tor 2>&1", $output);
		foreach($output as $row) {
			if(strstr($row,"./tor/App/tor")) {
				$does_tor_run = TRUE;
			}
		}
		return $does_tor_run;
	}


	function run() {
		if(!$this->test()) {
			exec("nohup ./tor/App/tor -f ".$this->path_to_torrc." > process.out 2> process.err < /dev/null &");
			if($this->test()) $this->run_status = TRUE;
		}
	}


	function open() {
		if($this->mysock == FALSE) {
			if($this->mysock = fsockopen("localhost", 9051, $this->errno, $this->errstr, 1)) {
  			  if($this->errno == 0) $this->open_status = TRUE;
                          return true;
			}
		}
                return false;
	}


	function close() {
		if($this->mysock != FALSE) {
                        $this->send("quit");
			fclose($this->mysock);
			$this->open_status = FALSE;
		}
	}


	function send($message) {
		if($this->mysock != FALSE) fwrite($this->mysock, $message . "\n");
	}


	function read($bufflen=128) {
		return fread($this->mysock, $bufflen);
	}

	function feof($fp) {
		 $this->start = microtime(true);
		 return feof($fp);
	}

	function authenticate() {

		if($this->open_status) {
			$this->send("authenticate \"".$this->controlport_password."\"");
                        $response = $this->read(1024);
		        list($code, $text) = explode(' ', $response, 2);
		        if ($code != '250') return false; //authentication failed
                        return true;
		}

	}


        function getconf($name) {
                $return = array();
                $this->send("getconf ".$name);

                $this->start = NULL;
                // woot deux fread()
                $response = $this->read(); // fread($this->mysock);
                list($respcode, $respcontent) = explode(" ", $response, 2);
                if($respcode!='250') {
                  return false;
                }
                list($name, $value) = explode("=", $respcontent, 2);
                return trim($value);

        }

	function getinfo($argues) {
                $return = array();
		if($this->open_status) {

			$this->send("getinfo ".$argues);
                        $lines = '';

			$this->start = NULL;
                        // woot deux fread()
			$lines.= $this->read(16384); // fread($this->mysock);
                        $lines.= $this->read(16384); // fread($this->mysock);
			$temparray = explode("\n",$lines);
			foreach($temparray as $token) {
                               	list($varname, $varvalue) = explode(" ", $token, 2);
                                if(strlen(trim($varname))<3) continue;
                                if(trim($varvalue)=='' || trim($varvalue)=='OK') continue;
                                $return[trim($varname)] = trim($varvalue);
			}
                        array_shift($return);
		}
                return $return;

	}



        function getcountry($ip) {
                $this->send("getinfo ip-to-country/".$ip);
                $ret = explode("\n", $this->read(128));
                if(count($ret)<2) return false ; // $ret[0];
                if(trim($ret[1])!='250 OK') return false ; // $ret[1];
                $cn  = trim(end(explode("=", $ret[0])));
                if(!preg_match("/^[a-z]{2}$/i", $cn)) return false; // "[bad CN]";
                return $cn;
                //echo " ** $ret ** ";
                //return end(explode("=", $this->read(128)));
        }







	function setinfo($argues) {

		if($this->open_status) {

			$this->send("setinfo ".$argues);

			$line = "";
			$return = FALSE;
			while(substr($line,0,6) != "250 OK") {
				$line = $this->read();
				if(substr($line,0,6) == "250 OK") $return = TRUE;
			}

			return $return ;

		}

	}


	function setconf($what,$data) {

		if($this->open_status) {

			$this->send("setconf ".$what."=\"".$data."\"");

			$line = "";
			$return = FALSE;
			while((substr($line,0,6) != "250 OK") && (substr($line,0,3) != "552")) {
				$line = $this->read();
				if(substr($line,0,6) == "250 OK") $return = TRUE;
				if(substr($line,0,3) == "552") $return = FALSE;
			}

			return $return ;

		}

	}


	function saveconf() {

		if($this->open_status) {

			$this->send("saveconf");

			$line = "";
			$return = FALSE;
			while((substr($line,0,6) != "250 OK") && (substr($line,0,3) != "552")) {
				$line = $this->read();
				if(substr($line,0,6) == "250 OK") $return = TRUE;
				if(substr($line,0,3) == "552") $return = FALSE;
			}

			return $return ;

		}

	}


}

