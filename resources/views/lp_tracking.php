<?php
/**
 * server to server (different server) tracking: for landing page server
 */
global $pixelk_domain, $lp_mvt;

error_reporting ( E_ALL & ~ E_NOTICE );
ini_set ( 'display_errors', 'Off' );

// check the extensions and config
if (! $pixelk_domain) {
	echo '<strong>$pixelk_domain</strong> is not defined, please define it and refresh this page again';
	die ();
}

if (! function_exists ( 'curl_init' )) {
	echo '<strong>PHP Curl extension</strong> is not enabled, please enable it and refresh this page again';
	die ();
}

if (! function_exists ( 'mcrypt_encrypt' )) {
	echo '<strong>PHP mCrypt extension</strong> is not enabled, please enable it and refresh this page again';
	die ();
}

// protocol scheme
if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] && strtolower ( $_SERVER ['HTTPS'] ) != 'off') {
	$proto = 'https://';
} else {
	$proto = 'http://';
}

// base url
$base_url = $proto . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];

// landing page anti-spy redirect link
$spied_link = $pixelk_domain . '/spy.php?url=' . urlencode ( $base_url );

// handle MVT(multivariate testing)
$mvt_data = '';
if (isset ( $lp_mvt ) && is_array ( $lp_mvt )) {
	$url_variate = $templp_mvt = array ();
	foreach ( $lp_mvt as $var => $var_list ) {
		$temp = $var_list [mt_rand ( 0, count ( $var_list ) - 1 )];
		$templp_mvt [$var] = $temp;
		
		// insert to database, cut the string in case of long chars
		(mb_strlen ( $temp, 'UTF-8' ) > 30) ? $temp = mb_substr ( $temp, 0, 30, 'UTF-8' ) . '...' : '';
		$url_variate [] = $var . ':' . $temp;
	}
	
	// re-assign
	$lp_mvt = $templp_mvt;
	
	// post to database
	$mvt_data = implode ( '|', $url_variate );
}

// curl post
$ch = curl_init ();
curl_setopt ( $ch, CURLOPT_URL, $pixelk_domain . '/strack.php' );
curl_setopt ( $ch, CURLOPT_POSTFIELDS, 'd=' . encryptS2SData ( "{$_GET ['k']}|{$_GET ['c']}" ) . "&mvt={$mvt_data}" );
curl_setopt ( $ch, CURLOPT_POST, true );
curl_setopt ( $ch, CURLOPT_HEADER, false );
curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
curl_setopt ( $ch, CURLOPT_TIMEOUT, 8 );
curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
		'User-Agent: ' . $_SERVER ['HTTP_USER_AGENT'],
		'Accept-Language: ' . $_SERVER ['HTTP_ACCEPT_LANGUAGE'],
		'Accept: */*',
		'Pktrk-Lpurl: ' . $base_url, // LP url
		'Pktrk-Ip-Addr: ' . $_SERVER ['REMOTE_ADDR'], // original visitor IP
		'Pktrk-Seckey: cm-p2x_qt5' 
) ); // sec key

$ret = curl_exec ( $ch );
$ret_info = curl_getinfo ( $ch );
curl_close ( $ch );

// network error
if ($ret_info ['http_code'] != 200) {
	echo "Can not connect to your tracking server(network error), details below:<br/>";
	echo '<pre>';
	print_r ( $ret_info );
	echo '</pre>';
	die ();
}

// json decode
try {
	$ret_arr = json_decode ( $ret, true );
} catch ( Exception $e ) {
	$ret_arr = false;
}

if (! is_array ( $ret_arr )) {
	echo "Invalid response:<br/><pre>{$ret}</pre>";
	die ();
}

$basic_info = explode ( '|', $ret_arr ['a'] );
$other_lps = $ret_arr ['b'] ? explode ( '|', $ret_arr ['b'] ) : array (); // other LP list
$camp_info = $ret_arr ['c'] ? explode ( '|', $ret_arr ['c'] ) : array (); // camp id and key
$cid = $camp_info [0]; // camp id
$ckey = $camp_info [1]; // camp key
$lpdata = $ret_arr ['d'] ? explode ( '|', $ret_arr ['d'] ) : array (); // data on LP

$camp_type = $basic_info [0];
$total_num = $basic_info [1] + 1;
if ($camp_type == 1) {
	// path
	
	// rotate offer
	$offer = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'];
	
	// specific offer link
	for($i = 1; $i <= $total_num; $i ++) {
		${'offer_' . $i} = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'] . '&offer=' . $i;
	}
} elseif ($camp_type == 2) {
	// option
	
	// rotate offer
	$offer = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'];
	
	for($i = 1; $i <= $total_num; $i ++) {
		${'option_' . $i} = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'] . '&option=' . $i;
	}
} elseif ($camp_type == 3) {
	// funnel
	
	// rotate offer
	$offer = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'];
	
	for($i = 1; $i <= $total_num; $i ++) {
		${'option_' . $i} = $pixelk_domain . '/click.php?c=' . $_GET ['c'] . '&k=' . $_GET ['k'] . '&option=' . $i;
	}
	
	$next_level = "{$pixelk_domain}/click_next.php";
	
	$total_level = $basic_info [2] >= 0 ? $basic_info [2] + 1 : 0;
	for($i = 1; $i <= $total_level; $i ++) {
		${'next_level_' . $i} = "{$pixelk_domain}/click_next.php?page={$i}";
	}
}

// data on landing page
if ($lpdata) {
	// token 1-15
	for($i = 1; $i <= 15; $i ++) {
		$arrk = $i - 1;
		${'t' . $i} = $lpdata [$arrk];
	}
	
	// extra tokens
	for($i = 1; $i <= 5; $i ++) {
		$arrk = $i + 14;
		${'et' . $i} = $lpdata ['ET' . $arrk];
	}
	
	$country_code = $lpdata [20];
	$country_name = $lpdata [21];
	$region = $lpdata [22];
	$city = $lpdata [23];
	$postal_code = $lpdata [24];
	$isp = $lpdata [25];
	$lang = $lpdata [26];
	$ref_domain = $lpdata [27];
	$os = $lpdata [28];
	$osv = $lpdata [29];
	$browser = $lpdata [30];
	$browserv = $lpdata [31];
	$brand = $lpdata [32];
	$model = $lpdata [33];
	$marketing_name = $lpdata [34];
	$tablet = $lpdata [35];
	$rheight = $lpdata [36];
	$rwidth = $lpdata [37];
	$timestamp = $lpdata [38];
}

// landing page load time(start)
$loaded_time = $_SERVER ['REQUEST_TIME'] * 1000;

// engage secs
$engage_sec = $ret_arr ['e'];

// detect engage or not
if ($engage_sec > 0) {
	$engage_sec *= 1000;
} else {
	$engage_sec = 0;
}

// landing page js
$lp_js = <<<STRING
<script type="text/javascript">
!function(e,n,o){var t={},i=function(e){try{var n=decodeURIComponent((new RegExp("[?|&]"+e+"=([^&#]+?)(&|#|$)").exec(o.location.search)||["",""])[1].replace(/\+/g,"%20"));return""==n&&"undefined"!=typeof arguments[1]?arguments[1]:n}catch(t){return"undefined"!=typeof arguments[1]?arguments[1]:""}},r="{$pixelk_domain}",a="{$_GET['c']}",c="{$_GET['k']}",p={$engage_sec},d=function(e){o.write(e)},f=function(e){var n=o.createElement("img"),i="img_"+(new Date).getTime();t[i]=n,n.onload=n.onerror=function(){t[i]=n=n.onload=n.onerror=null,delete t[i]},n.src=e+"&t="+Math.random()},m=function(e){if("object"!=typeof e)return!1;var n="";for(var o in e)"undefined"!=typeof e[o]&&(n+="&"+o+"="+encodeURIComponent(e[o]));f(r+"/lib/ajax/campdata.php?c="+a+"&k="+c+n)},u={saveCampToken:m,echo:d,getToken:i};e.PK=u;var g=(new Date).getTime();f(r+"/ctrack.php?c="+a+"&k="+c+"&sr="+(n.screen.width||0)+"_"+(n.screen.height||0)),n.onload=function(){var e=(new Date).getTime()-g,o=0;"undefined"!=typeof n.performance&&"undefined"!=typeof n.performance.timing&&(o=n.performance.timing.domainLookupEnd-n.performance.timing.domainLookupStart),f(r+"/lib/ajax/lp_timing.php?c="+a+"&k="+c+"&d="+encodeURIComponent(e+"_"+o)),p>0&&setTimeout(function(){f(r+"/lib/ajax/lp_engage.php?c="+a+"&k="+c)},p)}}(this,window,document);
</script>

STRING;

/**
 * encrypt data
 * MCRYPT_RIJNDAEL_128 and MCRYPT_MODE_CBC is fatest and high performance
 *
 * @param unknown $data        	
 */
function encryptS2SData($data) {
	if (! $data) {
		return '';
	}
	
	$key = 'y_Q,JG*yVe%4&k8=Zm:Wa+pRf#ydgJ;J'; // 32
	$iv = 'p2ze;6V:0r,U&F#K'; // 16
	
	try {
		$endata = mcrypt_encrypt ( MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv );
	} catch ( Exception $e ) {
		return '';
	}
	
	return base64_encode ( $endata );
}
?>