<?php
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
	}
	echo '第三种AES加密方案:<br>';      
	$key = '1234567890123456';      
	$key = pad2Length($key,16);      
	$iv = 'asdff';      
	$content = 'hello';      
	$content = pad2Length($content,16);      
	$AESed =  bin2hex( mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key,$content,MCRYPT_MODE_ECB,$iv) ); #加密      
	echo "128-bit encrypted result:".$AESed.'<br>';      
	$jiemi = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key,hexToStr($AESed),MCRYPT_MODE_ECB,$iv); #解密      
	echo '解密:';      
	echo trimEnd($jiemi);     
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
