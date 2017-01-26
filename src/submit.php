<?php
  session_start();
  include("php/parsedown.php");
  
  if (!isset($_SESSION['user'])) {
    header('Location: http://quotebook.retrocraft.ca/login.php');
  } else {
    $user = $_SESSION['user']['name'];
  }

  if ($_SESSION['user']['submit'] != "1") {
    header('Location: http://quotebook.retrocraft.ca/?err=' . urlencode("You don't have permission to submit a quote. Please contact me (james) if you think there's a mistake. If you're sure you have permission, try logging in and out."));
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
      query({action: 'people'}, function(data) {
        var people = data.people;
        var select = '';
        for (var i = 0; i < people.length; i++) {
          select += '<option>' + people[i].name + '</option>';
        }
        $("#speaker").html(select);
        $("#speaker").material_select();
      });

      // Setup submit button hook thingy
      $("#submit").click(function() {
        query({
          action: 'submit',
          speaker: $("#speaker").val(),
          quote: $("#quote").val(),
          context: $("#context").val(),
          timestamp: $("#timestamp").val(),
          morestuff: $("#morestuff").val()
        }, function(data) {
            window.location = "http://quotebook.retrocraft.ca/dashboard.php";
        });
      });
    });
  </script>
</head>
<body>
  <?php include('php/navbar.php'); ?>
  <div class="header blue blue-text text-lighten-4">
    <div class="container">
      <h1>Submit a Quote!</h1>
      <p>Mind you I have to approve all the things you type in here, and I would like to have eyes after this is all said and done.</p>
      <p>This page does is under construction and <strong>might</strong> not work. Lemme know if it doesn't.</p>
    </div>
  </div>
  <div class="container">
    <h1>Big Long Form To Fill Out</h1>
    <div class="row">
      <div class="input-field col s12">
        <select id="speaker">
          <option value="" disabled selected>Loading...</option>
        </select>
        <label for="speaker">Who said it?</label>
      </div>
    </div>
    <div class="row">
      <div class="input-field col s12">
        <label for="quote">What'd they say?</label>
        <input type="text" class="form-control" id="quote">
      </div>
    </div>
    <div class="row">
      <div class="input-field col s12">
        <label for="context">Context?</label>
        <input type="text" class="form-control" id="context" placeholder="i.e. 'on the Skype group chat'">
      </div>
    </div>
    <div class="row">
      <div class="input-field col s12">
        <input type="datetime-local" class="form-control" id="timestamp">
        <label for="timestamp" class="active">Timestamp?</label>
        <p class="form-text text-muted">If you can't remember time, put midnight (00:00). If you can't remember date, put 1<sup>st</sup> of that month. If it happens a lot, and you want the context field used instead of the date (i.e. "every. single. day."), put 01/01/0001 00:00 (i.e. zeroes. lots of them)</p>
      </div>
    </div>
    <div class="row">
      <div class="input-field col s12">
        <label for="morestuff">Anything else you'd like to add?</label>
        <!--Gosh this is a stupid decision let's pretend didn't happen...-->
        <textarea id="morestuff" cols="30" rows="10" class="materialize-textarea code">### Header (use at least H3, so that it's not *too* big)
  *italics* **bold** ***blitalics*** ~~strikethrough~~
  > This is a big giant quote!

  | Tables        | Are           | Cool  |
  | ------------- |:-------------:| -----:|
  | col 3 is      | right-aligned | $1600 |
  | col 2 is      | centered      |   $12 |
  | idk why you   | would need    |  this |</textarea>
        <p class="form-text text-muted">What you write in here is formatted with Markdown. Examples are included. If you don't know how to read an example, you can use Google for help. <a href="http://lmgtfy.com/?iie=1&q=Markdown+cheat+sheet" target="_blank">I should not have to tell you to do that.</a></p>
      </div>
    </div>
    <div class="row">
      <p>Preview of what you wrote above in case you <strong>STILL</strong> can't figure out Markdown <small class="text-muted">*sigh*</small></p>
      <div class="markdown" id="markdown-preview"></div>
    </div>
    <div class="row center">
      <button class="btn blue accent-4 waves-effect waves-light btn-large" id="submit">BIG FAT SUBMIT BUTTON!!</button>
    </div>
  </div>
  <?php include('php/footer.php'); ?>
</body>
</html>
