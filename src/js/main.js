function alertbox(msg, lvl) {
  var bold;

  switch (lvl) {
    case 'success':
      bold = 'Yay!';
      break;
    case 'danger':
      bold = 'Error!'
      break;
    default:
      bold = '';
  }

  $('.content').prepend('<div class="alert alert-' + lvl + ' alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' + bold + '</strong> ' + msg + '.</div>');
}

function query(params, callback) {
  $.post('php/query.php', params, function(data) {
  console.log(data);
  if (data.status == "error") {
    alertbox(data.message, 'danger');
  } else if (data.status == "success") {
    callback(data);
  }
}, 'json');
}