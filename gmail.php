<?php
// disable warnings
//error_reporting(E_ERROR); 

class AheadzenGoogle
{
	var $scope,$client_id,$client_secret,$redirect_uri,$refresh_token,$accessToken;
	
	/**
	* IDp wrappers initializer 
	*/
	function __construct()
	{
		global $social_plugin_dir_url;
		$this->client_id = get_option('aheadzen_google_client_id');
		$this->client_secret = get_option('aheadzen_google_client_secret');
		$this->google_max_contacs = get_option('aheadzen_google_max_contacs');
		$this->redirect_uri = site_url('/?hauth.done=Google');
		if(!$this->google_max_contacs){$this->google_max_contacs=200;}
		// Provider api end-points
		$this->default_photo = $social_plugin_dir_url.'/images/default_photo.png';
		$this->authorize_url  = "https://accounts.google.com/o/oauth2/auth";
		$this->token_url      = "https://accounts.google.com/o/oauth2/token";
		//$this->token_info_url = "https://www.googleapis.com/oauth2/v2/tokeninfo";
		$this->googleUserInfoAPI = "https://www.googleapis.com/oauth2/v1/userinfo";
		$this->googleUserContactAPI = 'https://www.google.com/m8/feeds/contacts/default/full/?alt=json&v=3.0&max-results='.$this->google_max_contacs;
		$this->googlePlusUserContactAPI = "https://www.googleapis.com/plus/v1/people/me/people/visible?max-results=".$this->google_max_contacs;	
		$this->scope = "https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/plus.profile.emails.read https://www.google.com/m8/feeds/";
	}
	
	/**
	* begin login step 
	*/
	function loginBegin()
	{
		$url = $this->authorize_url.'?client_id='.($this->client_id).'&redirect_uri='.urlencode($this->redirect_uri).'&response_type=code&scope='.urlencode($this->scope).'&access_type=offline';
		wp_redirect($url);exit;
	}	
	
	/**
	* load the user (Gmail and google plus) contacts 
	*  ..toComplete
	*/
	function getUserContacts()
	{ 
		$accessToken = $this->get_oauth2_token($this->accessToken,'online');
		$contacts = array();
		// Google Gmail and Android contacts
		if (isset($accessToken) && $accessToken!='')
		{
			$invitor_profile_obj = $this->call_api($accessToken,$this->googleUserInfoAPI);
			$invitor_profile_name = $invitor_profile_obj->name;
		}
		
		if (isset($accessToken) && $accessToken!='' && strpos($this->scope, '/m8/feeds/') !== false) {
	
			$response = $this->call_api($accessToken,$this->googleUserContactAPI);
				
			if( ! $response ){
				return array();
			}
			if (isset($response->feed->entry)) {
				/*echo '<pre>';
				print_r($response->feed->entry);
				echo '</pre>';exit;*/
				foreach( $response->feed->entry as $idx => $entry ){
					$uc = array();
					$uc['email'] = $email = isset($entry->{'gd$email'}[0]->address) ? (string) $entry->{'gd$email'}[0]->address : '';
					$uc['displayName'] = $displayName 	= isset($entry->title->{'$t'}) ? (string) $entry->title->{'$t'} : '';
					$uc['identifier'] = $email;
					$uc['website'] = $website = isset($entry->{'gContact$website'}[0]->href) ? (string) $entry->{'gContact$website'}[0]->href : '';
					if( property_exists($entry,'link') ){
						/**
						 * sign links with access_token
						 */
						if(is_array($entry->link)){
							foreach($entry->link as $l){
								if( property_exists($l,'gd$etag') && $l->type=="image/*"){
									$uc['photo'] = $this->addUrlParam($l->href, array('access_token' => $accessToken));
								} else if($l->type=="self"){
									$uc['photo'] = $this->addUrlParam($l->href, array('access_token' => $accessToken));
								}
							}
						}
					} else {
						$uc->profileURL = '';
					}
					if($uc['email']){ $contacts[] = $uc; }
				}
			}
		}
		
		if (isset($this->refresh_token) && $accessToken && strpos($this->scope, '/auth/plus.login') !== false) {
			$response = $this->call_api($accessToken,$this->googlePlusUserContactAPI);
			if( ! $response ){
				return array();
			}
			foreach( $response->items as $idx => $item ){
				$uc = array();
				$uc['email'] = $email = (property_exists($item,'email'))?$item->email:'';   
				$uc['displayName'] = $displayName = (property_exists($item,'displayName'))?$item->displayName:'';  
				$uc['identifier'] = $identifier = (property_exists($item,'id'))?$item->id:'';
				$uc['website'] = '';
				$uc['photo'] = (property_exists($item,'image'))?((property_exists($item->image,'url'))?$item->image->url:''):'';
				if($uc['email']){ $contacts[] = $uc; }
			}
		}
		
		if($contacts)
		{

$aheadzen_invitation_subject = get_option('aheadzen_invitation_subject');
$aheadzen_invitation_message = get_option('aheadzen_invitation_message');
$bloglinkwithurl = '<a href="'.site_url().'">'.get_bloginfo().'</a>';		
global $social_replace_constants_key;
$srch_arr = $social_replace_constants_key;
$rpl_arr = array($invitor_profile_name,get_bloginfo(),$bloglinkwithurl);
$aheadzen_invitation_subject = str_replace($srch_arr,$rpl_arr,$aheadzen_invitation_subject);
$aheadzen_invitation_message = str_replace($srch_arr,$rpl_arr,$aheadzen_invitation_message);
?>
<html>
<head>
<style>
body{}
.list {font-family:sans-serif;margin:0;padding:0;font-size:12px;}
input {border:solid 1px #ccc;border-radius: 5px;padding:7px 14px;margin-bottom:10px}
input:focus {outline:none;border-color:#aaa;}
.sort {padding:8px 15px;border-radius: 6px;border:none;display:inline-block;color:#fff;text-decoration: none;background-color: #28a8e0;height:30px;}
.sort:hover {text-decoration: none;background-color:#1b8aba;}
.sort:focus {outline:none;}
.sort:after {width: 0;height: 0;border-left: 5px solid transparent;border-right: 5px solid transparent;border-bottom: 5px solid transparent;content:"";position: relative;top:-10px;right:-5px;}
.sort.asc:after {width: 0;height: 0;border-left: 5px solid transparent;border-right: 5px solid transparent;border-top: 5px solid #fff;content:"";position: relative;top:13px;right:-5px;}
.sort.desc:after {width: 0;height: 0;border-left: 5px solid transparent;border-right: 5px solid transparent;border-bottom: 5px solid #fff;content:"";position: relative;top:-10px;right:-5px;}
ul.social_listing li{vertical-align:top;clear:both;display:inline-block;height:32px;width:49%;border: 1px solid #d3edff;padding:2px 0;}
ul.social_listing img{width:32px;height:32px;float:left;margin-right:5px;}
ul.social_listing b{color:#424242;padding:5px 0;vertical-align:middle;font-weight:normal;}
ul.social_listing span{color: #b3b3b3;display: block;font-size: 12px;font-style: normal;}
#userlist{background:none repeat scroll 0 0 #fff;border:1px solid #cacaca;height:267px;overflow:auto;}
.gcheckbox{float: left;margin-right:5px;}
li label{width:98%;display: inline-block;}
#submit-button {display: block;font-size: 26px;height: 40px;margin: 20px auto 0;padding: 0 22px;background-color: #21759b;background-image: linear-gradient(to bottom, #2a95c5, #21759b);border-color: #21759b #21759b #1e6a8d;border-radius: 3px;border-style: solid;border-width: 1px;box-shadow: 0 1px 0 rgba(120, 200, 230, 0.5) inset;box-sizing: border-box;color: #fff;cursor: pointer;display: inline-block;font-size: 12px;height: 24px;line-height: 23px;margin: 0;padding: 0 10px 1px;text-decoration: none;text-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);white-space: nowrap;}
.text{width:99%;}
.textarea{width:99%;height:150px;}
.send_invite_list{margin:5px 0;}
.send_invite_list label{color: #333;font-family: sans-serif;font-size: 14px;font-weight:bold;}
.invite_emls{margin-top: 10px;}
.invite_to_div{width:100%;min-height:1px;}
.send_invite_list.to_face{margin-top:10px;}
ul.invite_to_div{  list-style: outside none none;margin: 0 0 10px;padding: 0;}
ul.invite_to_div li{display: inline-block;margin:2px;padding:2px 3px;background-color: #c5eefa;border:1px solid #5dc8f7;color: #4594b5;border-radius: 3px; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;vertical-align: middle;}
ul.invite_to_div li a{color: #000000;font-weight: bold;margin-left: 4px;opacity: 0.2;padding: 0 2px;cursor: pointer;}
</style>
</head>
<body>
<form name="gml_send_invitation" action="" method="POST">
<input type="hidden" name="google_send_invitations" value="sendinvite" />
<input type="hidden" name="invitor_name" value="<?php echo $invitor_profile_name;?>" />
<center><h2>Select Your Friends</h2></center>
<div id="agoogle_users">
<input class="search" placeholder="Search" />
<input type="button" class="sort" data-sort="geml" value="Sort by e-mail">
<label for="selecctall"><input type="checkbox" id="selecctall"/> Select All/None</label>
<div id="userlist">

			<?php
			echo '<ul class="social_listing list">';
			for($c=0;$c<count($contacts);$c++)
			{
				$email = $contacts[$c]['email'];				
				$displayName = $contacts[$c]['displayName'];
				$photo = $contacts[$c]['photo'];
				if(!$photo){$photo = $this->default_photo;}
				$srch = array('@','.');
				$repl = array('_','_');
				$email_str = str_replace($srch,$repl,$email);
				echo '<li><label for="'.$email_str.'">';
				echo '<input id="'.$email_str.'" class="gcheckbox" type="checkbox" name="check[]" value="'.$email.'">';
				echo '<img src="'.$photo.'" alt="" />';
				echo '<b class="geml">'.$email.'</b>';
				echo '<span class="gdsname">'.$displayName.'</span>';
				echo '</label></li>';
			}
			echo '</ul>';
			
			AheadDB::insert_social_contact_google($contacts); //insert data in to db
			
		

global $social_plugin_dir_url;
?>
</div></div>
<div class="send_invite_list to_face"><label>Sending Invitations To</label>
<ul class="invite_to_div">
<li><?php _e('Please select your contacts first.','aheadzen');?></li>
</ul>
</div>
<div class="send_invite_list invite_emls"><label>Invite by E-mail Address (Use commas to separate e-mails)</label>
<center><input class="text" type="text" name="invite_emls" value="<?php echo $aheadzen_invite_emls;?>" ></center>
</div>
<div class="send_invite_list"><label>Subject</label>
<center><input class="text" type="text" name="subject" value="<?php echo $aheadzen_invitation_subject;?>" ></center>
</div>

<div class="send_invite_list"><label>Message</label>
<textarea class="textarea" name="message"><?php echo $aheadzen_invitation_message;?></textarea>
<ul>
<?php global $social_plugin_replace_constants;
foreach($social_plugin_replace_constants as $key=>$val)
{
	echo '<li><b>'.$key.'</b> : '.$val.'</li>';
}
?>
</ul>

</div>
<div class="send_invite_list" style="text-align: right">
<input type="submit" id="submit-button" value="SEND">
</div>
<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
<script src="<?php echo $social_plugin_dir_url.'/js/list.js';?>"></script>
<script>
var options = {
  valueNames: ['geml','gdsname']
};
var userList = new List('agoogle_users', options);

function ahdzn_rmv_tag($obj)
{
	var li_str = jQuery($obj).closest('li').html();
	var li_res = li_str.split(" "); 
	var li_eml = jQuery.trim(li_res[0]);
	li_eml = li_eml.replace(/\@/g, '_');
	li_eml = li_eml.replace(/\./g, '_');
	jQuery('ul.social_listing li #'+li_eml).attr('checked', false);
	jQuery($obj).closest('li').hide();
} 

function add_to_detail()
{
	jQuery('ul.invite_to_div').html('');
	jQuery('ul.social_listing li input:checkbox').each(function () {
       if (jQuery(this).is(':checked')) {
			jQuery('ul.invite_to_div').append( '<li>'+jQuery(this).val()+' <a onclick="ahdzn_rmv_tag(this)">x</a></li>');
		}
	});
}

jQuery(document).ready(function() {
jQuery('ul.social_listing li input:checkbox').click(function() {
	add_to_detail();
});

jQuery('#selecctall').click(function(event) {  //on click
        if(this.checked) { // check select status
            jQuery('.gcheckbox').each(function() { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"
            });
        }else{
            jQuery('.gcheckbox').each(function() { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"                      
            });        
        }
		add_to_detail();
	});
   
});
</script>
</form>
</body>
</html>
<?php		
}else{
	echo '<center><h2>Cannot continue the process. <br /> Sorry for inconvenience.</h2></center>';
}
		/*echo '<pre>';
		print_r($contacts);
		echo 'END HERE';
		*/
		exit;
		
		return $contacts;
 	}
	
	function resetTheCookie()
	{
		unset($_COOKIE['google_accesstoken']);
		unset($_COOKIE['google_refreshtoken']);
		setcookie('google_accesstoken', null, -1, COOKIEPATH, COOKIE_DOMAIN, false);
		setcookie('google_refreshtoken', null, -1, COOKIEPATH, COOKIE_DOMAIN, false);
	}
	/**
	 * Add to the $url new parameters
	 * @param string $url
	 * @param array $params
	 * @return string
	 */
	function addUrlParam($url, array $params)
	{
		$query = parse_url($url, PHP_URL_QUERY);

		// Returns the URL string with new parameters
		if( $query ) {
			$url .= '&' . http_build_query( $params );
		} else {
			$url .= '?' . http_build_query( $params );
		}
		return $url;
	}
	
	//calls api and gets the data
	function call_api($accessToken,$url){
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

	//returns session token for calls to API using oauth 2.0
	//set global refreshToken var if refresh token is returned
	function get_oauth2_token($grantCode,$grantType="offline") {
		
		$oauth2token_url = $this->token_url;
		$clienttoken_post = array(
			"client_id" => $this->client_id,
			"client_secret" => $this->client_secret
			);
		
		if ($grantType === "online"){
			$clienttoken_post["code"] = $grantCode;	
			$clienttoken_post["redirect_uri"] = $this->redirect_uri;
			$clienttoken_post["grant_type"] = "authorization_code";
		}else
		if ($grantType === "offline"){
			$clienttoken_post["refresh_token"] = $grantCode;
			$clienttoken_post["grant_type"] = "refresh_token";
		}
		$curl = curl_init($oauth2token_url);

		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $clienttoken_post);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		if(!$json_response = curl_exec($curl))
		{
			trigger_error(curl_error($curl));
		}
		
		//$json_response = curl_exec($curl);
		curl_close($curl);
		$authObj = json_decode($json_response);
		/*
		//if offline access requested and granted, get refresh token
		if (isset($authObj->refresh_token)){
			$this->refresh_token = $authObj->refresh_token;
		}elseif($authObj->error)
		{
			$this->resetTheCookie();
		}
		*/
		$accessToken = $authObj->access_token;
		return $accessToken;
	}
	
}
