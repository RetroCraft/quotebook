<?php
session_start();

if ($_POST["action"] == 'login') {

  if(isset($_SESSION["login"]) && $_SESSION["login"] == "true") {
    success("Already logged in.");
    header("Location: http://quotebook.retrocraft.ca");
  } else {
    include('database.php');
    $dbh = connect();

    // check for user
    $query = "SELECT id, name, fullname, admin FROM users WHERE name = :name AND pass = :pass AND login = 1 LIMIT 1;";
    $name = $_POST["name"];
    $pass = $_POST["pass"];

    try {
      $stmt = $dbh->prepare($query);
      $stmt->bindParam(":name", $name, PDO::PARAM_STR);
      $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();
    } catch (PDOException $e) {
      fail($e->getMessage());
    }

    if ($row = $stmt->fetch()) {
      $_SESSION["login"] = "true";
      $_SESSION["user"] = $row;

      filelog("Login", "From $name");
      success("Login successful.");
    } else {
      filelog("Login", "Incorrect login for $name.");
      fail("Incorrect or unknown username or password.");
    }
  }

} else if (isset($_GET["logout"])) {

  $name = $_SESSION['user']['name'];
  filelog("Logout", "From $name");
  session_unset();
  session_destroy();
  header("Location: http://quotebook.retrocraft.ca/login.php?logout");
  success("Logout successful");

} else if (isset($_GET['book'])) {

  include('database.php');
  $dbh = connect();

  $user = $_SESSION['user']['id'];
  $newbook = $_GET['book'];

  $query = "SELECT id, name, fullname, login, book_id, book_name, book_displayname, role_id, role 
              FROM vw_users 
              WHERE id = :id AND book_id = :book
              LIMIT 1";

  try {
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(":id", $user, PDO::PARAM_STR);
    $stmt->bindParam(":book", $newbook, PDO::PARAM_STR);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
  } catch (PDOException $e) {
    fail($e->getMessage());
  }

  if ($row = $stmt->fetch()) {
    $_SESSION['user'] = $row;
    
    header('Location: http://quotebook.retrocraft.ca/');
  } else {
    header('Location: http://quotebook.retrocraft.ca/?err=Unknown+book');
  }

} else {

  fail("Unknown or unspecified action");

}

function fail($error) {
  die('{"status": "error", "message": "' . $error . '"}');
}

function success($msg) {
  die('{"status": "success", "message": "' . $msg . '"}');
}

function filelog($context, $msg) {
  $now = date("Y-m-d H:i:s");
  $cip = $_SERVER["REMOTE_ADDR"];
  $log = "[$now] ($context|$cip) $msg\n";
  file_put_contents("/var/www/quotebook/log.txt", $log, FILE_APPEND);
}

?>
