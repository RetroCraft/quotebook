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

      query({action: 'changepass', currpass: currpass, newpass: newpass}, function(data) {
        alertbox('Settings changed successfully', 'success');
      });
    }
  </script>
</head>
<body>
  <?php include('php/navbar.php'); ?>
  <div class="header blue blue-text text-lighten-4">
    <div class="container">
      <h1>Settings</h1>
      <p>But seriously, why did I make this?</p>
    </div>
  </div>
  <div class="container">
    <h1>Profile</h1>
    <div class="row">
      <div class="input-field col s12">
        <label for="name" >Username</label>
        <input type="text" class="form-control" value="<?php echo $user; ?>" id="name" disabled>
      </div>
    </div>
    <div class="row">
      <div class="input-field col s12">
        <label for="fullname" >Full Name</label>
        <input type="text" class="form-control" value="<?php echo $_SESSION['user']['fullname']; ?>" id="fullname" disabled>
      </div>
    </div>
    <div class="row">
      <div class="file-field input-field col s12">
        <div class="btn disabled">
          <span>Profile Picture (WIP)</span>
          <input type="file" id="profilepic" disabled>
        </div>
        <div class="file-path-wrapper">
          <input type="text" class="file-path" disabled>
        </div>
      </div>
    </div>
    <h1>Security</h1>
    <div class="row">
      <div class="input-field col s12">
        <label for="currpass" >Current Password</label>
        <input type="password" class="form-control" id="currpass">
      </div>
    </div>
    <div class="row">
      <div class="input-field col s12">
        <label for="newpass" >New Password</label>
          <input type="password" class="form-control pass" id="newpass">
          <div class="form-control-feedback" id="notmatching" style="display:none;">Passwords do not match!</div>
      </div>
    </div>
    <div class="row">
      <div class="input-field col s12">
        <label for="newpass2" >New Password Again</label>
        <input type="password" class="form-control pass" id="newpass2">
        <small class="form-text text-muted">...Because I Am Not Responsable For You Forgetting Your Password</small>
      </div>
    </div>
    <button class="btn btn-success btn-lg" id="update" onclick="update()" disabled>Update</button>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
