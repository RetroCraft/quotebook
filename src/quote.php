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

  // Try retrieving quote as user
  try {
    $stmt = $dbh->prepare('SELECT * FROM vw_quotes WHERE id = :id AND submittedby = :user');
    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
    $stmt->bindParam(":user", $user, PDO::PARAM_STR);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
  } catch (PDOException $e) {
    fail($e->getMessage());
  }

  if ($row = $stmt->fetch()) {
    // User owns quote, allow edit functions
    $admin = true;

    switch ($row['status']) {
      case "Submitted":
        $class = "warning";
        break;
      case "Approved":
        $class = "success";
        break;
      case "Rejected":
        $class = "danger";
        break;
      case "Marked for Deletion":
        $class = "default";
        break;
    }
  } else {
    $admin = false;

    // Try retrieving quote normally
    $stmt = $dbh->prepare('SELECT * FROM vw_quotes WHERE id = :id AND status = "Approved"');
    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    $row = $stmt->fetch();
    
    // Quote does not exist or is not available to user
    if (!$row) {
      fail("Quote not found. Maybe it was deleted?");
    }
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
</head>
<body>
  <?php include('php/navbar.php'); ?>
  <div class="header">
    <div class="container">
      <h1><?php echo $row["quote"]; ?></h1>
      <p>
        &mdash;<a href="speaker.php?id=<?php echo $row["speaker"]; ?>"><?php echo $row["fullname"]; ?></a>, 
        <?php echo $row["context"];
        if ($row["year"] > 1) {
        echo ", " . $row["year"]; 
        } ?>
      </p>
      <?php if ($admin): ?>
      <hr>
      <p>
        <strong>Note:</strong> You own this quote. Go to the <a href="dashboard.php">Dashboard</a> to edit it.
        <span class="tag tag-<?php echo $class; ?> tag-pill float-lg-right">
          Status: <?php echo $row['status']; ?>
        </span>
      </p>
      <?php endif; ?>
    </div>
  </div>
  <div class="container">
    <h2>Description</h2>
    <div class="markdown">
      <?php 
        if ($row["morestuff"] != "") {
          echo Parsedown::instance()
            ->setBreaksEnabled(true)
            ->text($row["morestuff"]);
        } else {
          echo "No description provided. Sorry... ☹";
        }
      ?>
    </div>
    <hr>
    <h2>Info</h2>
    <table class="table table-bordered table-hover">
      <tbody>
        <tr>
          <th scope="row">Estimated Date/Time</th>
          <td><?php echo $row["date"]; ?></td>
        </tr>
        <tr>
          <th scope="row">Submitted By</th>
          <td><?php echo $row["submittedby"]; ?></td>
        </tr>
      </tbody>
    </table>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
