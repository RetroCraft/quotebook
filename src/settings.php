<?php
  session_start();
  if (!isset($_SESSION['user'])) {
    header('Location: http://quotebook.retrocraft.ca/login.php');
  } else {
    $user = $_SESSION['user']['name'];
  }
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('php/header.php'); ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha3/0.5.5/sha3.min.js"></script>
  <script>
    $(document).ready(function() {
      $('.pass').change(function() {
        if ( $('#newpass').val() != $('#newpass2').val() || !$('#newpass').val() || !$("#newpass2").val() ) {
          $('#update').prop('disabled', true);
          $('#notmatching').show();
          $('.pass').parent().addClass('has-danger');
        } else {
          $('#update').prop('disabled', false);
          $('#notmatching').hide();
          $('.pass').parent().removeClass('has-danger');
        }
      })
    });

    function update() {
      var currpass = sha3_256($('#currpass').val());
      var newpass = sha3_256($('#newpass').val());

      $.post('php/query.php', {'currpass': currpass, 'newpass': newpass, 'action': 'changepass'}, function(data) {
        console.log(data);
        if (data.status == "error") {
          $("#errormsg").html(data.message);
          $("#error").show();
        } else if (data.status == "success") {
          window.location = "http://quotebook.retrocraft.ca/settings.php?success";
        }
      }, 'json');
    }
  </script>
</head>
<body>
  <?php include('php/navbar.php'); ?>
  <div class="header">
    <div class="container">
      <h1>Settings</h1>
      <p>But seriously, why did I make this?</p>
    </div>
  </div>
  <div class="container">
    <div class="alert alert-danger" id="error" style="display:none;"><strong>Error!</strong> <span id="errormsg"></span></div>
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Details changed successfully</div>
    <?php endif; ?>
    <h1>Profile</h1>
    <div class="form-group row">
      <label for="name" class="col-xs-2 col-form-label">Username</label>
      <div class="col-xs-10"><input type="text" class="form-control" value="<?php echo $user; ?>" id="name" disabled></div>
    </div>
    <div class="form-group row">
      <label for="fullname" class="col-xs-2 col-form-label">Full Name</label>
      <div class="col-xs-10"><input type="text" class="form-control" value="<?php echo $_SESSION['user']['fullname']; ?>" id="fullname" disabled></div>
    </div>
    <div class="form-group row">
      <label for="profilepic" class="col-xs-2 col-form-label">Profile Picture (WIP)</label>
      <div class="col-xs-10">
        <input type="file" id="profilepic" class="form-control-file">
      </div>
    </div>
    <h1>Security</h1>
    <div class="form-group row">
      <label for="currpass" class="col-xs-2 col-form-label">Current Password</label>
      <div class="col-xs-10"><input type="password" class="form-control" id="currpass"></div>
    </div>
    <div class="form-group row">
      <label for="newpass" class="col-xs-2 col-form-label">New Password</label>
      <div class="col-xs-10">
        <input type="password" class="form-control pass" id="newpass">
        <div class="form-control-feedback" id="notmatching" style="display:none;">Passwords do not match!</div>
      </div>
    </div>
    <div class="form-group row">
      <label for="newpass2" class="col-xs-2 col-form-label">New Password Again</label>
      <div class="col-xs-10"><input type="password" class="form-control pass" id="newpass2"></div>
      <small class="form-text text-muted">...Because I Am Not Responsable For You Forgetting Your Password</small>
    </div>
    <button class="btn btn-success btn-lg" id="update" onclick="update()" disabled>Update</button>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
