<?php
/*
Plugin Name: Invite friends
Plugin URI: http://invite-friends.dev.rain.hu
Description: This Plugin adds friend invitation possibility to your blog
Version: 0.3
Author: DjZoNe
Author URI: http://djz.hu

	Copyright 2008  Zsolt Lakatos  (email : djz@djz.hu)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

load_plugin_textdomain('invite-friends', ABSPATH.'/wp-content/plugins/invite-friends/languages');

global $table_prefix, $wpdb;


function InviteFriendsInit() {
  add_menu_page(__('Invite friends', 'invite-friends'), __('Invite friends', 'invite-friends'),6,dirname(__FILE__).'/pages/inviteform.php');
  if (function_exists('add_submenu_page'))
  { 
    add_submenu_page('plugins.php', __('Invite friends settings', 'invite-friends'), __('Invite friends settings', 'invite-friends'), 'manage_options', dirname(__FILE__).'/pages/settings.php');
  }

}


### Add file_id to query vars
function InviteFriendsQueryVars($public_query_vars) {
	$public_query_vars[] = "if_activation_code";
	return $public_query_vars;
}


### Add rewrite role to .htaccess
function InviteFriendsRewriteRule() 
{ 
	add_rewrite_rule('invite-friends/([0-9]{1,})/?$', 'index.php?if_activation_code=$matches[1]');
}

### Function Activated when an user download a file
function InviteFriendsActivate() {
	global $wpdb, $user_ID, $table_prefix;
	$key = mysql_real_escape_string(get_query_var('if_activation_code'));
	
	$valid_key = false;
	
	if(!empty($key)) {
	
    $user = $wpdb->get_row("SELECT * FROM ".$wpdb->users." WHERE user_activation_key='$key'");    
    
    $errors = new WP_Error();
    
    if (!empty($user) && is_numeric($user->ID))
    {
      $valid_key = true;
    }
    
    if (empty($errors->errors) && !empty($_POST) && !empty($_POST['if_user_login']))
    {
      $user_login = sanitize_user( $_POST['if_user_login'] );
      
      if (strlen($user_login) > 4)
      {
        $login_check = $wpdb->get_row("SELECT COUNT(*) as total FROM ".$wpdb->users." WHERE user_login='".mysql_real_escape_string($user_login)."'");      
      }
      else
      {
        $errors->add('tooshort_login', __('<strong>ERROR</strong>: Username is too short, or contains illegal characters.','invite-friends'));      
      }

      if (empty($errors->errors))
      {
        if(!empty($login_check) && $login_check->total == "0")
        { 
          if (!empty($_POST['pass1']) &&  !empty($_POST['pass2']))
          {
            if ($_POST['pass1'] != $_POST['pass2'])
            {
              $errors->add('mismatch_password', __('<strong>ERROR</strong>: Please enter the same password to the fields.','invite-friends'));
            }
            else
            {
              $user_pass = wp_hash_password($_POST['pass1']);
              
              $data = array('user_pass'=>$user_pass,'user_login'=>$user_login,'user_nicename'=>$user_login,'display_name'=>$user_login,'user_activation_key'=>'');
              $where = array('ID'=>$user->ID);
              
              $ret = $wpdb->update($wpdb->users,$data,$where);
              
              if ($ret)
              {
                wp_new_user_notification($user->ID, $user_pass);
                
                unset($data['user_nicename']);
                unset($data['display_name']);
                unset($data['user_activation_key']);
                
                wp_signon($data);
                wp_safe_redirect(get_option('if_activate_redirect'));
                
              }
              else
              {
                wp_die(sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !'), get_option('admin_email')));
              }
            }
          }
          else
          {
            $errors->add('empty_password', __('<strong>ERROR</strong>: Please enter your new password to both fields.','invite-friends'));
          }
        }
        else
        {
          $errors->add('login_taken', __('<strong>ERROR</strong>: This username is already taken. Pick aother one.','invite-friends'));
        }
      }
    
      //var_dump($login_check);
      //die;
    }
    
/*    if(!$file) {
      header('HTTP/1.0 404 Not Found');
      header('Refresh: 4; URL='.$_SERVER['PHP_SELF']);
      die(__('Invalid File ID.', 'downloads-manager'));
    }*/
    
  require ABSPATH."/wp-content/plugins/invite-friends/pages/activate.php"; 
  die;
    
 } 
}

/**
 * Hooks
 */ 
add_action('admin_menu', 'InviteFriendsInit');
add_action('init', 'InviteFriendsRewriteRule');
 
//add_action('admin_menu', 'DownloadsManager_Init');
add_action('template_redirect', 'InviteFriendsActivate');
add_filter('query_vars', 'InviteFriendsQueryVars');


?>