<?php
/*
Get guide from : http://www.phpbuilder.com/articles/application-architecture/miscellaneous/oauth-authentication-for-social-apps-in-php.html
http://localhost/arpit/?get_contact=twitter
*/

class Twitter
{
    const AUTHORIZE_URI = 'https://api.twitter.com/oauth/authorize';
    const REQUEST_URI = 'https://api.twitter.com/oauth/request_token';
    const ACCESS_URI = 'https://api.twitter.com/oauth/access_token';  
    const API_URI = 'https://api.twitter.com/';
 
    private $consumer_key = 'S6iAtr3No8GDJ43NNt1yK1GzD';
    private $consumer_secret = '1Hil0YHInDX2RbxRevKquXScSbIwyLAkgFWICWtN3tEYrlSQyq';
    private $method = 'GET';
    private $algorithm = 'HMAC-SHA1';
	
	public function get_request_token($callback)
    {
		//Generate an array with the initial oauth values we need
        $auth = $this->build_auth_array(self::REQUEST_URI,
                                       $this->consumer_key,
                                       $this->consumer_secret,
                                                     array('oauth_callback' => urlencode($callback)),
                                                     $this->method,
                                                     $this->algorithm);
      echo $str = $this->build_auth_string($auth); // build auth string which will be added to HTTP header
       
	   //Send the request
        $response = $this->connect(self::REQUEST_URI, $str);
       
		echo 'RESPONSE';
		print_r($response);
        //We should get back a request token and secret which
        //we will add to the redirect url.
        parse_str($response, $resarray); // parse the response string into array
        
        return $resarray;
    }
	
	 public function get_access_token($token = FALSE, $secret = FALSE, $verifier = FALSE)
    {
        //If no request token was specified then attempt to get one from the url
        if($token === FALSE && isset($_GET['oauth_token']))
        {
             $token = $_GET['oauth_token'];
        }
        
        if($verifier === FALSE && isset($_GET['oauth_verifier']))
        {
             $verifier = $_GET['oauth_verifier'];
        }
        
        //If all else fails attempt to get it from the request uri.
        if($token === FALSE && $verifier === FALSE)
        {
            $uri = $_SERVER['REQUEST_URI'];
            $uriparts = explode('?', $uri);
 
            $authfields = array();
            parse_str($uriparts[1], $authfields);
            $token = $authfields['oauth_token'];
            $verifier = $authfields['oauth_verifier'];
        }
        
        $tokenddata = array('oauth_token' => urlencode($token),
                                 'oauth_verifier' => urlencode($verifier));
        
        if($secret !== FALSE)
        {
             $tokenddata['oauth_token_secret'] = urlencode($secret);
        }
        
        //Include the token and verifier into the header request.
        $auth = get_auth_header(self::ACCESS_URI,
                            $this->consumer_key,
                            $this->consumer_secret,
                                          $tokenddata,
                                          $this->method,
                                              $this->algorithm);
                                
        $response = $this->connect(self::ACCESS_URI, $auth);
        

        //Parse the response into an array it should contain
        //both the access token and the secret key. (You only
        //need the secret key if you use HMAC-SHA1 signatures.)
        parse_str($response, $oauth);
        
	    //Return the token and secret for storage
        return $oauth;
    }
	
	function build_auth_array($baseurl, $key, $secret, $extra = array(), $method = 'GET', $algo = OAUTH_ALGORITHMS::RSA_SHA1)
	{
		$auth['oauth_consumer_key'] = $key;
		$auth['oauth_signature_method'] = $algo;
		$auth['oauth_timestamp'] = time();
		$auth['oauth_nonce'] = md5(uniqid(rand(), true));
		$auth['oauth_version'] = '1.0';
		$auth = array_merge($auth, $extra);
		//We want to remove any query parameters from the base url
		$urlsegs = explode("?", $baseurl);
		$baseurl = $urlsegs[0];
		//If there are any query parameters we need to make sure they
		//get signed with the rest of the auth data.
		$signing = $auth;
		if(count($urlsegs) > 1)
		{
		preg_match_all("/([\w\-]+)\=([\w\d\-\%\.\$\+\*]+)\&?/", $urlsegs[1], $matches);
		$signing = $signing + array_combine($matches[1], $matches[2]);
		}
		if(strtoupper($algo) == $this->algorithm)$auth['oauth_signature'] = $this->sign_hmac_sha1($method, $baseurl, $secret, $signing);
		else if(strtoupper($algo) == $this->algorithm)$auth['oauth_signature'] = $this->sign_rsa_sha1 ($method, $baseurl, $secret, $signing);
		$auth['oauth_signature'] = urlencode($auth['oauth_signature']);
		return $auth;
	}

	function build_auth_string(array $authparams)
	{
		$header = "Authorization: OAuth ";
		$auth = '';
		foreach($authparams AS $key=>$value)
		{
		//Don't include token secret
		if($key != 'oauth_token_secret')$auth .= ", {$key}=\"{$value}\"";
		}
		return $header.substr($auth, 2)."\r\n";
	}
	
	function sign_hmac_sha1($method, $baseurl, $secret, array $parameters)
	{
	$data = $method.'&';
	$data .= urlencode($baseurl).'&';
	$oauth = '';
	ksort($parameters);
	//Put the token secret in if it does not exist. It
	//will be empty if it does not exist as per the spec.
	if(!array_key_exists('oauth_token_secret', $parameters))$parameters['oauth_token_secret'] = '';
	foreach($parameters as $key => $value)
	{
	//Don't include the token secret into the base string
	if(strtolower($key) != 'oauth_token_secret')$oauth .= "&{$key}={$value}";
	}
	$data .= urlencode(substr($oauth, 1));
	$secret .= '&'.$parameters['oauth_token_secret'];
	return base64_encode(hash_hmac('sha1', $data, $secret, true));
	}
	
	function sign_rsa_sha1($method, $baseurl, $certfile, array $parameters)
	{
	$fp = fopen($certfile, "r");
	$private = fread($fp, 8192);
	fclose($fp);
	$data = $method.'&';
	$data .= urlencode($baseurl).'&';
	$oauth = '';
	ksort($parameters);
	foreach($parameters as $key => $value)
	$oauth .= "&{$key}={$value}";
	$data .= urlencode(substr($oauth, 1));
	$keyid = openssl_get_privatekey($private);
	openssl_sign($data, $signature, $keyid);
	openssl_free_key($keyid);
	return base64_encode($signature);
	}
	
	function connect($url, $auth)
    {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE);
		$curlheader[0] = $auth;
		curl_setopt($curl, CURLOPT_HTTPHEADER, $curlheader);
		
		if(!$json_response = curl_exec($curl))
		{
			trigger_error(curl_error($curl));
		} 
		//echo '<pre>';
		//print_r(curl_getinfo($curl));
		//print_r($json_response);exit;
		curl_close($curl);
		
		return $json_response;
    }
}



$twitter = new Twitter(); // the class from above , where all the functions are stored
$response = $twitter->get_request_token(site_url('/?hauth.done=Twitter'));
 
 print_r($response);exit;
$_SESSION['twitter_token'] = $response['oauth_token'];
$_SESSION['twitter_token_secret'] = $response['oauth_token_secret'];
 
header("Location:". self::AUTHORIZE_URI."?oauth_token={$token}");

