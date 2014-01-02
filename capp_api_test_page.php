<?php

$submitted = isset($_POST['url']) && isset($_POST['username']) && isset($_POST['passphrase']);
if($submitted) {
  require_once('capp_api_calls.php');

  $user = [
      "username" => $_POST['username'],
      "passphrase" => $_POST['passphrase']
  ];

  $api = new CappApiCalls($_POST['url'], $user);

  function status ($result) {
    return is_array($result) && isset($result['status']) ? 'error' : 'ok';
  }

  $persons = $api->getAllPersons();
  $courseTemplates = $api->getCourseTemplates(true);
  $course = $api->getCourse(4);
  $courseSubscription = $api->getSubscription(1,52);
}
?>
<!doctype html>
<html>
<head>
  <title>CAPP API test page</title>
  <style>
    a { cursor: pointer;}
    pre {display: none;}
    .ok { color:green;}
    .error {color: red;}
  </style>
</head>
<body>
  <form method="POST">
    <input name="url" placeholder="API base url" type="url" required>
    <input name="username" placeholder="username" required>
    <input name="passphrase" placeholder="passphrase" required>
    <input type="submit">
  </form>
<?php if($submitted) { ?>
  <ul>
    <li>
      Persons: <span class="<?php echo status($persons); ?>"><?php echo status($persons); ?></span> (<a>+/-</a>)<br>
      <pre><?php print_r($persons); ?></pre>
    </li>
    <li>
      Course templates: <span class="<?php echo status($courseTemplates); ?>"><?php echo status($courseTemplates); ?></span>  (<a>+/-</a>)<br>
      <pre><?php print_r($courseTemplates); ?></pre>
    </li>
    <li>
      Course: <span class="<?php echo status($course); ?>"><?php echo status($course); ?></span> (<a>+/-</a>)<br>
      <pre><?php print_r($course); ?></pre>
    </li>
    <li>
      Subscription: <span class="<?php echo status($courseSubscription); ?>"><?php echo status($courseSubscription); ?></span> (<a>+/-</a>)<br>
      <pre><?php print_r($courseSubscription); ?></pre>
    </li>
  </ul>

  <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
  <script>
  $(function() {
    $("a").click(function () {
      $(this.parentElement).find("pre").slideToggle("slow");
    })
  });
  </script>
  <?php } ?>
</body>
</html>
