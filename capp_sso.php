<?php
require_once 'vendor/autoload.php';
require_once 'service/Capp.php';

use OAuth\OAuth1\Service\Capp;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\Uri;

class CappSso
{
  private $cappService;

  public function __construct($base_url, $secret)
  {
    /**
     * Create a new instance of the URI class with the current URI, stripping the query string
     */
    $uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
    $currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
    $currentUri->setQuery('');

    /**
     * @var array A list of all the credentials to be used by the different services in the examples
     */
    $servicesCredentials = array(
        'capp' => array(
            'key'       => 'CappSSO',
            'secret'    => $secret,
        ),
    );

    /** @var $serviceFactory \OAuth\ServiceFactory An OAuth service factory. */
    $serviceFactory = new \OAuth\ServiceFactory();

    // We need to use a persistent storage to save the token, because oauth1 requires the token secret received before'
    // the redirect (request token request) in the access token request.
    $storage = new Session();

    // Setup the credentials for the requests
    $credentials = new Credentials(
        $servicesCredentials['capp']['key'],
        $servicesCredentials['capp']['secret'],
        $currentUri->getAbsoluteUri()
    );

    $serviceFactory->registerService('Capp','OAuth\\OAuth1\\Service\\Capp');

    // Instantiate the Capp service using the credentials, http client and storage mechanism for the token
    /** @var $cappService Capp */
    $this->cappService = $serviceFactory->createService('Capp', $credentials, $storage, array(), new Uri($base_url));

  }

  public function login($username, $options=array())
  {             
      $params = ['AccountNameProperty' => $username];
   
      if(!empty($options)){
           $params = array_merge($params, $options);            
      }  
      
      //get request token
      $token = $this->cappService->requestRequestToken( $params );
  
      //redirect to CAPP with request token
      $url = $this->cappService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
      header('Location: ' . $url);
  }
}
