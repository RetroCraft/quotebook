<?php
  session_start();
  if (isset($_SESSION['user'])) {
    header('Location: http://quotebook.retrocraft.ca/?info=Already%20logged%20in');
  }
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('php/header.php'); ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha3/0.5.5/sha3.min.js"></script>
  <script>
    $(document).ready(function() {
      $("#login").click(function() {
        name = $("#username").val();
        pass = sha3_256($("#password").val());
        $.post('php/checklogin.php', {'name': name, 'pass': pass, 'action': 'login'}, function(data) {
          console.log(data);
          if (data.status == "error") {
            $("#errormsg").html(data.message);
            $("#error").show();
          } else if (data.status == "success") {
            window.location = "http://quotebook.retrocraft.ca/";
          }
        }, 'json');
      })
    });
  </script>
</head>
<body>
  <div class="container">
    <h1 style="margin-top: 10%">Login</h1>
    <hr>
    <?php if (isset($_GET["logout"])): ?>
      <div class="alert alert-success">Logout successful</div>
    <?php endif; ?>
    <div class="alert alert-danger" id="error" style="display:none">
      <strong>Error!</strong> <span id="errormsg"></span>
    </div>
    <div class="form">
      <div class="form-group">
        <label for="username" class="bmd-label-floating">Username</label>
        <input type="text" id="username" class="form-control">
      </div>
      <div class="form-group">
        <label for="password" class="bmd-label-floating">Password</label>
        <input type="password" id="password" class="form-control">
      </div>
      <div class="form-group">
        <button id="login" class="btn btn-lg btn-primary">Login</button>
      </div>
    </div>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
