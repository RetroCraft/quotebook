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
  <script>
    $(document).ready(function() {
      getQuotes();

      $('.filter').change(getQuotes);
    });

    function getQuotes() {
      var selectedAuthor = $("#author").val();
      var params = {
        action: "main",
        "filters:search": $("#search").val(),
        "filters:speaker": selectedAuthor
      }

      // Get the things
      $.post("php/query.php", params, function(data) {
        var quoteHtml = '', authorHtml = '<option>---</option>';

        // Loop through quotes
        for (var i = 0; i < data.quotes.length; i++) {
          quoteHtml += '<a href="quote.php?id=' + data.quotes[i].id + '">' +
            '<div class="card card-block"><blockquote class="card-blockquote">' +
            '<p>' + data.quotes[i].quote + '</p>' +
            '<footer><small class="text-muted">—' +
            data.quotes[i].speaker + ", " + ((data.quotes[i].year > 1) ? data.quotes[i].year : data.quotes[i].context) +
            '</small></footer></blockquote></div></a>';
        }

        $("#quotes").html(quoteHtml);

        // Loop through authors
        for (var j = 0; j < data.authors.length; j++) {
          authorHtml += '<option>' + data.authors[j].name + '</option>';
        }

        $("#author").html(authorHtml);
        $("#author").val(selectedAuthor);

      }, "json");
    }
  </script>
</head>
<body>
  <?php include('php/navbar.php'); ?>
  <div class="header">
    <div class="container">
      <h1>Quotebook</h1>
      <p class="lead">Why did I make this?</p>
    </div>
  </div>
  <div class="container">
    <?php if (isset($_GET['err'])): ?>
      <div class="alert alert-danger">
        <strong>Error!</strong> <?php echo $_GET['err']; ?>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['info'])): ?>
      <div class="alert alert-info">
        <?php echo $_GET['info']; ?>
      </div>
    <?php endif; ?>

    <div class="form-inline">
      <div class="input-group">
        <span class="input-group-addon">Search</span>
        <input type="text" class="form-control filter" id="search">
      </div>
      <div class="input-group">
        <span class="input-group-addon">Speaker</span>
        <select id="author" class="custom-select filter form-control">
          <option value="---">---</option>
        </select>
      </div>
    </div>
    <br>
    <div id="quotes" class="card-columns">
    </div>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
