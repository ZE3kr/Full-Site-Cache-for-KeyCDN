<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$fsckeycdn_realhost = $_SERVER['HTTP_HOST'];
$fsckeycdn_rootdomain = implode('.',array_slice(explode('.',$_SERVER['HTTP_HOST']),-2));
define('COOKIE_DOMAIN', $fsckeycdn_realhost);
define('FSCKEYCDN_SETUP', true);
if($fsckeycdn_realhost != $fsckeycdn_rootdomain){
	if (substr($_SERVER['HTTP_HOST'],0,9)=='wp-admin-'){
		$fsckeycdn_admin = $_SERVER['HTTP_HOST'];
		$_SERVER['HTTP_HOST'] = substr($_SERVER['HTTP_HOST'],9);
	} elseif (substr($_SERVER['HTTP_HOST'],0,9)=='wp-admin.'){
		$fsckeycdn_admin = $_SERVER['HTTP_HOST'];
		$_SERVER['HTTP_HOST'] = 'www.'.substr($_SERVER['HTTP_HOST'],9);
	} elseif (substr($_SERVER['HTTP_HOST'],0,4)=='www.'){
		$fsckeycdn_admin = 'wp-admin.'.substr($_SERVER['HTTP_HOST'],4);
	} else {
		$fsckeycdn_admin = 'wp-admin-'.$_SERVER['HTTP_HOST'];
	}
}

$_SERVER = str_replace('wp-admin.','www.',$_SERVER);

function fsckeycdn_ip_in_range($ip) {
	global $fsckeycdn_ip_white_list, $fsckeycdn_ipv6_white_list;
	$type = strpos($ip, ":") === false ? 4 : 6;
	if ($type == 6){
		$ip_pieces = explode("::", $ip, 2);
		if( $ip_pieces[0] ) {
			$ip = $ip_pieces[0];
		}
		$ip = fsckeycdn_get_ipv6_full($ip);
		foreach($fsckeycdn_ipv6_white_list as $range) {
			if( fsckeycdn_ipv6_in_range($ip, $range) ){
				return true;
			}
		}
	} else {
		foreach($fsckeycdn_ip_white_list as $range) {
			if( fsckeycdn_ipv4_in_range($ip, $range) ){
				return true;
			}
		}
	}
	return false;
}
function fsckeycdn_ipv4_in_range($ip, $range) {
	if (strpos($range, '/') !== false) {
		// $range is in IP/NETMASK format
		list($range, $netmask) = explode('/', $range, 2);
		if (strpos($netmask, '.') !== false) {
			// $netmask is a 255.255.0.0 format
			$netmask = str_replace('*', '0', $netmask);
			$netmask_dec = ip2long($netmask);
			return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
		} else {
			// $netmask is a CIDR size block
			// fix the range argument
			$x = explode('.', $range);
			while(count($x)<4) $x[] = '0';
			list($a,$b,$c,$d) = $x;
			$range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
			$range_dec = ip2long($range);
			$ip_dec = ip2long($ip);
			# Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
			#$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));
			# Strategy 2 - Use math to create it
			$wildcard_dec = pow(2, (32-$netmask)) - 1;
			$netmask_dec = ~ $wildcard_dec;
			return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
		}
	} else {
		// range might be 255.255.*.* or 1.2.3.0-1.2.3.255
		if (strpos($range, '*') !==false) { // a.b.*.* format
			// Just convert to A-B format by setting * to 0 for A and 255 for B
			$lower = str_replace('*', '0', $range);
			$upper = str_replace('*', '255', $range);
			$range = "$lower-$upper";
		}
		if (strpos($range, '-')!==false) { // A-B format
			list($lower, $upper) = explode('-', $range, 2);
			$lower_dec = (float)sprintf("%u",ip2long($lower));
			$upper_dec = (float)sprintf("%u",ip2long($upper));
			$ip_dec = (float)sprintf("%u",ip2long($ip));
			return ( ($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
		}
		return false;
	}
}
function fsckeycdn_ip2long6($ip) {
	if (substr_count($ip, '::')) { 
		$ip = str_replace('::', str_repeat(':0000', 8 - substr_count($ip, ':')) . ':', $ip); 
	} 
	$ip = explode(':', $ip);
	$r_ip = ''; 
	foreach ($ip as $v) {
		$r_ip .= str_pad(base_convert($v, 16, 2), 16, 0, STR_PAD_LEFT); 
	} 
	return base_convert($r_ip, 2, 10);
}
function fsckeycdn_get_ipv6_full($ip) {
	$pieces = explode ("/", $ip, 2);
	$left_piece = $pieces[0];
	$right_piece = null;
	if (count($pieces) > 1) $right_piece = $pieces[1];

	// Extract out the main IP pieces
	$ip_pieces = explode("::", $left_piece, 2);
	$main_ip_piece = $ip_pieces[0];
	$last_ip_piece = null;
	if (count($ip_pieces) > 1) $last_ip_piece = $ip_pieces[1];

	// Pad out the shorthand entries.
	$main_ip_pieces = explode(":", $main_ip_piece);
	foreach($main_ip_pieces as $key=>$val) {
		$main_ip_pieces[$key] = str_pad($main_ip_pieces[$key], 4, "0", STR_PAD_LEFT);
	}

	// Check to see if the last IP block (part after ::) is set
	$last_piece = "";
	$size = count($main_ip_pieces);
	if (trim($last_ip_piece) != "") {
		$last_piece = str_pad($last_ip_piece, 4, "0", STR_PAD_LEFT);

		// Build the full form of the IPV6 address considering the last IP block set
		for ($i = $size; $i < 7; $i++) {
			$main_ip_pieces[$i] = "0000";
		}
		$main_ip_pieces[7] = $last_piece;
	}
	else {
		// Build the full form of the IPV6 address
		for ($i = $size; $i < 8; $i++) {
			$main_ip_pieces[$i] = "0000";
		}		
	}

	// Rebuild the final long form IPV6 address
	$final_ip = implode(":", $main_ip_pieces);

	return fsckeycdn_ip2long6($final_ip);
}
function fsckeycdn_ipv6_in_range($ip, $range_ip) {
	$pieces = explode ("/", $range_ip, 2);
	$left_piece = $pieces[0];
	$right_piece = $pieces[1];
	// Extract out the main IP pieces
	$ip_pieces = explode("::", $left_piece, 2);
	$main_ip_piece = $ip_pieces[0];
	$last_ip_piece = $ip_pieces[1];
	// Pad out the shorthand entries.
	$main_ip_pieces = explode(":", $main_ip_piece);
	foreach($main_ip_pieces as $key=>$val) {
		$main_ip_pieces[$key] = str_pad($main_ip_pieces[$key], 4, "0", STR_PAD_LEFT);
	}
	// Create the first and last pieces that will denote the IPV6 range.
	$first = $main_ip_pieces;
	$last = $main_ip_pieces;
	// Check to see if the last IP block (part after ::) is set
	$last_piece = "";
	$size = count($main_ip_pieces);
	if (trim($last_ip_piece) != "") {
		$last_piece = str_pad($last_ip_piece, 4, "0", STR_PAD_LEFT);
		// Build the full form of the IPV6 address considering the last IP block set
		for ($i = $size; $i < 7; $i++) {
			$first[$i] = "0000";
			$last[$i] = "ffff";
		}
		$main_ip_pieces[7] = $last_piece;
	}
	else {
		// Build the full form of the IPV6 address
		for ($i = $size; $i < 8; $i++) {
			$first[$i] = "0000";
			$last[$i] = "ffff";
		}		
	}
	// Rebuild the final long form IPV6 address
	$first = fsckeycdn_ip2long6(implode(":", $first));
	$last = fsckeycdn_ip2long6(implode(":", $last));
	$in_range = ($ip >= $first && $ip <= $last);
	return $in_range;
}

if($fsckeycdn_useHTTPS){
	$fsckeycdn_scheme = 'https';
} else {
	$fsckeycdn_scheme = 'http';
}

if($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
	$_SERVER['HTTPS'] = 'on';
}

function fsckeycdn_convert($s, $to=62) {
	$dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$res = '';
	$b = '';
	if($to > 0) {
		$to = ceil(log($to, 2));
		for($i=0; $i<strlen($s); $i++) $b .= sprintf('%04b', hexdec($s{$i}));
		while(strlen($b) >= $to) {
			$res = $dict{bindec(substr($b, -$to))} . $res;
			$b = substr($b, 0, -$to);
		}
		$res = $dict{bindec($b)} . $res;
		return $res;
	}
	$to = ceil(log(-$to, 2));
	for($i=0; $i<strlen($s); $i++) $b .= sprintf("%0{$to}b", strpos($dict, $s{$i}));
	while(strlen($b) > 4) {
		$res = $dict{bindec(substr($b, -4))} . $res;
		$b = substr($b, 0, -4);
	}
	if(bindec($b)) $res = $dict{bindec($b)} . $res;
	return $res;
}
if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])){
	$_SERVER['HTTP_HOST'] = explode(',',$_SERVER['HTTP_X_FORWARDED_HOST'])[0];// Has better support when using a proxy server.
}
if($fsckeycdn_variable_key){
	$fsckeycdn_x_pull_key = substr(fsckeycdn_convert('f'.md5($fsckeycdn_x_pull_key.$_SERVER['HTTP_HOST'])),-15);
}
if($_SERVER['HTTP_X_PULL'] == $fsckeycdn_x_pull_key) {
	$_SERVER['HTTPS'] = 'on';
	if($_SERVER['SCRIPT_NAME'] != '/index.php'){
		if(substr($fsckeycdn_realhost,0,4) == 'www.') {
			header('HTTP/1.1 302 Moved Temporarily');
			header('Location: '.$fsckeycdn_scheme.'://wp-admin.'.substr($fsckeycdn_realhost,4).$_SERVER['REQUEST_URI']);
			exit();
		} else {
			header('HTTP/1.1 302 Moved Temporarily');
			header('Location: '.$fsckeycdn_scheme.'://wp-admin-'.$fsckeycdn_realhost.$_SERVER['REQUEST_URI']);
			exit();
		}
	}
	$_COOKIE = [];// Clear Cookie if use KeyCDN, so when use KeyCDN, never show adminbar.
} else {
	if(!isset(explode('.',$fsckeycdn_realhost)[2]) && $_SERVER['SCRIPT_NAME'] == '/index.php'){
		header('HTTP/1.1 302 Moved Temporarily');
		header('Location: '.$fsckeycdn_scheme.'://www.'.$fsckeycdn_realhost.$_SERVER['REQUEST_URI']);
		exit();
	}

	if(isset($_SERVER['HTTP_CF_VISITOR'])){
		$visitor = json_decode($_SERVER['HTTP_CF_VISITOR'],true);
		if($visitor['scheme'] == 'https'){
			$_SERVER['HTTPS'] = 'on';
		}
	}

	if(isset($fsckeycdn_ip_white_list)&&isset($fsckeycdn_client_ip)){
		if(!fsckeycdn_ip_in_range($fsckeycdn_client_ip)){
			$fsckeycdn_403 = <<<HTML
<html>
<head><title>403 Forbidden</title></head>
<body bgcolor="white">
<center><h1>403 Forbidden</h1></center>
<hr><center>Full Site Cache Enabler for KeyCDN</center>
</body>
</html>

HTML;

			header('HTTP/1.1 403 Forbidden');
			exit($fsckeycdn_403);
		}
	}

	if(isset($fsckeycdn_client_real_ip)){
		$_SERVER['REMOTE_ADDR'] = $fsckeycdn_client_real_ip;
		$_SERVER['HTTP_CLIENT_IP'] = $fsckeycdn_client_real_ip;
		$_SERVER['HTTP_X_CLIENT_IP'] = $fsckeycdn_client_real_ip;
		$_SERVER['HTTP_X_FORWARDED_FOR'] = $fsckeycdn_client_real_ip;
	}
}
