<?php
class AheadzenEmail
{
	/**
	* IDp wrappers initializer 
	*/
	function __construct()
	{
	
	}
	
	/**
	* load the user (Gmail and google plus) contacts 
	*  ..toComplete
	*/
	function sendUserEmailInvitation()
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
		.send_invite_list.to_face{margin-top:10px; text-align:right;}
		ul.invite_to_div{  list-style: outside none none;margin: 0 0 10px;padding: 0;}
		ul.invite_to_div li{display: inline-block;margin:2px;padding:2px 3px;background-color: #c5eefa;border:1px solid #5dc8f7;color: #4594b5;border-radius: 3px; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075) inset;vertical-align: middle;}
		ul.invite_to_div li a{color: #000000;font-weight: bold;margin-left: 4px;opacity: 0.2;padding: 0 2px;cursor: pointer;}
		</style>
		</head>
		<body>
		<form name="gml_send_invitation" action="" method="POST">
		<input type="hidden" name="eml_send_invitations" value="sendinvite" />
		<center><h2>Select Your Friends</h2></center>
		<div id="agoogle_users">
		
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
		<div class="send_invite_list" style="text-align: right">
		<input type="submit" id="submit-button" value="SEND">
		</div>
		</form>
		</body>
		</html>
		<?php		
		exit;
		return $contacts;
 	}

}
