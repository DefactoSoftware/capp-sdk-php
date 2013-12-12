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
    
    if ($http_status == '200') {
		  $results = json_decode($result);
		  
		  $_SESSION['token'] = $results->token;
		} else {
		  return array('status' => $http_status);
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
   */
  public function getAllPersons() {
  	if ($this->checkTokenAvailable()) {
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
   * Fetch user subscription
   *
   */
  public function getSubscription($personID, $courseID) {
    if ($personID > 0 && $courseID > 0) {
    	if ($this->checkTokenAvailable()) {
    		$url = $this->BASE_URL . 'subscriptions';
    
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
  	if ($this->checkTokenAvailable()) {
  		$subscription = json_encode($subscription);
  				
  		$url = $this->BASE_URL . 'persons';
  
  		$ch =  curl_init($url);
  		
  		curl_setopt_array($ch, array(
  		  CURLOPT_RETURNTRANSFER => true,
  		  CURLOPT_CUSTOMREQUEST => "PUT",
  		  CURLOPT_POSTFIELDS => $subscription,
  		  CURLOPT_HTTPHEADER => array('Content-Type: application/json', "Api-Token: ". (isset($_SESSION['token']) ? $_SESSION['token'] : '')),
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
  }
  
  /**
   * Create person object
   * 
   * token required
   */
  public function createPerson($person) {
  	if ($this->checkTokenAvailable()) {
  		$person = json_encode($person);
  				
  		$url = $this->BASE_URL . 'persons';
  
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