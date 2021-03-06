<?php
if (isset($_POST['url']) && isset($_POST['secret']) && isset ($_POST['username'])):
  require_once 'capp_sso.php';
  $sso = new CappSso($_POST['url'], $_POST['secret']);
  $sso->login($_POST['username']);
else:
?>
<!doctype html>
<html>
<head>
  <title>CAPP SSO test page</title>
  <style>
    legend, span, input, button { font-family: sans-serif; }
    span, input, button { font-size: 120%; }
    label { display: block; }
    span { display: inline-block; width:10em; }
    input { width:15em; }
    .hint { color: gray; font-size: small}
  </style>
</head>
<body>
  <form method="POST">
    <fieldset>
      <legend>CAPP data</legend>
      <label><span>CAPP URL:</span><input name="url" placeholder="CAPP url" type="url" required> <span class="hint">(with trailing /)</span></label>
      <label><span>Secret:</span><input name="secret" placeholder="secret" required></label>
    </fieldset>
    <fieldset>
      <legend>Log in as</legend>
      <label><span>Username:</span><input name="username" placeholder="username" required type="text"></label>
    </fieldset>
    <button type="submit">Test</button>
  </form>
</body>
</html>
<?php endif; ?>
