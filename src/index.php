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
    var currPage;
    $(document).ready(function() {
      getQuotes(1);

      // Setup select watching
      $('.filter').each(function() {
        var filter = $(this);
        filter.data('oldVal', filter.val());
        filter.bind("change click keyup input paste propertychange", function() {
          if (filter.data('oldVal') != filter.val()) {
            filter.data('oldVal', filter.val());
            getQuotes(1);
          }
        });
      });

      // Setup author watching
      $('#author').change(function() {
          if ($('option:not(:first)', this).is(':selected')) {
              $('option:first', this).prop('selected', false);
          }
      });
    });

    function getQuotes(page) {
      var limit = $("#num").val();

      // Parse author array
      var selectedAuthors = $("#author").val();
      var authors = selectedAuthors.join("|");

      // Get the things
      query({
        action: "main",
        "filters:search": $("#search").val(),
        "filters:speaker": (authors ? authors : ""),
        "sort": ($("#sort").val() ? $("#sort").val() : "createtime|DESC"),
        "limit": limit,
        "page": page
      }, function(data) {
        var quoteHtml = '', authorHtml = '<option value="" disabled>All</option>', paginationHtml = '';

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
          authorHtml += '<option value="' + data.authors[j].name + '">' + data.authors[j].name + ' (' + data.authors[j].num_quotes + ')</option>';
        }

        $("#author").html(authorHtml);
        $("#author").val(selectedAuthors);
        $("#author").material_select();

        // Loop through pages
        var pages = Math.ceil(data.total / limit);
        currPage = data.page;

        paginationHtml += '<li class="' + (currPage == 1 ? 'disabled' : 'waves-effect') + '"><a href="#!" onclick="getQuotes(currPage - 1)"><i class="material-icons">chevron_left</i></a></li>';

        for (var page = 1; page <= pages; page++) {
          paginationHtml += '<li class="' + (page == currPage ? 'active' : 'waves-effect') + '"><a href="#!" onclick="getQuotes(' + page + ')">' + page + '</a></li>';
        }

        paginationHtml += '<li class="' + (currPage == pages ? 'disabled' : 'waves-effect') + '"><a href="#!" onclick="getQuotes(currPage + 1)"><i class="material-icons">chevron_right</i></a></li>';

        $("#pagination").html(paginationHtml);
      });
    }
  </script>
</head>
<body>
  <?php include('php/navbar.php'); ?>
  <div class="header">
    <div class="container">
      <div class="row">
        <h1>Quotebook &ndash; <?php echo $_SESSION['user']['book_displayname']; ?></h1>
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
      <div class="input-field inline col s12 m6 l3">
        <input type="text" class="filter" id="search">
        <label>Search</label>
      </div>
      <div class="input-field inline col s12 m6 l3">
        <select id="author" class="filter" multiple>
          <option value="" disabled>All</option>
        </select>
        <label>Speaker</label>
      </div>
      <div class="input-field inline col s12 m6 l3">
        <select id="sort" class="filter">
          <option value="createtime|DESC" selected>Latest</option>
          <option value="date|DESC">Newest</option>
        </select>
        <label>Sort</label>
      </div>
      <div class="input-field inline col s12 m6 l3">
        <select id="num" class="filter">
          <option>10</option>
          <option selected>20</option>
          <option>50</option>
          <option>100</option>
          <option value="2147483647">All</option>
        </select>
        <label>Quotes on Page</label>
      </div>
    </div>
    <div class="row">
      <div id="quotes" class="col s12 card-columns center"></div>
    </div>
    <div class="row center">
      <ul class="pagination" id="pagination"></ul>
    </div>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
