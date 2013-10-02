<?php
// Author: Defacto.nl
// Version: 1.3
// Required PHP version >= 5.4

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
  
  private function getToken() {
    $ch =  curl_init($this->BASE_URL . 'authorizationtokens/');
    
    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "{ 'username' : '$this->username', 'email' : '$this->email', 'password' : '$this->password', 'serverPassphrase' : '$this->passphrase' }",
      CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
      CURLOPT_SSL_VERIFYPEER => $this->sslsetting
    ));
    
    $result = curl_exec($ch);
    
    if (curl_errno($ch)) {
      echo curl_error($ch);
      exit;
    } else {
      $_SESSION['token'] = $result;
      curl_close($ch);
    }
  }
  
  private function checkTokenAvailable() {
	if (isset($_SESSION['token']) && $_SESSION['token'] != '') {
		return true;
	} else {
		$this->getToken();
		return true;
	}
	
	return false;
  }
  
  /**
   * Fetch all users
   *
   * persons/
   * token required
   */
  public function getAllPersons() {
	if ($this->checkTokenAvailable()) {
		$url = $this->BASE_URL . 'persons/';

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
   * coursetemplates/
   * no token required
   */
  public function getCourseTemplates($withCourses = false) {
    if ($withCourses) {
      $url = $this->BASE_URL . 'coursetemplates?withcourses=true';
    } else {
      $url = $this->BASE_URL . 'coursetemplates/';
    }

    $ch =  curl_init($url);
    
    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => array("Content-Type: application/json"),
      CURLOPT_SSL_VERIFYPEER => $this->sslsetting
    ));
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    $courseTemplates = json_decode($result);

    return $courseTemplates;
  }
  
  /**
   * Fetch user subscriptions
   *
   * subscriptions/
   * token required
   */
  public function getPersonSubscriptions() {
	if ($this->checkTokenAvailable()) {
		$url = $this->BASE_URL . 'subscriptions/';

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
   * Create person in CAPP
   * 
   * persons/
   * token required
   */
  public function createPerson($person) {
	if ($this->checkTokenAvailable()) {
		$person = json_encode($person);
				
		$url = $this->BASE_URL . 'persons/';

		$ch =  curl_init($url);
		
		curl_setopt_array($ch, array(
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_CUSTOMREQUEST => "PUT",
		  CURLOPT_POSTFIELDS => $person,
		  CURLOPT_HTTPHEADER => array('Content-Type: application/json', "Api-Token: ". (isset($_SESSION['token']) ? $_SESSION['token'] : '')),
		  CURLOPT_SSL_VERIFYPEER => $this->sslsetting
		));
		
		$result = curl_exec($ch);
		
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);
		
		if ($http_status == '200') {
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