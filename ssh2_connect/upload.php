<?php
class SFTPUpload {
    public $url;
	public $port;
	public $user;
	public $pass;
	
	public $localdir;
    public $remoteDir;

    function __construct() {
		$this->url 			= 'host';  // 172.0.0.1
		$this->port			= 22;
		$this->user			= 'username';
		$this->pass			= 'password';
        $this->remoteDir	= '/test/projectame';
        $this->localdir 	= "E:\server_backup\projectame";
    }

    public function connect(){
        try {
            $conn 	= ssh2_connect($this->url, $this->port);
            ssh2_auth_password($conn, $this->user, $this->pass);
            return $conn;
        } catch(Exception $e) {
			die($e->getMessage());
		}
    }
    public function sftp(){
		try{
			return ssh2_sftp($this->connect());
		} catch(Exception $e) {
			die($e->getMessage());
		}
    }

    public function scanFiles($path, &$results = array()){
        if(file_exists($path) && is_dir($path)){
            $result = scandir($path);
            $files = array_diff($result, array('.', '..'));
            if(count($files) > 0){
                foreach($files as $file){
                    if(is_file("$path/$file")){
                        $results[] = $path."/".$file;
                    } else if(is_dir("$path/$file")){
                        $this->scanFiles("$path/$file",$results);
                    }
                }
            }
        } 
        return $results;
    }

   public function sendFiles($dir)
   {
       try {
            $files = $this->scanFiles($this->localdir);
            foreach($files as $localFile){
                $sftp           = $this->sftp();
                $fileName       = basename($localFile);
                $remoteFile     = $this->remoteDir.(str_replace($this->localdir,'',$localFile));
                $remoteDirPath  = str_replace($fileName,'',$remoteFile);
                ssh2_sftp_mkdir($sftp, $remoteDirPath, 0755, true);
                $stream    = @fopen("ssh2.sftp://$sftp$remoteFile", 'w');
                $contants  = file_get_contents($localFile);
                fwrite($stream, $contants);

            }
        } catch(Exception $e) {
            die($e->getMessage());
        }
   } 
   public function callMe(){
       $this->sendFiles($this->remoteDir);
   }
}
$conn = new SFTPUpload();
$files = $conn->callMe();

