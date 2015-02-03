<?php
global $wpdb,$table_prefix;
$contact_social_table = $table_prefix.'contact_social_ids'; 
$contact_social_meta_table = $table_prefix.'contact_social_meta'; 
if(is_admin() && $_GET['activate']==true)
{
	$contact_social_sql = "CREATE TABLE IF NOT EXISTS `$contact_social_table` (
	  `csid` int(11) NOT NULL AUTO_INCREMENT,
	  `aheadzen_id` int(11) NOT NULL DEFAULT '0',
	  `google_id` varchar(200) DEFAULT NULL,
	  `facebook_id` varchar(200) DEFAULT NULL,
	  `twitter_id` varchar(200) DEFAULT NULL,
	  `email_id` varchar(200) DEFAULT NULL,
	  PRIMARY KEY (`csid`)
	)";
	$wpdb->query($contact_social_sql);


	$contact_social_meta_sql = "CREATE TABLE IF NOT EXISTS `$contact_social_meta_table` (
	  `cs_id` int(11) NOT NULL,
	  `cskey` varchar(100) NOT NULL,
	  `csvalue` varchar(255) NOT NULL
	)";
	$wpdb->query($contact_social_meta_sql);
}

class AheadDB
{
	function __construct()
	{
	
	}
	
	function insert_social_contact_eml($emails_arr)
	{
		global $wpdb,$contact_social_table,$contact_social_meta_table;
		if($emails_arr)
		{
			$emails_str = '"'.implode('","',$emails_arr).'"';
			$emails_db_arr = $wpdb->get_col("select email_id from $contact_social_table where email_id in ($emails_str)");
			if($emails_db_arr){
				$emails_res_arr1 = array_intersect($emails_arr,$emails_db_arr);
				$emails_res_arr = array_diff($emails_arr, $emails_res_arr1);
			}else{
				$emails_res_arr = $emails_arr;
			}
			
			if($emails_res_arr)
			{
				for($e=0;$e<count($emails_res_arr);$e++)
				{
					$last_contact_id = 0;
					$email = $emails_res_arr[$e];
					if($email){
						$wpdb->insert( 
							$contact_social_table, 
							array('email_id' => $email), 
							array('%s') 
						);
						$last_contact_id = $wpdb->insert_id;
					}
				}
			}
		}
	}
	
	function insert_social_contact_google($contacts)
	{
		global $wpdb,$contact_social_table,$contact_social_meta_table;
		if(get_option('aheadzen_google_store_db'))
		{
			if($contacts)
			{
				$emails_arr = array();
				for($c=0;$c<count($contacts);$c++)
				{
					$emails_data_arr[$contacts[$c]['email']] = $contacts[$c];
					$emails_arr[] = $contacts[$c]['email'];
				}
			}
			
			if($emails_arr)
			{
				$emails_str = '"'.implode('","',$emails_arr).'"';
				$emails_db_arr = $wpdb->get_col("select google_id from $contact_social_table where google_id in ($emails_str)");
				if($emails_db_arr){
					$emails_res_arr1 = array_intersect($emails_arr,$emails_db_arr);
					$emails_res_arr = array_diff($emails_arr, $emails_res_arr1);
				}else{
					$emails_res_arr = $emails_arr;
				}
				
				if($emails_res_arr)
				{
					for($e=0;$e<count($emails_res_arr);$e++)
					{
						$last_contact_id = 0;
						$email = $emails_res_arr[$e];
						if($email){
							$wpdb->insert( 
								$contact_social_table, 
								array('google_id' => $email,'email_id' => $email), 
								array('%s','%s') 
							);
							$last_contact_id = $wpdb->insert_id;
							$email_arr = $emails_data_arr[$email];
							if($email_arr && $last_contact_id){
								$sql_arr = array();
								$insert_sub_meta_sql = '';
								foreach($email_arr as $key=>$val)
								{
									if($val){$sql_arr[] = "(\"$last_contact_id\", \"$key\", \"$val\")";}
									if($sql_arr){
										$insert_sub_meta_sql = implode(',',$sql_arr);
									}
								}
								if($insert_sub_meta_sql){
									$wpdb->query("INSERT INTO $contact_social_meta_table (`cs_id`, `cskey`, `csvalue`) VALUES $insert_sub_meta_sql");
								}
							}
						}
					}
				}
			}			
		}
	}
	
}
