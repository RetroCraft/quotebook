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
      $("#error").hide();
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

    function edit(id, stage) {
      
    }

    function del(id) {
      $("#error").hide();
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
</body>
</html>
