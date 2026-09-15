<?php
$page_title = 'My profile';
require_once('includes/load.php');
// Check user login level
page_require_level(1);
$user = current_user();
?>
<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-6 col-md-offset-3">
    <div class="panel panel-default">
      <div class="panel-body text-center profile-box">
        <h3 class="profile-name"><?php echo remove_junk(ucfirst($user['name'])); ?></h3>
      </div>
      <div class="panel-footer text-center">
        <a href="edit_account.php" class="btn btn-primary">Edit profile</a>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
