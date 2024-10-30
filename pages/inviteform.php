<?php
global $table_prefix, $wpdb;

$current_user = wp_get_current_user(); 

$email_rule = '/^([a-z0-9][a-z0-9_\-\.\+]*)@([a-z0-9][a-z0-9\.\-]{0,63}\.(com|org|net|biz|info|name|net|pro|aero|coop|asia|mobi|museum|[a-z]{2,4})$)/i';
$name_rule = '/^([a-z0-9]{1,})$/i';
$invalid_msg_email = __('<strong>ERROR</strong>: The email address isn&#8217;t correct.');
$invalid_msg_name = __("<strong>ERROR</strong>: Please enter your friend's name.", 'invite-friends'); 

$if_invitation_cards = get_option("if_invitation_cards"); 
$invited = $wpdb->get_row("SELECT COUNT(*) as total FROM $wpdb->usermeta WHERE meta_key = 'invited_by' AND meta_value = '".$current_user->ID."'");

if ($if_invitation_cards == -1) $remain_cards = 0;
else $remain_cards = $if_invitation_cards - $invited->total;

$errors = new WP_Error();

//var_dump(preg_match($email_rule,"djz@rain.com"));

?>
<script type="text/javascript">
var emailRule = "<?php echo $email_rule; ?>";
var nameRule = "<?php echo $name_rule; ?>";

function If_Check_Email()
{
  var emailAddress = document.getElementById('if_friend_email').value;
  var friendName = document.getElementById('if_friend_name').value;
  
  res_email = emailRule.test(emailAddress);
  
  if (res_email === false)
  {
    document.getElementById('if_message').innerHTML="<?php echo $invalid_msg_email; ?>";
  }
  
  res_name = nameRule.test(friendName);
  if (res_name === false)
  {
    document.getElementById('if_message').innerHTML="<?php echo $invalid_msg_name; ?>";
  }
  
  if (res_email === false || res_name === false) return false;
  else return true;
}
</script>
<?php

//die;

if(!empty($_POST) && !empty($_POST['if_friend_email']))
{
  $if_friend_email = $_POST['if_friend_email'];
  $if_friend_name = addslashes($_POST["if_friend_name"]);
  /**
  * Later on $if_friend_email is set once more, but if an error appears (duplicated e-mail)
  * the input form will not display the given data;
  * 
  * I use addslashes to avoid XSS.
  * I don't use it in the email field, because it has been checked via regexp.
  * if it does not match the pattern it has been slashed in the else part.        
  */    
  
  if(preg_match($email_rule,$if_friend_email))
  {
    $check_result = $wpdb->get_row("SELECT COUNT(*) as total FROM $wpdb->users WHERE user_email = '$if_friend_email'");  
    
    $already_in_db = $check_result->total;
    
    //var_dump($invited->total);
    //var_dump($if_invitation_cards);
    
    if($already_in_db == 0)
    {
      if ($if_invitation_cards == "-1" || $remain_cards > 0)
      /**
       * The user can invite, if invitation is unlimited
       * or he/she has more than 1 invitation card     
       */         
      {
        $displayname = '';
        $nickname = '';
        $first_name = '';
        $last_name = '';
                
        if(!empty($_POST["if_friend_name"]))
        {
          $if_friend_name = $_POST["if_friend_name"]; 
          $pos = strpos($if_friend_name," ");
          
          if ($pos !== false)
          {
            $first_name = mysql_real_escape_string(substr($if_friend_name,0,$pos));
            $last_name = mysql_real_escape_string(substr($if_friend_name,$pos+1));
            
            $displayname = mysql_real_escape_string($if_friend_name);
            $nickname = $first_name; 
          }
        }
                
      $user_email = $if_friend_email;
    	$data = array("user_email"=>$user_email,'first_name'=>$first_name,'last_name'=>$last_name,'display_name'=>$displayname,'nickname'=>$nickname);
    	$user_id = wp_insert_user($data);
      
    	if ( !$user_id ) 
      {
    		echo sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !'), get_option('admin_email'));
    		die;
    	}
    	else
    	{
    	  $key = substr(md5(wp_generate_password()),9,10);
    	  $wpdb->query("UPDATE $wpdb->users SET user_activation_key = '$key',user_pass='' WHERE ID = '$user_id'");
    	  
        update_usermeta( $user_id, 'invited_by', $current_user->ID);
        
        $name = get_usermeta($current_user->ID,'first_name');
        if (!$name) 
        {
        		$name = get_usermeta($current_user->ID,'nickname');
        } 
        
      	$message .= sprintf(__("Dear %s,\r\nYour friend %s invited you to join the %s user community.", 'invite-friends'), $if_friend_name,$name,get_option('blogname')) . "\r\n\r\n";
      	$message .= __("To accept this invitation please click the link below, and pick a username:", 'invite-friends') . "\r\n\r\n";
      	$message .= get_option('siteurl') . "/index.php?if_activation_code=$key\r\n";
      
        $headers = "From: ". $name ." <" . $current_user->user_email . ">\r\n";
      
      	if ( !wp_mail($user_email, sprintf(__("I've invited you to join me at %s"), get_option('blogname')), $message, $headers) )
      	{
          die('<p>' . __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') . '</p>');
      	}
          
        $errors->add('sent',__('Invitation card has been sent to your friend','invite-friends'));
        $if_friend_name = '';
        $if_friend_email = '';
      }
  
  	   wp_cache_delete($user_id, 'users');
  	   wp_cache_delete($user_login, 'userlogins');
           
      }
      else
      {
        $errors->add('noinvitationcard',__('<strong>ERROR</strong>: You ran out of invitation cards','invite-friends'));
      }      
    }
    else
    {
		  $errors->add('email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.'));
    }   
  }
  else
  {
    $if_friend_email = addslashes($_POST['if_friend_email']);
    $errors->add('invalidemail', $invalid_msg_email);
  }
}
?>
<div class="wrap">
<p>
<?php 

if ($if_invitation_cards != '-1')
{
  if ($remain_cards < 1) _e('You ran out of invitation cards!', 'invite-friends');
  elseif ($remain_cards == 1) _e('You have one more invitation card left.','invite-friends');
  else echo sprintf(__('You have <strong>%s</strong> invitation cards left.','invite-friends'), $remain_cards);
}
else
{
  _e('You can invite as many friends as you want!','invite-friends');
}
?>
</p>
<?php if(!empty($errors) && !empty($errors->errors)): ?>
<p class="message">
<?php
    foreach ( $errors->get_error_messages() as $error ) 
    {
        echo $error . "<br />\n";
		}
?>
</p>
<?php endif; ?>
<form method="POST" action="" name="if_invitation_form" onsubmit="return checkEmail();" >
  <h2><?php _e('Invite Friends', 'invite-friends'); ?></h2>
  <p>
    <p class="message" id="if_message">
    <?php if($invalid_email === true): ?>
      <?php echo $msg; ?>
    <?php endif; ?>
    </p>
    <?php _e('Friend name', 'invite-friends'); ?><br />
    <input type="text" name="if_friend_name" id="if_friend_name" value="<?php echo $if_friend_name; ?>" ><br />
    <?php _e('Friend e-mail', 'invite-friends'); ?><br />
    <input type="text" name="if_friend_email" id="if_friend_email" value="<?php echo $if_friend_email; ?>" ><br />
    <input type="submit" name="if_invite_submit" onclick="return If_Check_Email();" value=" <?php _e('Send invitation!', 'invite-friends'); ?>" class="button">
  </p>

  </form>
</div>