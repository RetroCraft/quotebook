<?php
  session_start();
  include("php/parsedown.php");
  
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.5.5/showdown.min.js"></script>
  <script>
    var mkd = new showdown.Converter({
      omitExtraWLInCodeBlocks: true,
      noHeaderId: true,
      simplifiedAutoLink: true,
      excludeTrailingPunctuationFromURLs: true,
      strikethrough: true,
      tables: true,
      smoothLivePreview: true,
      simpleLineBreaks: true
    });

    $(document).ready(function() {
      // Setup live markdown parsing thingy
      var textarea = $("#morestuff");
      $("#markdown-preview").html(mkd.makeHtml(textarea.val()));
      textarea.data('oldVal', textarea.val());
      textarea.bind("change click keyup input paste propertychange", function(e) {
        if (textarea.data('oldVal') != textarea.val()) {
          textarea.data('oldVal', textarea.val());
          $("#markdown-preview").html(mkd.makeHtml(textarea.val()));
        }
      });

      // Populate list of authors
      $.post('php/query.php', {'action': 'people'}, function(data) {
        console.log(data);
        if (data.status == "error") {
          $("#errormsg").html(data.message);
          $("#error").show();
        } else if (data.status == "success") {
          var people = data.people;
          var select = '';
          for (var i = 0; i < people.length; i++) {
            select += '<option>' + people[i].name + '</option>';
          }
          $("#speaker").html(select);
        }
      }, 'json');
    });
  </script>
</head>
<body>
  <?php include('php/navbar.php'); ?>
  <div class="header">
    <div class="container">
      <h1>Submit a Quote!</h1>
      <p>Mind you I have to approve all the things you type in here, and I would like to have eyes after this is all said and done.</p>
      <p>This page does not actually work yet. Do not use it. It won't do anything.</p>
    </div>
  </div>
  <div class="container">
    <div class="alert alert-danger" id="error" style="display:none;"><strong>Error!</strong> <span id="errormsg"></span></div>
    <h1>Big Long Form To Fill Out</h1>
    <div class="form-group">
      <label for="speaker">Who said it?</label>
      <select id="speaker" class="custom-select form-control"></select>
    </div>
    <div class="form-group">
      <label for="quote">What'd they say?</label>
      <input type="text" class="form-control" id="quote">
    </div>
    <div class="form-group">
      <label for="context">Context?</label>
      <input type="text" class="form-control" id="context" placeholder="i.e. 'on the Skype group chat'">      
    </div>
    <div class="form-group">
      <label for="timestamp">Timestamp?</label>
      <input type="datetime-local" class="form-control" id="timestamp">
      <p class="form-text text-muted">If you can't remember time, put midnight (00:00). If you can't remember date, put 1<sup>st</sup> of that month. If it happens a lot, and you want the context field used instead of the date (i.e. "every. single. day."), put 0000-00-00 00:00 (i.e. zeroes. lots of them)</p>
    </div>
    <div class="form-group">
      <label for="morestuff">Anything else you'd like to add?</label>
      <!--Gosh this is a stupid decision let's pretend didn't happen...-->
      <textarea id="morestuff" cols="30" rows="10" class="form-control code">### Header (use at least H3, so that it's not *too* big)
*italics* **bold** ***blitalics*** ~~strikethrough~~
> This is a big giant quote!
| Tables        | Are           | Cool  |
| ------------- |:-------------:| -----:|
| col 3 is      | right-aligned | $1600 |
| col 2 is      | centered      |   $12 |
| idk why you   | would need    |  this |
</textarea>
      <p class="form-text text-muted">What you write in here is formatted with Markdown. Examples are included. If you don't know how to read an example, you can use Google for help. <a href="http://lmgtfy.com/?iie=1&q=Markdown+cheat+sheet" target="_blank">I should not have to tell you to do that.</a></p>
    </div>
    <p>Preview of what you wrote above in case you <strong>STILL</strong> can't figure out Markdown <small class="text-muted">*sigh*</small></p>
    <div class="markdown" id="markdown-preview"></div>
    <button class="btn btn-lg btn-success" id="submit">BIG FAT SUBMIT BUTTON!!</button>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
