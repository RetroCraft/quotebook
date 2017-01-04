<div class="not-footer">
<nav class="navbar navbar-light bg-faded">
  <button class="navbar-toggler hidden-lg-up" type="button" data-toggle="collapse" data-target="#navbar"></button>
  <div class="collapse navbar-toggleable-md container" id="navbar">
    <a class="navbar-brand" href="/">Quotebook</a>
    <ul class="nav navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="/">Home</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/submit.php">Submit</a>
      </li>
    </ul>
    <form class="form-inline float-lg-right">
      <span class="navbar-text text-muted">
        Logged in as <?php echo $_SESSION['user']['name']; ?>, <a href="/php/checklogin.php?logout">logout?</a>
        <a class="btn btn-sm" href="/settings.php"><i class="material-icons" style="font-size: inherit">settings</i></a>
      </span>
    </form>
  </div>
</nav>