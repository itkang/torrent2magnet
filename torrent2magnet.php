<?php
class aes {
 
    // CRYPTO_CIPHER_BLOCK_SIZE 32
     
    private $_secret_key = 'default_secret_key';
     
    public function setKey($key) {
        $this->_secret_key = $key;
    }
     
    public function encode($data) {
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256,'',MCRYPT_MODE_CBC,'');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
        mcrypt_generic_init($td,$this->_secret_key,$iv);
        $encrypted = mcrypt_generic($td,$data);
        mcrypt_generic_deinit($td);
         
        return $iv . $encrypted;
    }
     
    public function decode($data) {
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256,'',MCRYPT_MODE_CBC,'');
        $iv = mb_substr($data,0,32,'latin1');
        mcrypt_generic_init($td,$this->_secret_key,$iv);
        $data = mb_substr($data,32,mb_strlen($data,'latin1'),'latin1');
        $data = mdecrypt_generic($td,$data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
         
        return trim($data);
    }
}

$verifyToken = md5('unique_salt' . $_POST['timestamp']);
if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	
	// Validate the file type
	$fileTypes = array('torrent');
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array($fileParts['extension'],$fileTypes)) {
		require('lightbenc.php');
		$info = Lightbenc::bdecode_getinfo($tempFile);
		if (isset($info['info_hash'])) {
			success($info['info_hash']);
		}
		else {
			failed();	
		}
	} 
	else {
		failed();
	}
}
	
function success($info_hash)
{
	$result = array('result'=>1,'url'=>'magnet:?xt=urn:btih:'.strtoupper($info_hash));
	$json = json_encode($result);
	if ($json)
	{
		echo $json;
		$aes = new aes();
		$aes->setKey('test');

		// 加密
		$string = $aes->encode($json);
		// 解密
		$aes->decode($string);
		echo $string;
	}  
}

function failed()
{
	$result = array('result'=>0,'url'=>null);
	$json = json_encode($result);
	if ($json)
	{
		echo $json;
	}
}
?>
