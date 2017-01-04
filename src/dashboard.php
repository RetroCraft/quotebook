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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.5.5/showdown.min.js"></script>
  <script>
    var mkd = new showdown.Converter({
      omitExtraWLInCodeBlocks: true,
      noHeaderId: true,
      simplifiedAutoLink: true,
      excludeTrailingPunctuationFromURLs: true,
      strikethrough: true,
      tables: true,
      smoothLivePreview: true,
      simpleLineBreaks: true
    });
  </script>
</head>
<body>
  <?php include('php/navbar.php'); ?>
  <div class="header">
    <div class="container">
      <h1>Dashboard</h1>
      <p>This page does is under construction and <strong>will</strong> not work.</p>
    </div>
  </div>
  <div class="container">
    <div class="alert alert-danger" id="error" style="display:none;"><strong>Error!</strong> <span id="errormsg"></span></div>
    <h2>Your Quotes</h2>
    <ul class="list-group quotes">
      <li href="#" class="list-group-item list-group-item-action">
        <span class="tag tag-success tag-pill float-xs-right">Approved</span>
        <h5 class="list-group-item-heading">Quote...</h5>
        <a href="#" class="edit-icon material-icons float-xs-right">edit</a>
        <a href="#" class="edit-icon material-icons float-xs-right">delete</a>
        <p class="list-group-item-text">Excerpt from morestuff</p>
      </li>
    </ul>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
