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
            alertbox(data.message, 'danger');
          } else if (data.status == "success") {
            window.location = "http://quotebook.retrocraft.ca/";
          }
        }, 'json');
      })
    });
  </script>
</head>
<body>
  <main>
  <div class="container">
    <h1 style="margin-top: 10%">Login</h1>
    <hr>
    <div class="row">
      <?php if (isset($_GET["logout"])): ?>
        <div class="alert alert-success"><strong>Yay!</strong> Logout successful</div>
      <?php endif; ?>
    </div>
    <div class="row">
      <div class="input-field col s12">
        <label for="username">Username</label>
        <input type="text" id="username">
      </div>
    </div>
    <div class="row">
      <div class="input-field col s12">
        <label for="password">Password</label>
        <input type="password" id="password">
      </div>
    </div>
    <button id="login" class="btn waves-effect waves-light">Login</button>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
