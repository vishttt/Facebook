<?php
//define('USERNAME', 'Nhập username vào đây');
//define('PASSWORD', 'Nhập password vào đây');
function get_token($username, $password, $type = 'android')
{
	$linklist = 'https://api.facebook.com/restserver.php';
	if ($type == 'android')	{
		$data = array(
			'api_key' => '882a8490361da98702bf97a021ddc14d',
			'email' => $username,
			'format' => 'JSON',
			//'generate_machine_id' => '1',
			//'generate_session_cookies' => '1',
			'locale' => 'vi_vn',
			'method' => 'auth.login',
			'password' => $password,
			'return_ssl_resources' => '0',
			'v' => '1.0'
		);
		$sig = '62f8ce9f74b12f84c123cc23437a4a32';
	}
	elseif ($type == 'ios') {
		$data = array(
			'api_key' => '3e7c78e35a76a9299309885393b02d97',
			'email' => $username,
			'format' => 'JSON',
			//'generate_machine_id' => '1',
			//'generate_session_cookies' => '1',
			'locale' => 'vi_vn',
			'method' => 'auth.login',
			'password' => $password,
			'return_ssl_resources' => '0',
			'v' => '1.0'
		);
		$sig = 'c1e620fa708a1d5696fb991c1bde5662';
	}
	$sig = '';
	foreach($data as $key => $value){
		$sig .= "$key=$value";
	}
	if ($type == 'android')	{
		$sig .= '62f8ce9f74b12f84c123cc23437a4a32';
	}
	elseif ($type == 'ios') {
		$sig .= 'c1e620fa708a1d5696fb991c1bde5662';
	}
	$data['sig'] = md5($sig);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $linklist);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	if ($type == 'android') {
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; Android 4.4.2; SMART 3.5'' Touch+ Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36");	
	}
	elseif ($type == 'iphone') {
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1");
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

	$page = curl_exec($ch);
	curl_close($ch);

	$infotoken = json_decode($page);
	$token = $infotoken->access_token;

	return $token;
}
echo 'TOKEN ANDROID: '.get_token($_REQUEST['usr'], $_REQUEST['pwd']).'<br>'.'<br>';
echo 'TOKEN IOS: '.get_token($_REQUEST['usr'], $_REQUEST['pwd'], 'ios');
//<p>Access Token Android: .get_token($_REQUEST['usr'],$_REQUEST['pwd'])</p>