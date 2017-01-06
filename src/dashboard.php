<?php
  session_start();
  
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

    var quotes = {};

    $(document).ready(refresh);

    function refresh() {
      query({action: 'myquotes'}, function(data) {
        html = '';
        quotes = data.quotes;
        for (var i = 0; i < quotes.length; i++) {
          q = quotes[i];
          html += '<li href="#" class="list-group-item list-group-item-action">'
                + '<span class="tag tag-' + q.class + ' tag-pill float-xs-right">' + q.status + '</span>'
                + '<h5 class="list-group-item-heading"><a href="/quote.php?id=' + q.id + '">' + q.quote + '</a></h5>'
                + '<p class="list-group-item-text">' + q.excerpt + '</p>';
          if (q.status != "Marked for Deletion") {
            html += '<div class="btn-group edit-icon float-xs-right">'
                  + '<button class="btn btn-sm btn-primary material-icons" onclick="edit(' + i + ', 0)">edit</button>'
                  + '<button class="btn btn-sm btn-danger material-icons" onclick="del(' + i + ', 0)">delete</button>'
                  + '</div>';
          }
          html += '<p class="list-group-item-text">&ndash;' + q.name + '</p>'
                + '</li>';
        }
        $(".quotes").html(html);
      });
    }

    function edit(id) {
      var q = quotes[id];

      // Setup modal
      var modal = $('#edit-modal');

      // Populate list of authors
      query({action: 'people'}, function(data) {
        var people = data.people;
        var select = '';
        for (var i = 0; i < people.length; i++) {
          select += '<option>' + people[i].name + '</option>';
        }
        $("#speaker").html(select);
      });

      // Get and populate fields
      query({action: 'quote', id: q.id}, function(data) {
        var quote = data.quote;
        $("#speaker").val(quote.name);
        $("#quote").val(quote.quote);
        $("#context").val(quote.context);

        // Parse timestamp
        var tzoffset = (new Date()).getTimezoneOffset() * 60000; //offset from Z in ms
        var timestamp = (new Date(new Date(quote.date) - tzoffset)).toISOString().slice(0,-1);
        $("#timestamp").val(timestamp);

        // Decode morestuff with the hackiest thing ever
        $("#morestuff").val($('<textarea />').html(quote.morestuff).text());

        // Setup live markdown parsing thingy
        var textarea = $("#morestuff");
        textarea.data('oldVal', textarea.val());
        textarea.bind("change click keyup input paste propertychange", function(e) {
          if (textarea.data('oldVal') != textarea.val()) {
            textarea.data('oldVal', textarea.val());
            $("#markdown-preview").html(mkd.makeHtml(textarea.val()));
          }
        });
        
        $("#markdown-preview").html(mkd.makeHtml(textarea.val()));
      });

      // Setup submit button hook thingy
      $("#submit").click(function() {
        query({
          action: 'edit',
          id: q.id,
          speaker: $("#speaker").val(),
          quote: $("#quote").val(),
          context: $("#context").val(),
          timestamp: $("#timestamp").val(),
          morestuff: $("#morestuff").val()
        }, function(data) {
          alertbox('Edit successful.', 'success');
          refresh();
        });
      });
      
      // Show modal
      modal.modal();
    }

    function del(id) {
      var q = quotes[id];

      // Setup modal
      var modal = $('#del-modal');
      modal.find('[data-template]').each(function() {
        // Possibly the most illegible line of code in this entire repository
        $(this).html(q[$(this).attr('data-template')]);
      });

      // Setup actual delete button
      $('#delete').click(function() {
        query({action: 'deletemark', id: q.id}, function(data) {
          alertbox('Delete successful.', 'success');
          refresh();
        });
      });

      // Show modal
      modal.modal();
    }
  </script>
</head>
<body>
  <?php include('php/navbar.php'); ?>
  <div class="header">
    <div class="container">
      <h1>Dashboard</h1>
      <p>This page does is under construction and <strong>might</strong> not work. (Delete button does though!)</p>
    </div>
  </div>
  <div class="container content">
    <h2>Your Quotes</h2>
    <ul class="list-group quotes"></ul>
  </div>
  <?php include('php/footer.php'); ?>
  <div class="modal fade" id="del-modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button class="close" data-dismiss="modal"><span>&times;</span></button>
          <h4 class="modal-title">Delete: <span data-template="quote"></span></h4>
        </div>
        <div class="modal-body">
          <p>This will mark the quote below for permanent deletion. This cannot be undone once the deletion is complete.</p>
          <blockquote class="blockquote">
            <p class="mb-0 quote" data-template="quote"></p>
            <footer class="blockquote-footer" data-template="name"></footer>
          </blockquote>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button class="btn btn-danger" id="delete">Delete</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="edit-modal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button class="close" data-dismiss="modal"><span>&times;</span></button>
          <h4 class="modal-title">Edit: <span data-template="quote"></span></h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="speaker">Who said it?</label>
            <select id="speaker" class="custom-select form-control">
              <option value="">Loading...</option>
            </select>
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
            <p class="form-text text-muted">If you can't remember time, put midnight (00:00). If you can't remember date, put 1<sup>st</sup> of that month. If it happens a lot, and you want the context field used instead of the date (i.e. "every. single. day."), put 01/01/0001 00:00 (i.e. zeroes. lots of them)</p>
          </div>
          <div class="form-group">
            <label for="morestuff">Anything else you'd like to add?</label>
            <!--Gosh this is a stupid decision let's pretend didn't happen...-->
            <textarea id="morestuff" cols="30" rows="10" class="form-control code"></textarea>
            <p class="form-text text-muted">What you write in here is formatted with Markdown. Examples are included. If you don't know how to read an example, you can use Google for help. <a href="http://lmgtfy.com/?iie=1&q=Markdown+cheat+sheet" target="_blank">I should not have to tell you to do that.</a></p>
          </div>
          <p>Preview of what you wrote above in case you <strong>STILL</strong> can't figure out Markdown <small class="text-muted">*sigh*</small></p>
          <div class="markdown" id="markdown-preview"></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button class="btn btn-success" id="submit">BIG FAT (RE)SUBMIT BUTTON!!</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
