# CAPP SDK for PHP

This is a PHP SDK to quickly build PHP webapps on the CAPP platform. It currently contains two classes. Both of them have a test page.

## API - capp_api_calls.php

Wrapper class for the CAPP API. Ask Defacto to whitelist your IP adress for API usage and to generate a passphrase. Instantiate class with API base URL, passphrase and username for the user to impersonate. See capp_api_test_page.php for usage example and test page.


## SSO - capp_sso.php

Class to offer Single Sign-On with CAPP. You need composer to resolve dependencies:

```bash
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```

Instantiate class with CAPP url and OAuth secret. Call the login method with a users username to redirect the user to CAPP. See capp_sso_test_page.php for usage example and test page. CAPP administrators can generate an OAuth secret in CAPP on the <capp-url\>/EditSystemAppSettings.aspx page.


## Maintainence

Adhere to [PSR-1](http://www.php-fig.org/psr/psr-1/) and [PSR-2](http://www.php-fig.org/psr/psr-2/) coding style.
