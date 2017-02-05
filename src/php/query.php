<?php
session_start();

// Permission level constants
define('NONE', -1);
define('VIEW', 0);
define('EDIT', 1);
define('OWN', 2);
define('ADMIN', 3);

include('database.php');
$dbh = connect();

if (isset($_POST["action"])):

switch($_POST["action"]) {

  case "main":
    if (!isset($_POST["filters:search"]) || !isset($_POST["filters:speaker"]) || !isset($_POST["sort"]) || !isset($_POST["limit"]))
      fail("Missing parameters");

    $sorting = explode("|", $_POST["sort"]);

    $sort = "`" . str_replace("`","``",$sorting[0]) . "`";
    $by = $sorting[1];

    if (!isset($_POST["page"]))
      $page = 1;
    else
      $page = $_POST["page"];

    main($_POST["filters:search"], $_POST["filters:speaker"], $sort, $by, $_POST["limit"], $page);
    break;

  case "quote":
    if (!isset($_POST['id']))
      fail("Missing parameters");
    
    quote($_POST['id']);
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
  
  case "myquotes":
    myquotes();
    break;

  case "deletemark":
    if (!isset($_POST['id']))
      fail("Missing parameters");

    // Check permissions
    try {
      $stmt = $dbh->prepare('SELECT submittedby FROM quotes WHERE id = :id');
      $stmt->bindParam(":id", $_POST["id"], PDO::PARAM_STR);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();
    } catch (PDOException $e) {
      fail($e->getMessage());
    }

    if ($row = $stmt->fetch()) {
      // Owner of quote or is administrator
      if ($row['submittedby'] == $_SESSION['user']['name'] || $_SESSION['user']['admin'] == '1') {
        statusupdate($_POST['id'], "Marked for Deletion");
      } else {
        fail("You don't have permission to delete this quote!");
      }
    } else {
      fail("Invalid id");
    }
    break;

  default:
    fail("Unknown action");
}

else:
  fail("Unspecified action");
endif;

function main($search, $speaker, $sort, $by, $limit, $page) {
  global $dbh;
  $out = '{"status": "success", "authors": [';

  try {
    $stmt = $dbh->prepare("SELECT name, num_quotes FROM vw_users WHERE num_quotes >= 1 ORDER BY num_quotes DESC;");
    $stmt->execute();
  } catch (PDOException $e) {
    fail($e->getMessage());
  }

  while ($row = $stmt->fetch()) {
    $out .= '{"name": "' . $row["name"] . '",' .
            '"num_quotes": ' . $row["num_quotes"] . '},';
  }

  $out = rtrim($out, ",");
  $out .= '], "quotes": [';

  $column = $sort;
  $direction = $by;

  $offset = (int)$limit * ((int)$page - 1);

  $query = 'SELECT id, quote, context, name, year 
            FROM vw_quotes 
            WHERE name REGEXP :speaker 
              AND status = "Approved"
              AND (quote LIKE :quote OR context LIKE :quote)
            ORDER BY ' . $column . ' ' . $by . '
            LIMIT ' . (int)$limit . ' OFFSET ' . $offset . ';';

  if ($speaker == "") {
    $speakerGlob = ".*";
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
    $stmt->bindParam(":lim", $lim, PDO::PARAM_INT);
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

  try {
    $stmt = $dbh->prepare("SELECT COUNT(*) FROM quotes");
    $stmt->execute();

    $row = $stmt->fetch();
    $total = $row["COUNT(*)"];
  } catch (PDOException $e) {
    fail($e->getMessage());
  }

  $out .= '], "page": ' . $page . ', "total": ' . $total . '}';

  die($out);
}

function permission($id) {
  // Check for admin level
  if ($_SESSION['user']['admin'] == '1')
    return ADMIN;

  // Check if owner of quote
  try {
    $stmt = $dbh->prepare('SELECT status, submittedby FROM vw_quotes WHERE id = :id');
    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
  } catch (PDOException $e) {
    fail($e->getMessage());
  }

  if ($row = $stmt->fetch()) {
    // Check owner
    if ($row['submittedby'] == $_SESSION['user']['name'])
      return OWN;
    
    // Check permissions to view
    if ($row['status'] == 'Approved')
      return VIEW;
    
    return NONE;
  } else {
    die('Invalid id.');
  }
}

function quote($id) {
  global $dbh;

  $user = $_SESSION['user']['name'];
  $access = permission($id);

  if ($access > NONE) {
    // Grab quote data
    try {
      $stmt = $dbh->prepare('SELECT id, quote, name, fullname, context, morestuff, date, year, submittedby, status, colour FROM vw_quotes WHERE id = :id');
      $stmt->bindParam(":id", $id, PDO::PARAM_STR);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();
    } catch (PDOException $e) {
      fail($e->getMessage());
    }

    $out = '{"status": "success", "quote": {';

    // Ensure that quote exists
    if ($row = $stmt->fetch()) {
      $morestuff = str_replace("\n", "\\n", htmlspecialchars($row['morestuff']));
      $out .= '"id": ' . $row['id']
            . ', "quote": "' . $row['quote']. '"'
            . ', "name": "' . $row['name']. '"'
            . ', "fullname": "' . $row['fullname']. '"'
            . ', "context": "' . $row['context']. '"'
            . ', "morestuff": "' . $morestuff . '"'
            . ', "date": "' . $row['date'] . '"'
            . ', "year": ' . $row['year'];
      if ($access >= EDIT) {
        $out .= ', "status": "' . $row['status'] . '"'
              . ', "colour": "' . $row['colour']. '"';
      }

      if ($access >= ADMIN) {
        $out .= ', "submittedby": "' . $row['submittedby'] . '"';
      }

      $out .= '}}';
      die($out);
    } else {
      fail('Something went wrong and I have no idea how this happened...');
    }
  } else {
    fail('Invalid id.');
  }
}

function changepass($currpass, $newpass, $name) {
  global $dbh;
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
  global $dbh;
  try {
    $stmt = $dbh->prepare("SELECT name FROM users;");
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
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
  global $dbh;
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

function myquotes() {
  global $dbh;

  $user = $_SESSION['user']['name'];

  try {
    $stmt = $dbh->prepare("SELECT quote, morestuff, status, fullname, id, colour FROM vw_quotes WHERE submittedby = :user;");    
    $stmt->bindParam(":user", $user, PDO::PARAM_STR);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
  } catch (PDOException $e) {
    fail($e->getMessage());
  }

  $out = '{"status": "success", "quotes": [';

  while ($row = $stmt->fetch()) {    
    if ($row['morestuff'] != "")
      $excerpt = str_replace('"', '\\"', str_replace("\n", "\\n", htmlspecialchars(substr($row['morestuff'], 0, 75)))) . '...';
    else
      $excerpt = '';

    $out .= '{"quote": "' . $row['quote'] . '",'
          . '"excerpt": "' . $excerpt . '",'
          . '"status": "' . $row['status'] . '",'
          . '"name": "' . $row['fullname'] . '",'
          . '"id": "' . $row['id'] . '",'
          . '"colour": "' . $row['colour'] . '"},';
  }

  $out = rtrim($out, ",");
  $out .= "]}";

  die($out);
}

function statusupdate($id, $status) {
  global $dbh;

  try {
    $stmt = $dbh->prepare('UPDATE quotes SET status = :status WHERE id = :id');
    $stmt->bindParam(":status", $status, PDO::PARAM_STR);
    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
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
