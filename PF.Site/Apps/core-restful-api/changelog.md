# RESTful API :: Change Log

## Version 4.2.4

### Information

- **Release Date:** July 03, 2020
- **Best Compatibility:** phpFox >= 4.7.0

### Fix issues

- Compatible with PHP 7.4
- Improve authentication by access token 

### Changed Files

- M Service/RestApiTransport.php
- M GrantType/MobilePublicLoginCredentials.php
- M vendor/bshaffer/oauth2-server-php/*
- M vendor/composer/*
- M vendor/autoload.php
- M Install.php

## Version 4.2.3

### Information

- **Release Date:** April 15, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Fix issues

- Sometimes can not register an account.

### Changed Files

- M Service/Storage.php


## Version 4.2.2

### Information

- **Release Date:** March 06, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Support Mobile Api App 1.3.0


## Version 4.2.1

### Information

- **Release Date:** January 17, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Support Mobile Api App 1.2.0


## Version 4.2.0

### Information

- **Release Date:** December 14, 2018
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Support Mobile Api App 1.1.0

### Changed Files

- D GrantType/MobilePublicLoginCredentials.php
- M Install.php
- M README.md
- M Service/RestApiTransport.php
- M Service/Storage.php
- D assets/autoload.css
- D assets/autoload.js
- M change-log.md
- D composer.lock
- M hooks/bundle__start.php
- M installer.php
- M start.php
- M vendor/bshaffer/oauth2-server-php/src/OAuth2/Storage/Pdo.php
- D vendor/bshaffer/oauth2-server-php/test/*
- D vendor/rainner/restful-php/test/*


## Version 4.1.3

### Information

- **Release Date:** January 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements

- Check compatible with phpFox core 4.6.0.
- Support renew refresh token.

### Changed Files
- M Install.php
- A README.md
- M Service/RestApiTransport.php
- M change-log.md
- M composer.lock
- A hooks/bundle__start.php
- M start.php
- M vendor/bshaffer/*
- M vendor/composer/LICENSE
- M vendor/composer/autoload_real.php
- M vendor/composer/installed.json
- M views/admincp.html
- M views/admincp_client.html


## Version 4.1.2

### Information

- **Release Date:** April 11, 2017
- **Best Compatibility:** phpFox >= 4.5.2

### Fixed Bugs

- Always requires param "state" when request Authorization Code
- Grant type "refresh_token" isn't supported

### Improvements

- Support get information of current logged user
- Support attach files/photos to blog content

### Changed Files

- PF.Site/Apps/core-restful-api/Install.php
- PF.Site/Apps/core-restful-api/Service/Storage.php
- PF.Site/Apps/core-restful-api/start.php
