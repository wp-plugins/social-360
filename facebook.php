<?php
/*
Get guide from : https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/v2.2
http://localhost/arpit/?get_contact=facebook
*/
class AheadzenFacebook
{
	var $accessToken;
	/**
	* IDp wrappers initializer 
	*/
	function __construct()
	{
		global $social_plugin_dir_url;
		$this->api_id = get_option('aheadzen_fb_api_id');
		$this->api_secret = get_option('aheadzen_fb_api_secret');
		$this->fb_authorize_url = "https://www.facebook.com/dialog/oauth";
		$this->fb_token_url = "https://graph.facebook.com/oauth/access_token";
		$this->fb_friends_url = 'https://graph.facebook.com/v2.2/me/friends?1=1'; //'?limit=500';
		$this->fb_redirect_url = site_url('/?hauth.done=Facebook');
		$this->fb_state = '74793e8a0faf418470c1404b564fe311';
		$this->fb_sdk = 'php-sdk-3.2.3';
		$this->fb_scope = 'email, user_about_me, user_birthday, user_hometown, user_website, read_stream, publish_actions, manage_friendlists, user_friends, read_friendlists';
	}
	
	/**
	* Inviter code 
	*/
	function inviter_url()
	{
		if(is_single() || is_page())
		{
			$theurl = get_permalink();
		}else{
			$theurl = site_url();
		}
		$dialogboxname = "Facebook Invite Friends";
		$theurl = 'https://www.facebook.com/v2.0/dialog/send?app_id='.$this->api_id.'&display=popup&href='.urlencode($theurl).'&link='.urlencode($theurl).'&next=https%3A%2F%2Fs-static.ak.facebook.com%2Fconnect%2Fxd_arbiter%2FDU1Ia251o0y.js%3Fversion%3D41%23cb%3Df1879f9d8937e08%26domain%3Dwp.timersys.com%26origin%3Dhttps%253A%252F%252Fwp.timersys.com%252Ff39ec7be6b85cb4%26relation%3Dopener%26frame%3Df24b173b34a539%26result%3D%2522xxRESULTTOKENxx%2522&sdk=joey&version=v2.0';
		return $theurl;
	}	
	
	function fb_friend_list()
	{		
		$loginURL = $this->fb_authorize_url.'?client_id='.$this->api_id.'&redirect_uri='.urlencode($this->fb_redirect_url).'&scope='.urlencode($this->fb_scope).'&display=page';
		//$loginURL = $this->fb_authorize_url.'?client_id='.$this->api_id.'&redirect_uri='.urlencode($this->fb_redirect_url).'&state='.urlencode($this->fb_state).'&sdk='.urlencode($this->fb_sdk).'&scope='.urlencode($this->fb_scope).'&display=page';
		wp_redirect($loginURL);exit;
		//https://graph.facebook.com/me/friends?limit=500&access_token=CAAHGUWpsgKABAOKGg5nemccZCnCvyxVtZBjZBTOHFmmfOE7dc5R6HY4ozb4OjPvIGRntqLyNR6jwLktcXzZBAyCICVJuGeXMsuK0HoFtEdhM4lBsqBne8tzlrDIHusvoZCBwUeLlijhPvZBjnxI8x8YBMZBaDsFGRBTAmkUZAwJcBowKynmZCIz81VYi7ygLnDNIgVQZCQYeCDFP2wdbZAzRtlE
		
	}
	
	function getUserContacts()
	{
		/*$url = $this->fb_token_url.'?client_id='.$this->api_id.'&client_secret='.$this->api_secret.'
		&redirect_uri='.urlencode($this->fb_redirect_url).'&code='.$this->accessToken;*/
		$oauth2token_url = $this->fb_token_url."?client_id=".$this->api_id."&client_secret=".$this->api_secret."&code=".$this->accessToken."&redirect_uri=".urlencode($this->fb_redirect_url);
		$accessToken = $this->get_fb_token($oauth2token_url);
		if (isset($accessToken) && $accessToken!='')
		{
			$this->fb_friends_url .= '&access_token='.$accessToken;
			$response = $this->fb_call_api($accessToken,$this->fb_friends_url);
		}
		echo 'Contacts Here <br />';
		echo '<pre>';
		print_r($response);
		echo '</pre>';
		exit;
	}
	
	//calls api and gets the data
	function fb_call_api($accessToken,$url){
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,FALSE);
		$curlheader[0] = "Authorization: Bearer " . $accessToken;
		curl_setopt($curl, CURLOPT_HTTPHEADER, $curlheader);
		
		if(!$json_response = curl_exec($curl))
		{
			trigger_error(curl_error($curl));
		} 
		//$json_response = curl_exec($curl);
		curl_close($curl);
		
		$responseObj = json_decode($json_response);
		return $responseObj;	    
	}
	
	//calls api and gets the data
	function get_fb_token($url){
	
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,FALSE);
		
		if(!$json_response = curl_exec($curl))
		{
			trigger_error(curl_error($curl));
		} 
		//$json_response = curl_exec($curl);
		curl_close($curl);
		//access_token=CAAHGUWpsgKABAEWM6JVsQZByq3O3ugd6T4AJwRpMdO5shhGmtZC7TMrd0caWsUcUVETutKxH2zZBWWhg08syoXtnWR5HZAlyj3J4ZAiunBMVIY5Fx2XZCeB7CWCLmE2v2sbEHbYaYSkiSlfu8mZBN7MPfONWX9HpdOJp8jVWRjh76ZACSTGT4EtOi3nty8K3RY92VFlB22yQK15ihamqY5ek
		//&expires=5177481
		$response_obj = parse_str($json_response);
		return $access_token;
	}
	
}
