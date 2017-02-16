<?php
  session_start();
  
  if (!isset($_SESSION['user'])) {
    header('Location: http://quotebook.retrocraft.ca/login.php');
  } else {
    $user = $_SESSION['user']['name'];
    $admin = ($_SESSION["user"]["admin"] == 1);
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
        console.log(data);
        html = '';
        quotes = data.quotes;
        for (var i = 0; i < quotes.length; i++) {
          q = quotes[i];
          html += '<div class="card"><div class="card-content">'
                + '<span class="card-title"><a href="/quote.php?id=' + q.id + '">' + q.quote + '</a></span>'
                + '<p>' + q.excerpt + '</p>' + '<p>&ndash;' + q.name + '</p></div><div class="card-action">';
          if (q.status != "Marked for Deletion") {
            html += '<a href="#" onclick="edit(' + i + ', 0)"><i class="material-icons">edit</i></a>'
                  + '<a href="#" onclick="del(' + i + ', 0)"><i class="material-icons">delete</i></a>'
                  + '<span class="status sm ' + q.colour + '">' + q.status + '</span>';
          }
          html += '</div></div>';
        }
        $("#quotes-mine").html(html);
      });
      <?php if (admin): ?>
        query({action: 'approvequotes'}, function(data) {
          console.log(data);
          html = '<ul class="collection">';
          quotes = data.quotes;
          for (var i = 0; i < quotes.length; i++) {
            q = quotes[i];
            html += '<li class="collection-item">'
                  + '<span class="title">' + q.quote + '</span><br>'
                  + '&mdash;' + q.name 
                  + '</li>';
          }
          html += '</ul>';
          $("#approve").html(html);
        });
      <?php endif; ?>
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
        $("#speaker").material_select();
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
        $("#morestuff").trigger('autoresize');

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
      modal.modal().modal('open');
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
      modal.modal().modal('open');
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
  <div class="container">
    <div class="row">
      <div class="col s12">
        <ul class="tabs">
          <li class="tab"><a href="#quotes-mine">My Quotes</a></li>
          <?php if (admin): ?><li class="tab"><a href="#approve">Approve Quotes</a></li><?php endif; ?>
        </ul>
      </div>
      <div id="quotes-mine" class="quotes card-columns col s12"></div>
      <?php if (admin): ?><div id="approve" class="col s12"></div><?php endif; ?>
    </div>
  </div>
  <?php include('php/footer.php'); ?>
  <div class="modal modal-fixed-footer" id="del-modal">
    <div class="modal-content">
      <p>This will mark the quote below for permanent deletion. This cannot be undone once the deletion is complete.</p>
      <blockquote class="blockquote">
        <p class="mb-0 quote" data-template="quote"></p>
        <small>&mdash;<span data-template="name"></span></small>
      </blockquote>
    </div>
    <div class="modal-footer">
      <button class="btn modal-action modal-close waves-effect waves-blue btn-flat" data-dismiss="modal">Cancel</button>
      <button class="btn modal-action modal-close waves-effect waves-light red" id="delete">Delete</button>
    </div>
  </div>
  <div class="modal modal-fixed-footer" id="edit-modal">
    <div class="modal-content">
      <div class="form-group">
        <label for="speaker">Who said it?</label>
        <select id="speaker" class="custom-select ">
          <option value="">Loading...</option>
        </select>
      </div>
      <div class="form-group">
        <label for="quote">What'd they say?</label>
        <input type="text" class="" id="quote">
      </div>
      <div class="form-group">
        <label for="context">Context?</label>
        <input type="text" class="" id="context" placeholder="i.e. 'on the Skype group chat'">
      </div>
      <div class="form-group">
        <label for="timestamp">Timestamp?</label>
        <input type="datetime-local" class="" id="timestamp">
        <p class="form-text text-muted">If you can't remember time, put midnight (00:00). If you can't remember date, put 1<sup>st</sup> of that month. If it happens a lot, and you want the context field used instead of the date (i.e. "every. single. day."), put 01/01/0001 00:00 (i.e. zeroes. lots of them)</p>
      </div>
      <div class="form-group">
        <label for="morestuff">Anything else you'd like to add?</label>
        <!--Gosh this is a stupid decision let's pretend didn't happen...-->
        <textarea id="morestuff" cols="30" rows="10" class="materialize-textarea code"></textarea>
        <p class="form-text text-muted">What you write in here is formatted with Markdown. Examples are included. If you don't know how to read an example, you can use Google for help. <a href="http://lmgtfy.com/?iie=1&q=Markdown+cheat+sheet" target="_blank">I should not have to tell you to do that.</a></p>
      </div>
      <p>Preview of what you wrote above in case you <strong>STILL</strong> can't figure out Markdown <small class="text-muted">*sigh*</small></p>
      <div class="markdown" id="markdown-preview"></div>
    </div>
    <div class="modal-footer">
      <button class="btn modal-action modal-close waves-effect waves-blue btn-flat" data-dismiss="modal">Cancel</button>
      <button class="btn modal-action modal-close waves-effect waves-light blue" id="submit">BIG FAT (RE)SUBMIT BUTTON!!</button>
    </div>
  </div>
</body>
</html>
