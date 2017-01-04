<?php
session_start();
include('database.php');
$dbh = connect();

if (isset($_POST["action"])):

switch($_POST["action"]) {

  case "main":
    if (!isset($_POST["filters:search"]) || !isset($_POST["filters:speaker"])) {
      fail("Missing parameters");
    }

    $out = '{"status": "success", "authors": [';

    try {
      $stmt = $dbh->prepare("SELECT * FROM vw_users;");
      $stmt->execute();
    } catch (PDOException $e) {
      fail($e->getMessage());
    }

    while ($row = $stmt->fetch()) {
      $out .= '{"id": ' . $row["id"] . ',' .
              '"name": "' . $row["name"] . '",' .
              '"fullname": "' . $row["fullname"] . '"},';
    }

    $out = rtrim($out, ",");
    $out .= '], "quotes": [';

    $query = 'SELECT id, quote, context, name, year 
              FROM vw_quotes 
              WHERE name LIKE :speaker 
                AND status = "Approved"
                AND (quote LIKE :quote OR context LIKE :quote)';

    if ($_POST["filters:speaker"] == "---") {
      $speaker = "%";
    } else {
      $speaker = $_POST["filters:speaker"];
    }

    if ($_POST["filters:search"] == "") {
      $quote = "%";
    } else {
      $quote = "%" . $_POST["filters:search"] . "%";
    }

    try {
      $stmt = $dbh->prepare($query);
      $stmt->bindParam(":speaker", $speaker, PDO::PARAM_STR);
      $stmt->bindParam(":quote", $quote, PDO::PARAM_STR);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();
    } catch (PDOException $e) {
      fail($e->getMessage());
    }

    while ($row = $stmt->fetch()) {
      $out .= '{"id": ' . $row["id"] . ',' .
              '"quote": "' . $row["quote"] . '",' .
              '"context": "' . $row["context"] . '",' .
              '"speaker": "' . $row["name"] . '",' .
              '"year": "' . $row["year"] . '"},';
    }

    $out = rtrim($out, ",");
    $out .= "]}";

    die($out);
    break;

  case "changepass":
    if (!isset($_POST['currpass']) || !isset($_POST['newpass'])) {
      fail("Missing parameters");
    }

    // check for user
    $query = "SELECT name FROM users WHERE name = :name AND pass = :pass LIMIT 1;";
    $name = $_SESSION["user"]["name"];
    $pass = $_POST["currpass"];

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
      $newpass = $_POST["newpass"];

      try {
        $stmt = $dbh->prepare("UPDATE users SET pass = :pass WHERE name = :name");
        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt->bindParam(":pass", $newpass, PDO::PARAM_STR);
        $stmt->execute();
      } catch (PDOException $e) {
        fail($e->getMessage());
      }
      
      die('{"status":"success"}');

    } else {
      fail("Incorrect current password, or something else wrong happened.");
    }

    break;

  default:
    fail("Unknown action");
}

else:
  fail("Unspecified action");
endif;

function fail($error) {
  die('{"status": "error", "message": "' . $error . '", "$_POST": ' . json_encode($_POST) . ', "$_SESSION": ' . json_encode($_SESSION) . '}');
}
?>
