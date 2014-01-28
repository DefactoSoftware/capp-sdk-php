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

  //Retrieving data
  $persons = $api->getAllPersons();
  $courseTemplates = $api->getCourseTemplates(true);
  $courses = $api->getCourses();

  if(status($courses) == 'ok'):
    $course = $api->getCourse( $courses[0]->courseId);
  endif;
 
  //create person
  $cappUser["email"]      = $_POST['person_email'];
  $cappUser["loginName"]  = $_POST['person_email'];   //use e-mail as login name
  $cappUser["firstName"]  = $_POST['person_firstname'];
  $cappUser["lastName"]   = $_POST['person_lastname'];
  $createdPerson = $api->createPerson($cappUser);

if(status($createdPerson) == 'ok'):
    //create course subscription
    $courseSubscriptionData = [
        "traineePersonId" => $createdPerson->id,
        "courseId" => $course->courseId
    ];
    $createdSubscription = $api->createSubscription($courseSubscriptionData);
    $courseSubscription = $api->getSubscription($createdPerson->id,$course->courseId);

    //create course template subscription
    $courseTemplateSubscriptionData = [
        "traineePersonId" => $createdPerson->id,
        "courseTemplateId" => $courseTemplates[0]->courseTemplateId
    ];
    $createdCourseTemplateSubscription = $api->createCourseTemplateSubscription($courseTemplateSubscriptionData);
    $courseTemplateSubscription = $api->getCourseTemplateSubscription($createdPerson->id,$courseTemplates[0]->courseTemplateId);
  endif;
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
    <fieldset>
      <legend>API</legend>
      <input name="url" placeholder="API base url" type="url" required>
      <input name="username" placeholder="token username" required>
      <input name="passphrase" placeholder="token passphrase" required>
    </fieldset>
    <fieldset>
      <legend>Test person</legend>
      <input name="person_email" placeholder="e-mail" type="email">
      <input name="person_firstname" placeholder="first name">
      <input name="person_lastname" placeholder="last name">
    </fieldset>
    <input type="submit">
  </form>
<?php if($submitted) { ?>
  <ul>
    <li>
      getAllPersons: <span class="<?php echo status($persons); ?>"><?php echo status($persons); ?></span> (<a>+/-</a>)<br>
      <pre><?php print_r($persons); ?></pre>
    </li>
    <li>
      getCourseTemplates: <span class="<?php echo status($courseTemplates); ?>"><?php echo status($courseTemplates); ?></span>  (<a>+/-</a>)<br>
      <pre><?php print_r($courseTemplates); ?></pre>
    </li>

    <li>
      getCourses: <span class="<?php echo status($courses); ?>"><?php echo status($courses); ?></span> (<a>+/-</a>)<br>
      <pre><?php print_r($courses); ?></pre>
    </li>
    <li>
      getCourse:
<?php if(status($courses) == 'ok'): ?>
      <span class="<?php echo status($course); ?>"><?php echo status($course); ?></span> (<a>+/-</a>)<br>
      <pre><?php print_r($course); ?></pre>
<?php else: ?>
  no courses to test with
<?php endif; ?>
    </li>
    <li>
      createPerson: <span class="<?php echo status($createdPerson); ?>"><?php echo status($createdPerson); ?></span> (<a>+/-</a>)<br>
      <pre><?php print_r($createdPerson); ?></pre>
    </li>
<?php if(status($createdPerson) == 'ok'): ?>
  <li>
      createSubscription: <span class="<?php echo status($createdSubscription); ?>"><?php echo status($createdSubscription); ?></span> (<a>+/-</a>)<br>
      <pre><?php print_r($createdSubscription); ?></pre>
    </li> 
    <li>
      getSubscription: <span class="<?php echo status($courseSubscription); ?>"><?php echo status($courseSubscription); ?></span> (<a>+/-</a>)<br>
      <pre><?php print_r($courseSubscription); ?></pre>
    </li>

      <li>
      createCourseTemplateSubscription: <span class="<?php echo status($createdCourseTemplateSubscription); ?>"><?php echo status($createdCourseTemplateSubscription); ?></span> (<a>+/-</a>)<br>
      <pre><?php print_r($createdCourseTemplateSubscription); ?></pre>
    </li> 
    <li>
      getCourseTemplateSubscription: <span class="<?php echo status($courseTemplateSubscription); ?>"><?php echo status($courseTemplateSubscription); ?></span> (<a>+/-</a>)<br>
      <pre><?php print_r($courseTemplateSubscription); ?></pre>
    </li>
<?php else: ?>
    <li>
      could not perform subscription tests as no person was created
    </li>
<?php endif; ?>
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
