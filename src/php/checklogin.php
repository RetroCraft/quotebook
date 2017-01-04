<?php
session_start();

if (!isset($_POST["action"]) && !isset($_GET['logout'])) {
  fail("No action specified.");
}

if($_POST["action"] == 'signup') {  
  include('database.php');
  $dbh = connect();

  $user = $_POST["user"];
  $name = $_POST["name"];
  $pass = $_POST["pass"];

  $stmt = $dbh->prepare("INSERT INTO users (user, fullname, pass) VALUES (:user, :name, :pass);");
  $stmt->bindParam(":user", $user);
  $stmt->bindParam(":name", $name);
  $stmt->bindParam(":pass", $pass);

  try {
    $stmt->execute();
    filelog("Signup", "For $email ($user)");
    die("success");
  } catch (PDOException $e) {
    fail($e->getMessage());
  }

} else if ($_POST["action"] == 'login') {

  if(isset($_SESSION["login"]) && $_SESSION["login"] == "true") {
    success("Already logged in.");
    header("Location: http://quotebook.retrocraft.ca");
  } else {
    include('database.php');
    $dbh = connect();

    // check for user
    $query = "SELECT id, name, fullname FROM users WHERE name = :name AND pass = :pass LIMIT 1;";
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

      filelog("Login", "From $uid ($user_name)");
      header("Location: http://quotebook.retrocraft.ca");
      success("Login successful.");
    } else {
      filelog("Login", "Incorrect login for " . $_POST["email"] . ".");
      fail("Incorrect or unknown username or password.");
    }
  }

} else if (isset($_GET["logout"]) || $_POST["action"] = 'logout') {

  // logout
  filelog("Logout", "From $uid ($user_name)");
  session_unset();
  session_destroy();
  header("Location: http://quotebook.retrocraft.ca/login.php?logout");
  success("Logout successful");

} else {

  fail("Unknown action");

}

function fail($error) {
  die('{"status": "error", "message": "' . $error . '"}');
}

function success($msg) {
  die('{"status": "success", "message": "' . $msg . '"}');
}

function filelog($msg, $context) {
  $now = date("Y-m-d H:i:s");
  $cip = $_SERVER["REMOTE_ADDR"];
  $log = "[$now] ($context|$cip) $msg\n";
  file_put_contents("/var/www/quotebook/log.txt", $log, FILE_APPEND);
}

?>
