<?php
class Plumrocket_Invitations_Helper_Yahoo extends Mage_Core_Helper_Abstract
{
	
	protected $_yahooABook = null;
	
	public function getYahooABook()
	{
		if (is_null($this->_yahooABook)){
			$this->_yahooABook = Mage::getModel('invitations/addressbooks')->getByKey('yahoo');
		}
		return $this->_yahooABook;
	}
	
	public function getOauthConsumerKey()
	{
		return $this->getYahooABook()->getSettingByKey('consumer_key');
	}
	
	public function getOauthConsumerSecret()
	{
		return $this->getYahooABook()->getSettingByKey('consumer_secret');
	}
	
	public function oauthHttpBuildQuery($params, $excludeOauthParams=false)
	{
		$query_string = '';
		if (! empty($params)) {
			$keys = $this->getEncoded(array_keys($params));
			$values = $this->getEncoded(array_values($params));

			$params = array_combine($keys, $values);
			uksort($params, 'strcmp');

			$kvpairs = array();
			foreach ($params as $k => $v) {
				if ($excludeOauthParams && substr($k, 0, 5) == 'oauth') {
					continue;
				}
				if (is_array($v)) {
					natsort($v);
					foreach ($v as $value_for_same_key) {
						array_push($kvpairs, ($k . '=' . $value_for_same_key));
					}
				} else {
					array_push($kvpairs, ($k . '=' . $v));
				}
			}
			$query_string = implode('&', $kvpairs);
		}

		return $query_string;
	}

	public function oauthParseString($query_string)
	{
		$query_array = array();
		if (isset($query_string)) {
			$kvpairs = explode('&', $query_string);
			foreach ($kvpairs as $pair) {
				list($k, $v) = explode('=', $pair, 2);
				if (isset($query_array[$k])) {
					if (is_scalar($query_array[$k])) {
						$query_array[$k] = array($query_array[$k]);
					}
					array_push($query_array[$k], $v);
				} else {
					$query_array[$k] = $v;
				}
			}
		}

		return $query_array;
	}

	public function buildOauthHeader($params, $realm='')
	{
		$header = 'Authorization: OAuth realm="' . $realm . '"';
		foreach ($params as $k => $v) {
			if (substr($k, 0, 5) == 'oauth') {
				$header .= ',' . $this->getEncoded($k) . '="' . $this->getEncoded($v) . '"';
			}
		}
		return $header;
	}

	public function oauthComputePlaintextSig($consumer_secret, $token_secret)
	{
		return ($consumer_secret . '&' . $token_secret);
	}

	public function oauthComputeHmacSig($http_method, $url, $params, $consumer_secret, $token_secret)
	{
		$base_string = $this->signatureBaseString($http_method, $url, $params);
		$signature_key = $this->getEncoded($consumer_secret) . '&' . $this->getEncoded($token_secret);
		$sig = base64_encode(hash_hmac('sha1', $base_string, $signature_key, true));
		return $sig;
	}

	public function normalizeUrl($url)
	{
		$parts = parse_url($url);

		$scheme = isset($parts['scheme']) ? $parts['scheme'] : null;
		$host = isset($parts['host']) ? $parts['host'] : null;
		$port = isset($parts['port']) ? $parts['port'] : null;
		$path =isset($parts['path']) ?  $parts['path'] : null;

		if (! $port) {
			$port = ($scheme == 'https') ? '443' : '80';
		}
		if (($scheme == 'https' && $port != '443')
			|| ($scheme == 'http' && $port != '80')) {
			$host = "$host:$port";
		}

		return "$scheme://$host$path";
	}

	public function signatureBaseString($http_method, $url, $params)
	{
		$query_str = parse_url($url, PHP_URL_QUERY);
		if ($query_str) {
			$parsed_query = $this->oauthParseString($query_str);
			$params = array_merge($params, $parsed_query);
		}

		if (isset($params['oauth_signature'])) {
		unset($params['oauth_signature']);
		}

		$base_string = $this->getEncoded(strtoupper($http_method)) . '&' .
					 $this->getEncoded($this->normalizeUrl($url)) . '&' .
					 $this->getEncoded($this->oauthHttpBuildQuery($params));
		return $base_string;
	}

	public function getEncoded($raw_input)
	{
		if (is_array($raw_input)) {
			foreach($raw_input as $key => $value){
				$raw_input[$key] = $this->getEncoded($value);
			}
			return $raw_input;
		} else if (is_scalar($raw_input)) {
			return str_replace('%7E', '~', rawurlencode($raw_input));
		} else {
			return '';
		}
	}

	public function getDecoded($raw_input)
	{
		return rawurldecode($raw_input);
	}
	
	public function getRequestToken($consumer_key, $consumer_secret, $callback, $usePost=false, $useHmacSha1Sig=true, $passOAuthInHeader=false)
	{
		$retarr = array();	// return value
		$response = array();

		$url = 'https://api.login.yahoo.com/oauth/v2/get_request_token';
		$params['oauth_version'] = '1.0';
		$params['oauth_nonce'] = mt_rand();
		$params['oauth_timestamp'] = time();
		$params['oauth_consumer_key'] = $consumer_key;
		$params['oauth_callback'] = $callback;

		// compute signature and add it to the params list
		if ($useHmacSha1Sig) {
		$params['oauth_signature_method'] = 'HMAC-SHA1';
		$params['oauth_signature'] =
			$this->oauthComputeHmacSig($usePost? 'POST' : 'GET', $url, $params,
								 $consumer_secret, null);
		} else {
		$params['oauth_signature_method'] = 'PLAINTEXT';
		$params['oauth_signature'] =
			$this->oauthComputePlaintextSig($consumer_secret, null);
		}

		if ($passOAuthInHeader) {
			$query_parameter_string = $this->oauthHttpBuildQuery($params, FALSE);
			$header = $this->buildOauthHeader($params, "yahooapis.com");
			$headers[] = $header;
		} else {
			$query_parameter_string = $this->oauthHttpBuildQuery($params);
		}
	
		if ($usePost) {
			$request_url = $url;
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			$response = $this->doPost($request_url, $query_parameter_string, 443, $headers);
		} else {
			$request_url = $url . ($query_parameter_string ? ('?' . $query_parameter_string) : '' );
			$response = $this->doGet($request_url, 443, $headers);
		}

		if (! empty($response)) {
			list($info, $header, $body) = $response;
			$body_parsed = $this->oauthParseString($body);

			$retarr = $response;
			$retarr[] = $body_parsed;
		}

		return $retarr;
	}
	
	
	
	public function doGet($url, $port=80, $headers=NULL)
	{
		$retarr = array();
		
		$curl_opts = array(CURLOPT_URL => $url,
						 CURLOPT_PORT => $port,
						 CURLOPT_POST => false,
						 CURLOPT_SSL_VERIFYHOST => false,
						 CURLOPT_SSL_VERIFYPEER => false,
						 CURLOPT_RETURNTRANSFER => true);

		if ($headers) { $curl_opts[CURLOPT_HTTPHEADER] = $headers; }
	 
		$response = $this->doCurl($curl_opts);
		
		if (! empty($response)) { $retarr = $response; }
		
		return $retarr;
	}

	public function doPost($url, $postbody, $port=80, $headers=NULL)
	{
		$retarr = array();	// Return value

		$curl_opts = array(CURLOPT_URL => $url,
						 CURLOPT_PORT => $port,
						 CURLOPT_POST => true,
						 CURLOPT_SSL_VERIFYHOST => false,
						 CURLOPT_SSL_VERIFYPEER => false,
						 CURLOPT_POSTFIELDS => $postbody,
						 CURLOPT_RETURNTRANSFER => true);

		if ($headers) { $curl_opts[CURLOPT_HTTPHEADER] = $headers; }

		$response = $this->doCurl($curl_opts);

		if (! empty($response)) { $retarr = $response; }

		return $retarr;
	}

	public function doCurl($curl_opts)
	{
		$retarr = array();
		if (! $curl_opts) {
			return $retarr;
		}
		$ch = curl_init();
		 
		if (! $ch) {
			return $retarr;
		}
		curl_setopt_array($ch, $curl_opts);
		curl_setopt($ch, CURLOPT_HEADER, true);
		ob_start();
		$response = curl_exec($ch);
		$curl_spew = ob_get_contents();
		ob_end_clean();

		if (curl_errno($ch)) {
			$errno = curl_errno($ch);
			$errmsg = curl_error($ch);
			curl_close($ch);
			unset($ch);
			return $retarr;
		}
		$info = curl_getinfo($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size );
		curl_close($ch);
		unset($ch);
		array_push($retarr, $info, $header, $body);

		return $retarr;
	}
	
	public function getAccessToken($consumer_key, $consumer_secret, $request_token, $request_token_secret, $oauth_verifier, $usePost=false, $useHmacSha1Sig=true, $passOAuthInHeader=true)
	{
		$retarr = array();	
		$response = array();

		$url = 'https://api.login.yahoo.com/oauth/v2/get_token';
		$params['oauth_version'] = '1.0';
		$params['oauth_nonce'] = mt_rand();
		$params['oauth_timestamp'] = time();
		$params['oauth_consumer_key'] = $consumer_key;
		$params['oauth_token']= $request_token;
		$params['oauth_verifier'] = $oauth_verifier;

		if ($useHmacSha1Sig) {
			$params['oauth_signature_method'] = 'HMAC-SHA1';
			$params['oauth_signature'] = $this->oauthComputeHmacSig($usePost? 'POST' : 'GET', $url, $params, $consumer_secret, $request_token_secret);
		} else {
			$params['oauth_signature_method'] = 'PLAINTEXT';
			$params['oauth_signature'] = $this->oauthComputePlaintextSig($consumer_secret, $request_token_secret);
		}

		if ($passOAuthInHeader) {
			$query_parameter_string = $this->oauthHttpBuildQuery($params, false);
			$header = $this->buildOauthHeader($params, "yahooapis.com");
			$headers[] = $header;
		} else {
			$query_parameter_string = $this->oauthHttpBuildQuery($params);
		}

		if ($usePost) {
			$request_url = $url;
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			$response = doPost($request_url, $query_parameter_string, 443, $headers);
		} else {
			$request_url = $url . ($query_parameter_string ? ('?' . $query_parameter_string) : '' );
			$response = $this->doGet($request_url, 443, $headers);
		}
		
		if (! empty($response)) {
			list($info, $header, $body) = $response;
			$body_parsed = $this->oauthParseString($body);

			$retarr = $response;
			$retarr[] = $body_parsed;
		}

		return $retarr;
	}
	
	public function callectContacts($consumer_key, $consumer_secret, $guid, $access_token, $access_token_secret, $usePost=false, $passOAuthInHeader=true)
	{
		$retarr = array();
		$response = array();

		//$url = 'http://social.yahooapis.com/v1/user/' . $guid . '/contacts;';
		$url = 'https://query.yahooapis.com/v1/yql';
		$query = sprintf('SELECT * FROM social.contacts(%s,%s) WHERE guid="%s"', 0, 5000, $guid);
		$params = array('q' => $query, 'format' => 'json', 'env' => 'http://datatables.org/alltables.env');

		$params['format'] = 'json';
		$params['view'] = 'compact';
		$params['oauth_version'] = '1.0';
		$params['oauth_nonce'] = mt_rand();
		$params['oauth_timestamp'] = time();
		$params['oauth_consumer_key'] = $consumer_key;
		$params['oauth_token'] = $access_token;

		// compute hmac-sha1 signature and add it to the params list
		$params['oauth_signature_method'] = 'HMAC-SHA1';
		$params['oauth_signature'] =
			$this->oauthComputeHmacSig($usePost? 'POST' : 'GET', $url, $params,
								 $consumer_secret, $access_token_secret);

		// Pass OAuth credentials in a separate header or in the query string
		if ($passOAuthInHeader) {
			$query_parameter_string = $this->oauthHttpBuildQuery($params, true);
			$header = $this->buildOauthHeader($params, "yahooapis.com");
			$headers[] = $header;
		} else {
			$query_parameter_string = $this->oauthHttpBuildQuery($params);
		}

		// POST or GET the request
		if ($usePost) {
			$request_url = $url;
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			$response = $this->doPost($request_url, $query_parameter_string, 443, $headers);
		} else {
			$request_url = $url . ($query_parameter_string ?
									 ('?' . $query_parameter_string) : '' );

			$response = $this->doGet($request_url, 443, $headers);
		}

		if (! empty($response)) {
			list($info, $header, $body) = $response;

			$retarr = $response;
		}

		return $retarr;
	}
		
	
	function Unicode2Charset($str, $charset = null) { 
		
		if (is_null($charset)){
			$charset = Mage::getStoreConfig('design/head/default_charset');
		}
		
		$str = str_replace('&amp;#', '&#', $str);
		
		return preg_replace(
			'~&#(?:x([\da-f]+)|(\d+));~ie',
			'iconv("UTF-16LE", $charset, pack("v", "$1" ? hexdec("$1") : "$2"))',
			$str
		);
	}			

}

	
