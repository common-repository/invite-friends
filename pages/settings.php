<?php

if(!empty($_POST))
{
  if (!empty($_POST['if_invitation_cards']) && is_numeric($_POST['if_invitation_cards']))
  {
     update_option('if_invitation_cards',intval($_POST['if_invitation_cards'])); 
  }
  
  if (!empty($_POST['if_activate_redirect']))
  {
     update_option('if_activate_redirect',$_POST['if_activate_redirect']); 
  }
  
}

$if_invitation_cards = get_option("if_invitation_cards");

if (intval($if_invitation_cards) < 1)
{
  $if_invitation_cards = 5;
}

$if_activate_redirect = get_option("if_activate_redirect");
if (empty($if_activate_redirect))
{
  $if_activate_redirect = get_option('siteurl')."/wp-admin/profile.php";
}

?>


<div class="wrap">
<form method="POST" action="" name="form_if_settings" >
  <h2><?php _e('Invite Friends Settings', 'invite-friends'); ?></h2>
  <p>
    <?php _e('Number of invitation cards', 'invite-friends'); ?><br />
    <input type="text" style="width: 1.5em;" size="2" value="<?php echo $if_invitation_cards; ?>" id="if_invitation_cards" name="if_invitation_cards"/><br />
    <?php _e('If you want no limit, please enter "-1"', 'invite-friends'); ?><br /><br />
    
    <?php _e('Redirect after activate', 'invite-friends'); ?><br />
    <input type="text" style="width: 50em;" size="2" value="<?php echo $if_activate_redirect; ?>" id="if_activate_redirect" name="if_activate_redirect"/><br /><br /><br />
    <br />
    <input type="submit" name="if_invite_submit" value=" <?php _e('Save Changes'); ?>" class="button">
  </p>

  </form>
</div>