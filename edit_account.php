<?php
  $page_title = 'Edit Account';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(3);
?>

<?php
  // Update user details
  if(isset($_POST['update'])){
    $req_fields = array('name','username');
    validate_fields($req_fields);
    if(empty($errors)){
      $id      = (int)$user['id'];
      $name    = remove_junk($db->escape($_POST['name']));
      $username= remove_junk($db->escape($_POST['username']));
      $sql = "UPDATE users SET name ='{$name}', username ='{$username}' WHERE id='{$id}'";
      $result = $db->query($sql);
      if($result && $db->affected_rows() === 1){
        $session->msg('s',"Account updated ");
        redirect('edit_account.php', false);
      } else {
        $session->msg('d',' Sorry, update failed!');
        redirect('edit_account.php', false);
      }
    } else {
      $session->msg("d", $errors);
      redirect('edit_account.php',false);
    }
  }

  // Change password
  if(isset($_POST['change-password'])){
    $req_fields = array('password');
    validate_fields($req_fields);
    if(empty($errors)){
      $id      = (int)$user['id'];
      $password= remove_junk($db->escape($_POST['password']));
      $h_pass  = sha1($password);
      $sql = "UPDATE users SET password='{$h_pass}' WHERE id='{$id}'";
      $result = $db->query($sql);
      if($result && $db->affected_rows() === 1){
        $session->msg('s',"Password updated ");
        redirect('edit_account.php', false);
      } else {
        $session->msg('d',' Sorry, password update failed!');
        redirect('edit_account.php', false);
      }
    } else {
      $session->msg("d", $errors);
      redirect('edit_account.php',false);
    }
  }
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong><span class="glyphicon glyphicon-edit"></span> Edit My Account</strong>
      </div>
      <div class="panel-body">
        <form method="post" action="edit_account.php">
          <div class="form-group">
            <label for="name">Name</label>
            <input type="name" class="form-control" name="name" value="<?php echo remove_junk(ucwords($user['name'])); ?>">
          </div>
          <div class="form-group">
            <label for="username">Username</label>
            <input type="name" class="form-control" name="username" value="<?php echo remove_junk($user['username']); ?>">
          </div>
          <button type="submit" name="update" class="btn btn-primary">Update</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong><span class="glyphicon glyphicon-lock"></span> Change Password</strong>
      </div>
      <div class="panel-body">
        <form method="post" action="edit_account.php">
          <div class="form-group">
            <label for="password">New password</label>
            <input type="password" class="form-control" name="password" placeholder="Type new password">
          </div>
          <button type="submit" name="change-password" class="btn btn-danger">Change Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
