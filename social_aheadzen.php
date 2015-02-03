<?php
/*
Plugin Name: Social 360 Plugin
Plugin URI: http://aheadzen.com/
Description: The plugin add social invitation link by Shotcode like [SociaPlugin google=1]
Author: Aheadzen Team  | <a href="options-general.php?page=social_contacts" target="_blank">Plugin Settings</a>
Version: 1.0.1
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
include_once('send_email.php');
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
	wp_enqueue_style('social-360', plugins_url( 'css/style.css', __FILE__ ) );
}

function aheadzen_social_template_include($template)
{
	$google = new AheadzenGoogle();
	$fb = new AheadzenFacebook();
	$eml = new AheadzenEmail();	
	
	if($_POST && $_POST['eml_send_invitations'] && $_POST['invite_emls'])
	{
		$emls_arr = explode(',',$_POST['invite_emls']);
		$validate_eml_arr = array();
		if($emls_arr)
		{
			for($e=0;$e<count($emls_arr);$e++)
			{
				$the_eml = trim($emls_arr[$e]);
				if (filter_var($the_eml, FILTER_VALIDATE_EMAIL))
				{
					$validate_eml_arr[] = $the_eml;
				}				
			}
			if($validate_eml_arr)
			{
				$args = array();
				$args['check'] = $validate_eml_arr;
				$args['subject'] = $_POST['subject'];
				$args['message'] = $_POST['message'];
				aheadzen_invitor_send_email($args);
				AheadDB::insert_social_contact_eml($validate_eml_arr); //insert data in to db
				
			}
			
		}
		echo '<h2>'.__('Invitations sent successfully...','aheadzen').'</h2><br /><br />';
		echo '<script>
		setTimeout(function(){ window.close(); }, 3000);
		</script>';
		exit;
		exit;
	}
	elseif($_POST && $_POST['google_send_invitations'] && $_POST['invitor_name'] && ($_POST['check'] || $_POST['invite_emls']))
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
		}elseif($_GET['get_contact']=='email'){
			$eml->sendUserEmailInvitation();exit;
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
	global $social_plugin_dir_url;
?>
	<?php /*?>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
	<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
	<script>
	var the_iframe_url = '';
	jQuery(function() {
	jQuery( "#social_360_dialog" ).dialog({
		autoOpen: false,
		modal: true,
		open: function(ev, ui){
		  jQuery('#social_360Iframe').attr('src',the_iframe_url);
		 },
		show: {
		effect: "blind",
		duration: 1000
		},
		hide: {
		effect: "explode",
		duration: 1000
		}
	});
	jQuery( ".popupwindow" ).click(function(e) {
		the_iframe_url = jQuery(this).attr("href");
		jQuery( "#social_360_dialog" ).dialog( "open" );
		return false;
		});
	});
	</script>
	<?php */?>
	<script type="text/javascript">
	var profiles =
	{
		windowCallUnload:
		{
			height:620,
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

	<style>
	ul.social_360_front{text-align: center;}
	ul.social_360_front li{list-style:none; margin:0 5px;display: inline-block;}
	ul.social_360_front li a{color: #FFF;font-size: 44px;text-decoration: none;z-index: 2;display:block;padding:10px 15px;}
	.social_360_front .socialfb{background-color: #3b5998;}
	.social_360_front .socialgp {background-color: #ca3f2f;}
	.social_360_front .socialtwitter {background-color: #7cd2f2;}
	.social_360_front .socialeml {background-color: #744848;}
	</style>
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
	$email = intval($atts['email']);
	
	$content = '<ul class="social_360_front">';
	if($google){
		$content .= '<li><a href="'.site_url('/?get_contact=google').'" class="popupwindow socialgp" rel="windowCallUnload"><i class="wsiicon-google"></i></a></li>';
	}
	if($facebook)
	{
		$fb = new AheadzenFacebook();
		$inviter_url = $fb->inviter_url();
		$content .= '<li><a href="'.$inviter_url.'" class="popupwindow socialfb" rel="windowCallUnload"><i class="wsiicon-facebook"></i></a></li>';
	}
	if($twitter)
	{
		$content .= '<li><a href="'.site_url('/?get_contact=twitter').'" class="popupwindow socialtwitter" rel="windowCallUnload"><i class="wsiicon-twitter"></i></a></li>';
	}
	if($email)
	{
		$content .= '<li><a href="'.site_url('/?get_contact=email').'" class="popupwindow socialeml" rel="windowCallUnload"><i class="wsiicon-mail"></i></a></li>';
	}
	$content .= '</ul>';
	
	return $content;
}