<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<title><?php bloginfo('name'); ?> &rsaquo; <?php echo $title; ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<?php
	wp_admin_css( 'css/login' );
	wp_admin_css( 'css/colors-fresh' );	
	?>
	<script type="text/javascript">
		function focusit() {
			document.getElementById('if_user_login').focus();
		}
		window.onload = focusit;
	</script>
<?php do_action('login_head'); ?>
</head>
<body class="login">
  <div id="login"><h1><a href="<?php echo apply_filters('login_headerurl', 'http://wordpress.org/'); ?>" title="<?php echo apply_filters('login_headertitle', __('Powered by WordPress')); ?>"><?php bloginfo('name'); ?></a></h1>
    <?php if ($valid_key): ?>
    <p class="message">
      <?php _e("To activate your account, please enter a username, and a password twice.", 'invite-friends'); ?><br /><br />
      <?php _e("This username only used in the login procedure, and it <strong>can't be changed</strong>. But, you can set a display name, that would be assigned to your activities (ie. comments), and it can be changed anytime.", 'invite-friends'); ?><br /><br />
      <?php _e("Please notice, that the username can only contain alphanumeric characters, and numbers. Every non-latin character will be stripped out, and every upper case character will be converted to lowercase.", 'invite-friends'); ?><br />
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
    <form name="if_choose_login_form" id="if_choose_login_form" action="" method="post">
    	<p>
    		<label><?php _e('Username:') ?><br />
    		<input type="text" name="if_user_login" id="if_user_login" class="input" value="<?php echo attribute_escape(stripslashes($_POST['if_user_login'])); ?>" size="20" tabindex="10" />
        </label>
    	</p>
      <p>
      <label for="pass1"><?php _e('New Password:'); ?></label><br />
	     <input type="password" name="pass1" id="pass1" size="16" value="" /><br />
		   <input type="password" name="pass2" id="pass2" size="16" value="" /><br />
       <?php _e("Type your new password again."); ?><br />
		  </p>    	
    	
    	<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Signup'); ?>" tabindex="100" /></p>
    </form>
    <?php else: ?>
    <p class="message">
      <?php _e("Your activation key seems invalid.", 'invite-friends'); ?><br /><br />
      <?php _e("It means, that you either already registered, or the person who sent you the invitation canceled it.", 'invite-friends'); ?><br /><br />
    </p>    
    <?php endif; ?>
  </div>
<p id="backtoblog"><a href="<?php bloginfo('url'); ?>/" title="<?php _e('Are you lost?') ?>"><?php printf(__('&laquo; Back to %s'), get_bloginfo('title', 'display' )); ?></a></p>
</body>
</html>