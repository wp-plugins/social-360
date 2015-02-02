<?php
/*
Plugin Name: Social 360 Plugin
Plugin URI: http://aheadzen.com/
Description: The plugin add social invitation link by Shotcode like [SociaPlugin google=1]
Author: Aheadzen Team  | <a href="options-general.php?page=social_contacts" target="_blank">Plugin Settings</a>
Version: 1.0.0
Author URI: http://aheadzen.com/

License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
session_start();
set_time_limit(0);
$social_plugin_dir_path = dirname(__FILE__);
$social_plugin_dir_url = plugins_url('', __FILE__);

update_option('aheadzen_twitter_consumer_key','5TEPiMqGtcsfIC8woOnhcT4hR');
update_option('aheadzen_twitter_consumer_secret','a64uUo4IHVmT07u7nf1IJA4P7K75tSQYFJRehmfklmdx0XemFr');

$social_plugin_replace_constants = array(
			'%%INVITERNAME%%' 	=> __('Display name of the inviter','aheadzen'),
			'%%SITENAME%%' 		=> __('Name of your website','aheadzen'),
			//'%%SITENAMEWITHURL%%'=> __('Name of your website with home page link','aheadzen'),
			//'%%ACCEPTURL%%' 	=> __('Link that invited users can click to accept the invitation and register','aheadzen'),
			//'%%CURRENTURL%%' 	=> __('Prints the url where the widget was clicked','aheadzen'),
			//'%%CURRENTTITLE%%' 	=> __('Title of the post / page where the widget was clicked','aheadzen'),
		);
		
$social_replace_constants_key = array('%%INVITERNAME%%','%%SITENAME%%');

include_once('db.php');
include_once('gmail.php');
include_once('facebook.php');
include_once('twitter/twitter.php');

if(is_admin())
{
	include_once('admin_settings.php');
	add_action('admin_menu',array('SocialAdminClass','aheadzen_voter_admin_menu'));
}
add_action('init','aheadzen_social_init_functions');
add_action('wp_head','aheadzen_social_head_functions');
add_action( 'wp_enqueue_scripts', 'aheadzen_scripts_method' );
add_shortcode('SociaPlugin', 'aheadzen_social_plugin_shortcode');
add_filter('template_include','aheadzen_social_template_include');

function aheadzen_scripts_method() {
	global $social_plugin_dir_url;
	wp_enqueue_script('popupwindow',$social_plugin_dir_url.'/js/jquery.popupwindow.js',array( 'jquery' ));
	//wp_enqueue_script('jquerysearchlist',$social_plugin_dir_url.'/js/list.js',array( 'jquery' ));
}

function aheadzen_social_template_include($template)
{
	$google = new AheadzenGoogle();
	$fb = new AheadzenFacebook();
	
	if($_POST && $_POST['google_send_invitations'] && $_POST['invitor_name'] && ($_POST['check'] || $_POST['invite_emls']))
	{
		aheadzen_invitor_send_email($_POST);
		echo '<h2>'.__('Invitations sent successfully...','aheadzen').'</h2><br /><br />';
		echo '<script>
		setTimeout(function(){ window.close(); }, 3000);
		</script>';
		exit;
	}elseif($_GET['hauth_done']=='Google' && $_GET['code'])
	{
		$google->accessToken = $_GET['code'];
		$google->getUserContacts();exit;
	}elseif($_GET['hauth_done']=='Facebook' && $_GET['code'])
	{
		$fb->accessToken = $_GET['code'];
		$fb->getUserContacts();exit;
	}elseif($_GET['get_contact'])
	{
		if($_GET['get_contact']=='google')
		{
			$google->loginBegin();exit;
		}elseif($_GET['get_contact']=='facebook'){
			$fb->fb_friend_list();exit;
		}
	}	
	return $template;
}


function aheadzen_social_init_functions()
{
	
}

function aheadzen_invitor_send_email($args)
{
	$to_emails_arr = $args['check'];
	$from_name = $args['invitor_name'];
	$invite_emls = trim($args['invite_emls']);
	$subject = $args['subject'];
	$message = nl2br($args['message']);
	if(!$to_emails_arr){$to_emails_arr = array();}
	if($invite_emls){
		$invite_emls_arr = explode(',',$invite_emls);
		$to_emails_arr = array_merge($to_emails_arr,$invite_emls_arr);
	}
	
	$bloglinkwithurl = '<a href="'.site_url().'">'.get_bloginfo().'</a>';		
	global $social_replace_constants_key;
	$srch_arr = $social_replace_constants_key;
	$rpl_arr = array($invitor_profile_name,get_bloginfo(),$bloglinkwithurl);
	$subject = str_replace($srch_arr,$rpl_arr,$subject);
	$message = str_replace($srch_arr,$rpl_arr,$message);

	$from_name =  get_option('blogname');
	$from_email = get_option('admin_email');
	
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type: text/html; charset=".get_bloginfo('charset')."" . "\r\n";
	$headers .= "From: $from_name <$from_email>" . "\r\n";
	
	for($i=0;$i<count($to_emails_arr);$i++)
	{
		$to_email = trim($to_emails_arr[$i]);
		//echo "to : $to_email<br />, SUBJECT: $subject<br />, Message: $message<br />,Header : $headers";exit;
		//exit;
		wp_mail($to_email, $subject, $message, $headers);
	}	
}

function aheadzen_social_head_functions()
{
?>
	<script type="text/javascript">
	var profiles =
	{
		windowCallUnload:
		{
			height:650,
			width:650,
			center:1,
			scrollbars:1,
			resizable:1,
			onUnload:unloadcallback
		},
	};

	function unloadcallback(){alert("unloaded");};

   	jQuery(function(){
   		jQuery(".popupwindow").popupwindow(profiles);
   	});
	</script>
<?php
}


/*******************************
Shotcode :: [SociaPlugin google=1]
****************************/
function aheadzen_social_plugin_shortcode($atts) {
	$atts['shortcode']=1;
	$google = intval($atts['google']);
	$facebook = intval($atts['facebook']);
	$twitter = intval($atts['twitter']);
	$content = '<ul>';
	if($google){
		$content .= '<li><a href="'.site_url('/?get_contact=google').'" class="popupwindow" rel="windowCallUnload">Get Google Contacts</a></li>';
	}
	if($facebook)
	{
		$fb = new Facebook();
		$inviter_url = $fb->inviter_url();
		$content .= '<li><a href="'.$inviter_url.'" class="popupwindow" rel="windowCallUnload">Facebook Invite Friends</a></li>';
	}
	if($twitter)
	{
		$content .= '<li><a href="'.site_url('/?get_contact=twitter').'" class="popupwindow" rel="windowCallUnload">Twitter Followers</a></li>';
	}
	$content .= '</ul>';
	return $content;
}