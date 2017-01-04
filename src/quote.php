<?php
  session_start();
  include("php/parsedown.php");

  if (!isset($_SESSION['user'])) {
    header('Location: http://quotebook.retrocraft.ca/login.php');
  } else {
    $user = $_SESSION['user']['name'];
  }

  if (!isset($_GET["id"])) {
    header('Location: http://quotebook.retrocraft.ca/');
  }

  $id = $_GET["id"];

  try {
    $dbh = new PDO("mysql:host=localhost;dbname=quotebook", "quotebook", "C3yA8sJzDqCjT7zh");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $dbh->prepare('SELECT * FROM vw_quotes WHERE id = :id AND status = "Approved"');
    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    $row = $stmt->fetch();
  } catch (PDOException $e) {
    fail($e->getMessage());
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
    </div>
  </div>
  <div class="container">
    <div class="markdown">
      <?php 
        echo Parsedown::instance()
          ->setBreaksEnabled(true)
          ->text($row["morestuff"]);
      ?>
    </div>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
