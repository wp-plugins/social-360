<?php
class SocialAdminClass {
	/*************************************************
	Admin Settings For voter plugin menu function
	*************************************************/
	function aheadzen_voter_admin_menu()
	{
		add_submenu_page('options-general.php', 'Social 360 Options', 'Social 360', 'manage_options', 'social_contacts',array('SocialAdminClass','aheadzen_voter_settings_page'));
	}

	/*************************************************
	Admin Settings For voter plugin
	*************************************************/
	function aheadzen_voter_settings_page()
	{
		global $bp,$post;	
		if($_POST)
		{
			update_option('aheadzen_google_social_module',$_POST['google_social_module']);
			update_option('aheadzen_google_client_secret',trim($_POST['google_client_secret']));
			update_option('aheadzen_google_client_id',trim($_POST['google_client_id']));
			update_option('aheadzen_google_max_contacts',trim($_POST['google_max_contacts']));
			update_option('aheadzen_google_store_db',trim($_POST['google_store_db']));
			update_option('aheadzen_invitation_subject',trim($_POST['invitation_subject']));
			update_option('aheadzen_invitation_message',trim($_POST['invitation_message']));
			update_option('aheadzen_fb_api_id',trim($_POST['fb_api_id']));
			update_option('aheadzen_fb_api_secret',trim($_POST['fb_api_secret']));
			update_option('aheadzen_twitter_api_id',trim($_POST['twitter_api_id']));
			update_option('aheadzen_twitter_api_secret',trim($_POST['twitter_api_secret']));
			update_option('aheadzen_twitter_invitation_message',trim($_POST['twitter_invitation_message']));
			echo '<script>window.location.href="'.admin_url().'options-general.php?page=social_contacts&msg=success";</script>';
			exit;
		}

		?>
		<h2><?php _e('Social 360 Settings','aheadzen');?></h2>
		<?php
		if($_GET['msg']=='success'){
			echo '<p class="success">'.__('Your settings updated successfully.','aheadzen').'</p>';
		}
		?>
		<style>.success{padding:10px; border:solid 1px green; width:70%; color:green;font-weight:bold;}
		.text{width:80%;}
		</style>
		<form method="post" action="<?php echo admin_url();?>options-general.php?page=social_contacts">
			<table class="form-table">
				<tr><td><h3><?php _e('Google Settings','aheadzen');?></h3></td></tr>
				<tr valign="top">
					<td>
					<?php $google_client_id = get_option('aheadzen_google_client_id');?>
					<label for="google_client_id">
					<p><?php _e('Client ID','aheadzen');?> ::<br />
					<input class="text" type="text" id="google_client_id" name="google_client_id" value="<?php echo $google_client_id;?>" />
					<br />eg:  xxxxxxxxxx-3p7qxxxxxxxxxxxxxxn7kvn73xxxxxxxxxxiq8.apps.googleusercontent.com
					</p>
					</label>
					</td>
				</tr>
				<tr valign="top">
					<td>
					<?php $google_client_secret = get_option('aheadzen_google_client_secret');?>
					<label for="google_client_secret">
					<p><?php _e('Client Secret','aheadzen');?> ::<br />
					<input class="text" type="text" id="google_client_secret" name="google_client_secret" value="<?php echo $google_client_secret;?>" />
					<br />eg: ypCIBxxxxxxxxxxxxxxxxxxxx-cXt 
					</p>
					</label>
					</td>
				</tr>
				<tr valign="top">
					<td>
					<?php $google_max_contacts = get_option('aheadzen_google_max_contacts');?>
					<label for="google_max_contacts">
					<p><?php _e('Maximum number of contact lists to be display','aheadzen');?> ::<br />
					<input class="text" type="text" id="google_max_contacts" name="google_max_contacts" value="<?php echo $google_max_contacts;?>" />
					<br />default is : 200 
					</p>
					</label>
					</td>
				</tr>
				<tr valign="top">
					<td>
					<label for="google_store_db">
					<input type="checkbox" value="1" id="google_store_db" name="google_store_db" <?php if(get_option('aheadzen_google_store_db')){echo "checked=checked";}?>/>&nbsp;&nbsp;&nbsp;<?php _e('Store contact details to database?','aheadzen');?>
					</label>
					</td>
				</tr>
				<tr><td><?php _e('<b>NOTE:-</b> Get the document guide about to generate google Client ID and Secret Key, <a href="https://wp.timersys.com/wordpress-social-invitations/docs/configuration/#google" target="_blank">By Click Here</a>','aheadzen');?>
				<br /><?php _e('The "Redirect URIs" should be like: http://YOURSITENAME.COM/<u>?hauth.done=Google</u>','aheadzen');?>
				</td></tr>
				
				<tr><td><h3><?php _e('Default HTML Message for emails','aheadzen');?></h3>
				<br /><b style="color:brown;"><?php _e('Email details for GOOGLE & EMAIL Invitation settings.','aheadzen');?></b></td></tr>
				<tr valign="top">
					<td>
					<?php $aheadzen_invitation_subject = get_option('aheadzen_invitation_subject');
					if(!$aheadzen_invitation_subject){
						$aheadzen_invitation_subject = sprintf(__('I invite you to join %s','aheadzen'),get_bloginfo());
					}
					?>
					<label for="invitation_subject">
					<p><?php _e('Subject','aheadzen');?> ::<br />
					<input class="text" type="text" id="invitation_subject" name="invitation_subject" value="<?php echo $aheadzen_invitation_subject;?>" />
					<br />Default Subject for invitations 
					</p>
					</label>
					</td>
				</tr>
				
				<tr valign="top">
					<td>
					<?php $aheadzen_invitation_message = get_option('aheadzen_invitation_message');
					if(!$aheadzen_invitation_message){
						$aheadzen_invitation_message = __('<h3>%%INVITERNAME%% just invited you!</h3>%%INVITERNAME%% would like you to join %%SITENAME%%.','aheadzen');
					}
					?>
					<label for="invitation_message">
					<p><?php _e('HTML Message','aheadzen');?> ::<br />
					<textarea style="width:80%;height:200px;" id="invitation_message" name="invitation_message"><?php echo $aheadzen_invitation_message;?></textarea>
					<br />Default Message for HTML Invitations.
					</p>
					</label>
					<br /><br />
					<div style="background-color:#E8DCDC; padding:7px 20px; width:80%;">
					
					<?php _e('By default your users will be able to edit the default invitation message. Here you will be able to change the default message and forbid users to change it. <br /><br />Default messages are divided in several sections. Message for HTML providers, message for non HTML providers, message for twitter, non enditable section and footer.<br /><br />You can use the following placeholders on your message:','aheadzen');?>
					<br /><br />
					<ul>
					<?php global $social_plugin_replace_constants;
					foreach($social_plugin_replace_constants as $key=>$val)
					{
						echo '<li><b>'.$key.'</b> : '.$val.'</li>';
					}
					?>
					</ul>
					</div>
					</td>
				</tr>
				
				<tr><td><h3><?php _e('Facebook Settings','aheadzen');?></h3></td></tr>
				<tr valign="top">
					<td>
					<?php $fb_api_id = get_option('aheadzen_fb_api_id');?>
					<label for="fb_api_id">
					<p><?php _e('API ID','aheadzen');?> ::<br />
					<input class="text" type="text" id="fb_api_id" name="fb_api_id" value="<?php echo $fb_api_id;?>" />
					<br />eg:  499XXXXXXXXXX992
					</p>
					</label>
					</td>
				</tr>
				<tr valign="top">
					<td>
					<?php $fb_api_secret = get_option('aheadzen_fb_api_secret');?>
					<label for="fb_api_secret">
					<p><?php _e('App Secret','aheadzen');?> ::<br />
					<input class="text" type="text" id="fb_api_secret" name="fb_api_secret" value="<?php echo $fb_api_secret;?>" />
					<br />eg:  533XXXXXXXXXXXXXX95d8XXXXXXX26
					</p>
					</label>
					</td>
				</tr>
				<tr><td><?php _e('<b>NOTE:-</b> Get the document guide about to generate API ID, <a href="https://wp.timersys.com/wordpress-social-invitations/docs/configuration/#facebook" target="_blank">By Click Here</a>','aheadzen');?>
				</td></tr>
				
				<tr><td><h3><?php _e('Twitter Settings','aheadzen');?></h3></td></tr>
				<tr valign="top">
					<td>
					<?php $twitter_api_id = get_option('aheadzen_twitter_api_id');?>
					<label for="twitter_api_id">
					<p><?php _e('Consumer Key (API Key)','aheadzen');?> ::<br />
					<input class="text" type="text" id="twitter_api_id" name="twitter_api_id" value="<?php echo $twitter_api_id;?>" />
					<br />eg:  5TxxxxxxxxxxxxxxxxxxT4hR
					</p>
					</label>
					</td>
				</tr>
				<tr valign="top">
					<td>
					<?php $twitter_api_secret = get_option('aheadzen_twitter_api_secret');?>
					<label for="twitter_api_secret">
					<p><?php _e('Consumer Secret (API Secret)','aheadzen');?> ::<br />
					<input class="text" type="text" id="twitter_api_secret" name="twitter_api_secret" value="<?php echo $twitter_api_secret;?>" />
					<br />eg:  a64uXXXXXXXXXXXXXXXXXXXXSQYFJRxxxxxxxxxxxxxxxmFr 
					</p>
					</label>
					</td>
				</tr>
				<tr valign="top">
					<td>
					<?php $twitter_invitation_message = get_option('aheadzen_twitter_invitation_message');
					if(!$twitter_invitation_message){
						$twitter_invitation_message = __('<h3>%%INVITERNAME%% just invited you!</h3>%%INVITERNAME%% would like you to join %%SITENAME%%.','aheadzen');
					}
					?>
					<label for="twitter_invitation_message">
					<p><?php _e('Message','aheadzen');?> ::<br />
					<textarea style="width:80%;height:100px;" id="twitter_invitation_message" name="twitter_invitation_message"><?php echo $twitter_invitation_message;?></textarea>
					<br />Default Message for twitter to send message.
					</p>
					</label>
					<br /><br />
					<div style="background-color:#E8DCDC; padding:7px 20px; width:80%;">
					
					<?php _e('By default your users will be able to edit the default invitation message. Here you will be able to change the default message and forbid users to change it. <br /><br />Default messages are divided in several sections. Message for HTML providers, message for non HTML providers, message for twitter, non enditable section and footer.<br /><br />You can use the following placeholders on your message:','aheadzen');?>
					<br /><br />
					<ul>
					<?php global $social_plugin_replace_constants;
					foreach($social_plugin_replace_constants as $key=>$val)
					{
						echo '<li><b>'.$key.'</b> : '.$val.'</li>';
					}
					?>
					</ul>
					</div>
					</td>
				</tr>
				<tr valign="top">
					<td>
						<input type="hidden" name="page_options" value="<?php echo $value;?>" />
						<input type="hidden" name="action" value="update" />
						<input type="submit" value="Save settings" class="button-primary"/>
					</td>
				</tr>					
			</table>
		</form>
		<?php
		// Check that the user is allowed to update options  
		if (!current_user_can('manage_options'))
		{
			wp_die('You do not have sufficient permissions to access this page.');
		}
	}
}