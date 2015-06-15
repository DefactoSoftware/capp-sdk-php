<?php
// Author: Defacto.nl
// Required PHP version >= 5.4
// CAPP API Version: v3

class CappApiCalls
{
  private $BASE_URL = '';
  private $username = '';
  private $email = '';
  private $password = '';
  private $passphrase = '';
  private $sslsetting = false;

  public function __construct($base_url, $user = array())
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $this->BASE_URL = $base_url;
    $this->setUser($user);
  }

  public function setUser(array $user)
  {
    if (isset($user['username'])) {
      $this->username = $user['username'];
    }
    if (isset($user['email'])) {
      $this->email = $user['email'];
    }
    if (isset($user['password'])) {
      $this->password = $user['password'];
    }
    if (isset($user['passphrase'])) {
      $this->passphrase = $user['passphrase'];
    }
  }

  public function __destruct()
  {
    if (isset($_SESSION['token'])) {
      unset($_SESSION['token']);
    }
  }

  /**
  * Get's a token for current user and stores it in session for later use.
  *
  * @return bool True if a token was obtained. False at failure.
  */
  private function getToken()
  {
    $ch = curl_init($this->BASE_URL . 'authorization_tokens');

    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "{ 'username' : '$this->username', 'password' : '$this->password', 'serverPassphrase' : '$this->passphrase' }",
      CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
      CURLOPT_SSL_VERIFYPEER => $this->sslsetting
    ));

    $result = curl_exec($ch);

    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
      echo curl_error($ch);
      exit;
    } else {
      curl_close($ch);
    }

    if ($http_status == '200' || $http_status == '201') {
      $results = json_decode($result);

      $_SESSION['token'] = $results->token;

      return true;
    } else {
      error_log("Can't get token. Status: ".$http_status);

      return false;
    }
  }

  /**
  * Check if a token is available. Tries to obtain one if not.
  *
  * @return bool True if a token is available. False if not and in can't obtain one
  */
  private function isTokenAvailable()
  {
    if (isset($_SESSION['token']) && $_SESSION['token'] != '') {
      return true;
    } elseif ($this->getToken()) {
      return true;
    } else {
      return false;
    }
  }

  private function getRequestWithToken($url, $method = "GET", $data = [])
  {
    if ($this->isTokenAvailable()) {
      $ch =  curl_init($url);

      curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Api-Token: ". (isset($_SESSION['token']) ? $_SESSION['token'] : '')),
        CURLOPT_SSL_VERIFYPEER => $this->sslsetting
      ));

      $result = curl_exec($ch);
      $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($http_status == '200' || $http_status == '201') {
        $result = json_decode($result);

        return $result;
      } elseif ($http_status == '401' && $this->getToken()) {
        $this->getRequestWithToken($url, $method, $data);
      } else {
        return array('status' => $http_status);
      }
    } else {
      return array('status' => '500');
    }
  }

  /**
   * Fetch all users
   * token required
   *
   */
  public function getPersons()
  {
    $url = $this->BASE_URL . 'persons';

    $persons = $this->getRequestWithToken($url);

    return $persons;
  }

    /**
   * Get a person by email
   */
  public function getPersonByEmail($email)
  {
    $url = $this->BASE_URL . 'persons?email=' . $email;

    return $this->getRequestWithToken($url);
  }

      /**
    * Get a person by username
    * token required
    */
    public function getPersonByUserName($uid)
    {
      $url = $this->BASE_URL . 'persons?username='.$uid;

      return $this->getRequestWithToken($url);
    }

      /**
   * Create person object
   *
   * token required
   */
  public function createPerson($person)
  {
      $person = json_encode($person);
      $url = $this->BASE_URL . 'persons';

      $result = $this->getRequestWithToken($url, "POST", $person);

      return $result;
  }

  /**
   * Get all course templates
   *
   * No Token required
   */
  public function getCourseTemplates($withCourses = false)
  {
    if ($withCourses) {
      $url = $this->BASE_URL . 'course_templates?withcourses=true';
    } else {
      $url = $this->BASE_URL . 'course_templates';
    }

    return $this->getRequestWithToken($url);
  }

  /**
  * Get courses
  */
  public function getCourses()
  {
    $url = $this->BASE_URL . 'courses';

    return $this->getRequestWithToken($url);
  }

    /**
   * Get course
   */
  public function getCourse($id)
  {
    //check parameter
    if (!$id > 0) {
            return array('status' => '422');
    }

    //good to go
    $url = $this->BASE_URL . 'courses/'.$id;

    $course = $this->getRequestWithToken($url);

    return $course;

  }

  /**
   * Fetch user subscription
   *
   */
  public function getSubscription($personID, $courseID)
  {
    if ($personID > 0 && $courseID > 0) {
      $query_string = http_build_query(['personid'=>$personID, 'courseid'=>$courseID]);
      $url = $this->BASE_URL . 'subscriptions?'. $query_string;

      $subscription = $this->getRequestWithToken($url);

      return $subscription;
    } else {
      return array('status' => '422');
    }
  }

  /**
   * Create subscription
   *
   * token required
   */
  public function createSubscription($subscription)
  {
    $data = json_encode($subscription);
    $url = $this->BASE_URL . 'subscriptions';
    $result = $this->getRequestWithToken($url, "POST", $data);

    return $result;
  }

  /**
  * Create a course template subscription
  * Course template subscriptions are a trainee's interest in a course.
  * They're usally done when there are no planned course dates that fits the trainees agenda.
  * token required
  */
  public function createCourseTemplateSubscription($courseTemplateSubscription)
  {
      $courseTemplateSubscription = json_encode($courseTemplateSubscription);
      $url = $this->BASE_URL . 'course_template_subscriptions';

      return $this->getRequestWithToken($url, "POST", $courseTemplateSubscription);
  }

  /**
  * Method createELearningSubscription
  * 
  * Create a subscription for an e-learning only course. 
  * To successfully create an elearning subscription a courseTemplateId is mandatory.
  * 
  * @param  $courseTemplateSubscription (array) (minimal keys required: courseTemplateId) 
  * @throws none
  * @return stdObject with result 
  */  
  public function createELearningSubscription(array $courseTemplateSubscription)
  {
      $courseTemplateSubscription = json_encode($courseTemplateSubscription);
      $url = $this->BASE_URL . 'elearning_subscriptions';

      return $this->getRequestWithToken($url, "POST", $courseTemplateSubscription);
  }

  /**
  * Get a course template subscription for a user on courstemplate
  *
  */
  public function getCourseTemplateSubscription($personId, $courseTemplateId)
  {
    if (!($personId > 0 && $courseTemplateId > 0)) {
      return array('status' => 'Function input incorrect.');
    }

    $query_string = http_build_query(array('personid'=>$personId, 'coursetemplateid'=>$courseTemplateId));
    $url = $this->BASE_URL . 'course_template_subscriptions?'. $query_string;

    return $this->getRequestWithToken($url);
  }

  /**
  * Fetch user subscription
  *
  */
  public function getMySubscriptions()
  {
    $url = $this->BASE_URL . 'subscriptions/me';

    return $this->getRequestWithToken($url);
   }

  /**
  * Fetch user template subscription of person
  *
  */
  public function getMyTemplateSubscriptions()
  {
    $url = $this->BASE_URL . 'course_template_subscriptions/me';

    return $this->getRequestWithToken($url);
  }
}
