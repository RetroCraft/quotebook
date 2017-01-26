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

      $('.filter').each(function() {
        var filter = $(this);
        filter.data('oldVal', filter.val());
        filter.bind("change click keyup input paste propertychange", function() {
          if (filter.data('oldVal') != filter.val()) {
            filter.data('oldVal', filter.val());
            getQuotes();
          }
        });
      });
    });

    function getQuotes() {
      var selectedAuthor = $("#author").val();

      // Get the things
      query({
        action: "main",
        "filters:search": $("#search").val(),
        "filters:speaker": selectedAuthor
      }, function(data) {
        var quoteHtml = '', authorHtml = '<option>---</option>';

        // Loop through quotes
        for (var i = 0; i < data.quotes.length; i++) {
          quoteHtml += '<a href="quote.php?id=' + data.quotes[i].id + '">' +
            '<div class="card"><div class="card-content"><p>' +
            data.quotes[i].quote + '</p></div>' +
            '<div class="card-action"><small class="text-muted">—' +
            data.quotes[i].speaker + ", " + ((data.quotes[i].year > 1) ? data.quotes[i].year : data.quotes[i].context) +
            '</small></div></div></a>';
        }

        $("#quotes").html(quoteHtml);

        // Loop through authors
        for (var j = 0; j < data.authors.length; j++) {
          authorHtml += '<option>' + data.authors[j].name + '</option>';
        }

        $("#author").html(authorHtml);
        $("#author").val(selectedAuthor);
        $("#author").material_select();
      });
    }
  </script>
</head>
<body>
  <?php include('php/navbar.php'); ?>
  <div class="header blue blue-text text-lighten-4">
    <div class="container">
      <div class="row">
        <h1>Quotebook</h1>
        <p class="lead">Why did I make this?</p>
      </div>
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

    <div class="row">
      <div class="form-inline">
        <div class="input-field inline">
          <input type="text" class="filter" id="search">
          <label>Search</label>
        </div>
        <div class="input-field inline">
          <select id="author" class="filter">
            <option value="---">---</option>
          </select>
          <label>Speaker</label>
        </div>
      </div>
    </div>
    <div class="row">
      <div id="quotes" class="col s12 card-columns center"></div>
    </div>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
