<header>
  <nav class="navbar">
    <div class="nav-wrapper" id="navbar">
      <a class="brand-logo center hide-on-small-and-down" href="/">Quotebook</a>
      <ul class="left">
        <li class="waves-effect"><a href="/">
          <i class="material-icons">home</i>
          <span class="hide-on-med-and-down"> Home</span>
        </a></li>
        <?php if ($_SESSION['user']['role_id'] >= 2): ?>
        <li class="waves-effect"><a href="/submit.php">
          <i class="material-icons">create</i>
          <span class="hide-on-med-and-down"> Submit</span>
        </a></li>
        <li class="waves-effect"><a href="/dashboard.php">
          <i class="material-icons">dashboard</i>
          <span class="hide-on-med-and-down"> Dashboard</span>
        </a></li>
        <?php endif; ?>
      </ul>
      <ul class="right">
        <li class="waves-effect"><a href="/settings.php">
          <i class="material-icons">settings</i>
          <span class="hide-on-med-and-down"> Settings</span>
        </a></li>
        <li class="waves-effect"><a onclick="$('#booksel').modal('open')">
          <i class="material-icons">book</i>
          <span class="hide-on-med-and-down"> Books</span>
        </a></li>
        <li class="waves-effect"><a href="/php/checklogin.php?logout">
          <i class="material-icons">exit_to_app</i>
          <span class="hide-on-med-and-down"> Logout</span>
        </a></li>
      </ul>
    </div>
  </nav>
</header>
<main>