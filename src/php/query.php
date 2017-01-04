<?php
session_start();
include('database.php');
$dbh = connect();

if (isset($_POST["action"])):

switch($_POST["action"]) {

  case "main":
    if (!isset($_POST["filters:search"]) || !isset($_POST["filters:speaker"]))
      fail("Missing parameters");

    main($_POST["filters:search"], $_POST["filters:speaker"]);
    break;

  case "changepass":
    if (!isset($_POST['currpass']) || !isset($_POST['newpass']))
      fail("Missing parameters");

    changepass($_POST['currpass'], $_POST['newpass'], $_SESSION["user"]["name"]);
    break;
    
  case "people":
    people();
    break;

  case "submit":
    if (!isset($_POST['speaker']) || !isset($_POST['quote']) || 
        !isset($_POST['context']) || !isset($_POST['timestamp']) || !isset($_POST['morestuff']))
      fail("Missing parameters");

    submit($_POST['speaker'], $_POST['quote'], $_POST['context'], $_POST['timestamp'], $_POST['morestuff'], $_SESSION['user']['name']);
    break;

  default:
    fail("Unknown action");
}

else:
  fail("Unspecified action");
endif;

function main($search, $speaker) {
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

    if ($speaker == "---") {
      $speakerGlob = "%";
    } else {
      $speakerGlob = $speaker;
    }

    if ($search == "") {
      $searchGlob = "%";
    } else {
      $searchGlob = "%$search%";
    }

    try {
      $stmt = $dbh->prepare($query);
      $stmt->bindParam(":speaker", $speakerGlob, PDO::PARAM_STR);
      $stmt->bindParam(":quote", $searchGlob, PDO::PARAM_STR);
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
}

function changepass($currpass, $newpass, $name) {
    // check for user
    $query = "SELECT name FROM users WHERE name = :name AND pass = :pass LIMIT 1;";

    try {
      $stmt = $dbh->prepare($query);
      $stmt->bindParam(":name", $name, PDO::PARAM_STR);
      $stmt->bindParam(":pass", $currpass, PDO::PARAM_STR);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();
    } catch (PDOException $e) {
      fail($e->getMessage());
    }

    if ($row = $stmt->fetch()) {
      // Update password
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
}

function people() {
  try {
    $stmt = $dbh->prepare("SELECT name FROM users;");
    $stmt->execute();
  } catch (PDOException $e) {
    fail($e->getMessage());
  }

  $out = '{"status": "success", "people": [';

  while ($row = $stmt->fetch()) {
    $out .= '{"name": "' . $row["name"] . '"},';
  }

  $out = rtrim($out, ",");
  $out .= "]}";

  die($out);
}

function submit($speaker, $quote, $context, $timestamp, $morestuff, $submittedby) {
  $query = 'INSERT INTO quotes (quote, context, morestuff, speaker, `date`, submittedby) 
            VALUES (:quote, :context, :morestuff, :speaker, :timestamp, :submittedby)';

  try {
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(":speaker", $speaker, PDO::PARAM_STR);
    $stmt->bindParam(":quote", $quote, PDO::PARAM_STR);
    $stmt->bindParam(":context", $context, PDO::PARAM_STR);
    $stmt->bindParam(":timestamp", $timestamp, PDO::PARAM_STR);
    $stmt->bindParam(":morestuff", $morestuff, PDO::PARAM_STR);
    $stmt->bindParam(":submittedby", $submittedby, PDO::PARAM_STR);
    $stmt->execute();
  } catch (PDOException $e) {
    fail($e->getMessage());
  }

  die('{"status": "success"}');
}

function fail($error) {
  die('{"status": "error", "message": "' . $error . '", "$_POST": ' . json_encode($_POST) . ', "$_SESSION": ' . json_encode($_SESSION) . '}');
}
?>
