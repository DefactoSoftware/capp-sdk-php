<?php
// Author: Defacto.nl
// Required PHP version >= 5.4
// Version: v2b

class CappApiCalls {
  private $BASE_URL = '';
  private $username = '';
  private $email = '';
  private $password = '';
  private $passphrase = '';
  private $sslsetting = false;

  public function __construct($base_url, $user = array()) {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $this->BASE_URL = $base_url;
    $this->setUser($user);
  }

  public function setUser(array $user){
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

  public function __destruct() {
    if (isset($_SESSION['token'])) {
      unset($_SESSION['token']);
    }
  }

  /**
   * Get's a token for current user and stores it in session for later use.
   *
   * @return bool True if a token was obtained. False at failure.
   */
  private function getToken() {
    $ch =  curl_init($this->BASE_URL . 'authorization_tokens');

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
  private function isTokenAvailable() {
    if (isset($_SESSION['token']) && $_SESSION['token'] != '') {
      return true;
    } elseif($this->getToken()) {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Fetch all users
   *
   */
  public function getAllPersons() {
    if ($this->isTokenAvailable()) {
      $url = $this->BASE_URL . 'persons';

      $ch =  curl_init($url);

      curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Api-Token: ". (isset($_SESSION['token']) ? $_SESSION['token'] : '')),
        CURLOPT_SSL_VERIFYPEER => $this->sslsetting
      ));

      $result = curl_exec($ch);
      $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($http_status == '200') {
        $persons = json_decode($result);

        return $persons;
      } else {
        return array('status' => $http_status);
      }
    } else {
      return array('status' => 'No token available');
    }
  }

  /**
   * Get all course templates
   *
   * No Token required
   */
  public function getCourseTemplates($withCourses = false) {
    if ($withCourses) {
      $url = $this->BASE_URL . 'course_templates?withcourses=true';
    } else {
      $url = $this->BASE_URL . 'course_templates';
    }

    $ch =  curl_init($url);

    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => array("Content-Type: application/json"),
      CURLOPT_SSL_VERIFYPEER => $this->sslsetting
    ));

    $result = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status == '200') {
      $courseTemplates = json_decode($result);

      return $courseTemplates;
    } else {
      return array('status' => $http_status);
    }
  }

    /**
   * Get course
   */
  public function getCourse($id) {

    //check parameter
    if(!$id > 0) {
            return array('status' => 'Invalid value for parameter id.');
    }

    //check token
    if (!$this->isTokenAvailable()) {
        return array('status' => 'No token available');
    }

    //good to go
      $url = $this->BASE_URL . 'courses/'.$id;

      $ch =  curl_init($url);

      curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Api-Token: ". (isset($_SESSION['token']) ? $_SESSION['token'] : '')),
        CURLOPT_SSL_VERIFYPEER => $this->sslsetting
      ));

      $result = curl_exec($ch);
      $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($http_status == '200') {
        $course = json_decode($result);

        return $course;
      } else {
        return array('status' => $http_status);
      }

  }

  /**
   * Fetch user subscription
   *
   */
  public function getSubscription($personID, $courseID) {
    if ($personID > 0 && $courseID > 0) {
      if ($this->isTokenAvailable()) {
        $query_string = http_build_query(['personid'=>$personID, 'courseid'=>$courseID]);
        $url = $this->BASE_URL . 'subscriptions?'. $query_string;

        $ch =  curl_init($url);

        curl_setopt_array($ch, array(
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HTTPHEADER => array("Content-Type: application/json", "Api-Token: ". (isset($_SESSION['token']) ? $_SESSION['token'] : '')),
          CURLOPT_SSL_VERIFYPEER => $this->sslsetting
        ));

        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status == '200') {
          $subscription = json_decode($result);

          return $subscription;
        } else {
          return array('status' => $http_status);
        }
      } else {
        return array('status' => 'No token available');
      }
    } else {
      return array('status' => 'Function input incorrect.');
    }
  }

  /**
   * Create subscription
   *
   * token required
   */
  public function createSubscription($subscription) {
    if ($this->isTokenAvailable()) {
      $subscription = json_encode($subscription);

      $url = $this->BASE_URL . 'subscriptions';

      $ch =  curl_init($url);

      curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $subscription,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json', "Api-Token: ". (isset($_SESSION['token']) ? $_SESSION['token'] : '')),
        CURLOPT_SSL_VERIFYPEER => $this->sslsetting
      ));

      $result = curl_exec($ch);
      $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if (strncmp($http_status, '201', 2) === 0) {
        $subscription = json_decode($result);
        return $subscription;
      } else {
        return array('status' => $http_status);
      }
    } else {
      return array('status' => 'No token available');
    }
  }

  /**
   * Create person object
   *
   * token required
   */
  public function createPerson($person) {
    if ($this->isTokenAvailable()) {
      $person = json_encode($person);

      $url = $this->BASE_URL . 'persons';

      $ch =  curl_init($url);

      curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $person,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json', "Api-Token: ". (isset($_SESSION['token']) ? $_SESSION['token'] : '')),
        CURLOPT_SSL_VERIFYPEER => $this->sslsetting
      ));

      $result = curl_exec($ch);
      $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if (strncmp($http_status, '201', 2) === 0) {
        $person = json_decode($result);
        return $person;
      } else {
        return array('status' => $http_status);
      }
    } else {
      return array('status' => 'No token available');
    }
  }
}

?>
