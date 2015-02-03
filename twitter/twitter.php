<?php
set_time_limit(0);
ini_set('max_execution_time',0);
$twitter_api_id = get_option('aheadzen_twitter_api_id');
$twitter_api_secret = get_option('aheadzen_twitter_api_secret');
define('CONSUMER_KEY', $twitter_api_id);
define('CONSUMER_SECRET', $twitter_api_secret);

//error_reporting(E_ERROR);
//ini_set("display_errors", 1);

$consumerKey    = CONSUMER_KEY;
$consumerSecret = CONSUMER_SECRET;
$oAuthToken     = 'OAuthToken';
$oAuthSecret    = 'OAuth Secret';

require "autoloader.php";

use Abraham\TwitterOAuth\TwitterOAuth;

function aheadzen_twitter_init()
{
	
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);	
	# your code to retrieve data goes here, you can fetch your data from a rss feed or database
	//$statues = $connection->get("statuses/home_timeline", array("count" => 25, "exclude_replies" => true));	
	$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => site_url('/?hauth_done=twitter')));	
	$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));	
	$_SESSION['request_token'] = $request_token;	
	header("Location: $url");exit;
}

function aheadzen_twitter_followers()
{
	$request_token = $_SESSION['request_token'];
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);	
	$access_token_arr = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));
	$access_token = $access_token_arr['oauth_token'];
	$access_token_secret = $access_token_arr['oauth_token_secret'];	
	$user_id = $access_token_arr['user_id'];
	$_SESSION['access_token'] = $access_token;
	$_SESSION['access_token_secret'] = $access_token_secret;
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token, $access_token_secret);
	$followers = $connection->get('followers/list');
	$followers=$followers->users;
	if($followers){
	
	$content = $connection->get('account/verify_credentials'); //owner details
	$invitor_profile_name = $content->name.'('.$content->screen_name.')';
	
	$aheadzen_invitation_message = strip_tags(get_option('aheadzen_twitter_invitation_message'));
	$bloglinkwithurl = '<a href="'.site_url().'">'.get_bloginfo().'</a>';		
	global $social_replace_constants_key;
	$srch_arr = $social_replace_constants_key;
	$rpl_arr = array($invitor_profile_name,get_bloginfo(),$bloglinkwithurl);
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
	ul.social_listing li{vertical-align:top;clear:both;display:inline-block;height:32px;width:33%;border: 1px solid #d3edff;padding:2px 0;}
	ul.social_listing img{width:32px;height:32px;float:left;margin-right:5px;}
	ul.social_listing b{color:#424242;padding:5px 0;vertical-align:middle;font-weight:normal;}
	ul.social_listing span{color: #b3b3b3;display: block;font-size: 12px;font-style: normal;}
	#userlist{background:none repeat scroll 0 0 #fff;border:1px solid #cacaca;height:190px;overflow:auto;}
	.gcheckbox{float: left;margin-right:5px;}
	li label{width:98%;display: inline-block;}
	#submit-button {display: block;font-size: 26px;height: 40px;margin: 20px auto 0;padding: 0 22px;background-color: #21759b;background-image: linear-gradient(to bottom, #2a95c5, #21759b);border-color: #21759b #21759b #1e6a8d;border-radius: 3px;border-style: solid;border-width: 1px;box-shadow: 0 1px 0 rgba(120, 200, 230, 0.5) inset;box-sizing: border-box;color: #fff;cursor: pointer;display: inline-block;font-size: 12px;height: 24px;line-height: 23px;margin: 0;padding: 0 10px 1px;text-decoration: none;text-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);white-space: nowrap;}
	.text{width:99%;}
	.textarea{width:99%;height:100px;}
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
	<input type="hidden" name="twitter_send_invitations" value="sendinvite" />
	<input type="hidden" name="invitor_name" value="<?php echo $invitor_profile_name;?>" />
	<center><h2>Select Your Friends</h2></center>
	<div id="agoogle_users">
	<input class="search" placeholder="Search" />
	<input type="button" class="sort" data-sort="geml" value="Sort by e-mail">
	<label for="selecctall"><input type="checkbox" id="selecctall"/> Select All/None</label>
	<div id="userlist">

			<?php
			echo '<ul class="social_listing list">';
			foreach($followers as $follower)
			{
				$screen_name = $follower->screen_name;
				$name = $follower->name;
				$photo = $follower->profile_image_url;
				if(!$photo){$photo = $this->default_photo;}
				
				echo '<li><label for="'.$screen_name.'">';
				echo '<input id="'.$screen_name.'" class="gcheckbox" type="checkbox" name="check[]" value="'.$screen_name.'">';
				echo '<img src="'.$photo.'" alt="" />';
				echo '<b class="geml">'.$name.'</b>';
				echo '<span class="gdsname">'.$screen_name.'</span>';
				echo '</label></li>';
			}
			echo '</ul>';
			
			//AheadDB::insert_social_contact($contacts); //insert data in to db		
		

	global $social_plugin_dir_url;
	?>
	</div></div>
	<div class="send_invite_list to_face"><label>Sending Invitations To</label>
	<ul class="invite_to_div">
	<li><?php _e('Please select your contacts first.','aheadzen');?></li>
	</ul>
	</div>
	<div class="send_invite_list"><label>Message</label>
	<textarea class="textarea" name="message"><?php echo $aheadzen_invitation_message;?></textarea>
	<br /><small>Keep it under 140 characters.</small>
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
		/*echo '<pre>';
		print_r($follower->screen_name);exit;
		$screen_name = $follower->screen_name;
			$connection->post('direct_messages/new',array("user"=>$follower->screen_name,"text"=>$response));
		*/
	}else{
		echo '<center><h2>Cannot continue the process. <br /> Sorry for inconvenience.</h2></center>';
	}
	exit;
}


if($_POST && $_POST['twitter_send_invitations'])
{
	$to_arr = $_POST['check'];
	$from_name = $_POST['invitor_name'];
	$message = nl2br($_POST['message']);
	global $social_replace_constants_key;
	$srch_arr = $social_replace_constants_key;
	$rpl_arr = array($invitor_profile_name,get_bloginfo(),$bloglinkwithurl);
	$message = str_replace($srch_arr,$rpl_arr,$message);
	if(strlen($message)>140){$message=substr($message,0,139);}
	
	if(!$to_arr){$to_arr = array();}
	
	if($to_arr){
		$access_token = $_SESSION['access_token'];
		$access_token_secret = $_SESSION['access_token_secret'];	
		$user_id = $access_token_arr['user_id'];	
		$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token, $access_token_secret);
		for($i=0;$i<count($to_arr);$i++)
		{
			$screen_name = $to_arr[$i];
			$result = $connection->post('direct_messages/new',array("user"=>$screen_name,"text"=>$message));
		}
	}
	echo '<h2>'.__('Message sent successfully...','aheadzen').'</h2><br /><br />';
	echo '<script>
	setTimeout(function(){ window.close(); }, 3000);
	</script>';
	exit;
}
elseif($_GET['hauth_done'] && $_GET['oauth_token'] && $_GET['oauth_verifier'])
{
	aheadzen_twitter_followers();exit;
}elseif($_GET['get_contact']=='twitter'){
	aheadzen_twitter_init();exit;
}
/*
if(empty($_REQUEST['oauth_verifier']))
{
	aheadzen_twitter_init();
}
else {
	aheadzen_twitter_followers();
}
*/
//$access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => "nMznkpFRTMCuNMsmALzel9FgPlmWQDWg"));


//$url = $connection->url("oauth/authorize", array("oauth_token" => "EaQLH34YD8pgKkUiSp8RbjjOgNxIYVh7"));


//var_dump( $statues  );

//$tweet->post('statuses/update', array('status' => 'here the content of your tweet, you can add hashtags or links'));
?>
