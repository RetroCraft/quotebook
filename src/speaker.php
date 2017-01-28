<?php
  session_start();
  include("php/parsedown.php");
  include('php/database.php');
  $dbh = connect();

  // Check logged in
  if (!isset($_SESSION['user'])) {
    header('Location: http://quotebook.retrocraft.ca/login.php');
  } else {
    $user = $_SESSION['user']['name'];
  }

  // Make sure ID is provided
  if (!isset($_GET["id"])) {
    header('Location: http://quotebook.retrocraft.ca/');
  }

  $id = $_GET["id"];

  // Try retrieving user
  try {
    $stmt = $dbh->prepare('SELECT * FROM vw_users WHERE id = :id');
    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
  } catch (PDOException $e) {
    fail($e->getMessage());
  }

  if ($row = $stmt->fetch()) {
    // User exists
    if ($row["id"] == $_SESSION['user']['id'])
      $admin = true;
    else
      $admin = false;
  } else {
    fail("User not found. Oops...?");
  }

  function fail($err) {
    $err = urlencode($err);
    header("Location: http://quotebook.retrocraft.ca/?err=$err");
  }
?>
<!DOCTYPE html>
<html>
<head>
  <?php include('php/header.php'); ?>
  <script>
      query({
        action: "main",
        "filters:search": "",
        "filters:speaker": "<?php echo $row['name']; ?>",
        "sort": "date|DESC",
        "limit": 2147483647
      }, function(data) {
        var quoteHtml = '';

        // Loop through quotes
        for (var i = 0; i < data.quotes.length; i++) {
          quoteHtml += '<a href="quote.php?id=' + data.quotes[i].id + '">' +
            '<div class="card"><div class="card-content"><p>' +
            data.quotes[i].quote + '</p></div>' +
            '<div class="card-action"><small class="text-muted">—' +
            data.quotes[i].speaker + ", " + ((data.quotes[i].year > 1) ? data.quotes[i].year : data.quotes[i].context) +
            '</small></div></div></a>';
        }

        $(".card-columns").html(quoteHtml);
      });
  </script>
</head>
<body>
  <?php include('php/navbar.php'); ?>
  <div class="header">
    <div class="container">
      <div class="row">
        <h1>@<?php echo $row["name"]; ?></h1>
        <p><?php echo $row["fullname"]; ?></p>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <h2>Quotes</h2>
      <div class="card-columns col s12 center">
      </div>
    </div>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
