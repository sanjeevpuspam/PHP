<?php 
class SFTPDownload {
	public $url;
	public $port;
	public $user;
	public $pass;
	
	public $localdir;
	public $remoteDir;

	
	function __construct() {
		$this->url 			= 'host';
		$this->port			= 22;
		$this->user			= 'username';
		$this->pass			= 'password';
		$this->localdir 	= "E:\server_backup\projectame";
		$this->remoteDir	= '/outgoing';
	}

	public function sftp(){
		try{
			$conn 	= ssh2_connect($this->url, $this->port);
			ssh2_auth_password($conn, $this->user, $this->pass);
			return ssh2_sftp($conn);
		} catch(Exception $e) {
			die($e->getMessage());
		}
	}

	
	public function getContents($dir, $sftp, &$results = array()){
		$files = scandir('ssh2.sftp://' . $sftp . $dir);
		if(is_array($files)){
			foreach($files as $key => $value) {	
				$path = $dir."/".$value;
				if(strpos($value, ".") !== false){
					if($value != "." && $value != ".."){
						$results[] = $path;
					}
				} else {
					$this->getContents($path,$sftp,$results);
				}		
			}
		}
		return $results;
	}
	
	function writeContents($dir,$sftp){
		$files = array_reverse($this->getContents($this->remoteDir,$sftp));
		foreach($files as $file){
			$fullPath 	= $dir.$file;
			$fileName 	= basename($fullPath);
			$dirPath 	= str_replace($fileName,'',$fullPath);
			if(!is_dir($dirPath)){
				mkdir($dirPath, 0777, true);	
			}
			if(pathinfo($file, PATHINFO_EXTENSION)){
				$stream 	= @fopen("ssh2.sftp://$sftp$file", 'r');
				$contents 	= stream_get_contents($stream);
				file_put_contents("$dirPath/$fileName",$contents);
				echo "<span>Write content successfully in==> <strong>$dirPath$fileName</strong> </span><br />";
			}
		}
		@fclose($stream);
	}
	
	public function download(){
		$this->writeContents($this->localdir,$this->sftp());
	}
}
$obj = new SFTPDownload();
$obj->download();