<?php 
require "_init.php";

if (isset($_POST['username'])) {
    if ($pdb->login_attempt($_POST['username'], $_POST['password'])) {
        $pdb->create_user_session($_POST['username']);
        header("Location: {$pdb->blog_full_base_url}/admin/");
        exit;
    } else {
        $login_failure = TRUE;
    }
} 
?>

<?php $title = "Login"; require "_header.php" ?>

<h1>Login</h1>

<?php if ($login_failure) { ?>
    <h5>Login Attempt Failed</h5>
<?php } ?>

<form action="login.html" method="POST">
  <div class="form-group">
    <label for="loginUsername">Username</label>
    <input type="text" name="username" class="form-control" id="loginUsername" placeholder="Username">
  </div>
  <div class="form-group">
    <label for="loginPassword">Password</label>
    <input type="password" name="password" class="form-control" id="loginPassword" placeholder="Password">
  </div>
  <button type="submit" class="btn btn-primary">Login</button>
</form>

<?php require '_footer.php' ?>
