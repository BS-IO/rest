Rest Plugin
====

# A restful plugin for CakePHP (2.3)

The Rest plugin is my first attempt at creating a restful api framework for CakePHP 2.3 according to some best practices as defined by Apigee (Read more on that here: http://blog.apigee.com/detail/announcement_new_ebook_on_web_api_design/):

1) it uses nouns

2) it handles errors

3) complexity is swept under the ?

4) it is versionable

5) it allows for partial responses

6) it supports xml and json

7) it has application based key/secret authentication

8) it is debuggable

I tried to keep things as simple as possible, so everyone can use this plugin on top of their existing system. Please note this is an alpha version, so it is developed quite loose & I haven't spent much time optimizing stuff. If you find things that need improvement, please let me know.

## Basics

The architecture of the system is straightforward.
* RestController: containing a dispatch function that calls the right version of the api and does some errorhandling.
* Api01Controller: version 0.1 of the actual API-controller. Here you can add all the functions you need. I've added two example functions. Note that the method for the function is added after the function name, using an underscore and the method (_get, _put, _update, _delete). 
* To enable versioning of you api, just create Api02Controller for version 0.2, etc. 

## Things NOT included
- administration views
- tests


## Installation

Just copy the rest plugin to your plugin folder and add the following to your __app/Config/bootstrap.php__
```php
<?php 
CakePlugin::load('Rest', array('bootstrap' => true, 'routes' => true));
?>
```

Also, be sure to run the scheme and create a test application.

## Config

Basic authentication is turned off by default. You can turn it on in the bootstrap by setting requireSignature to *true*.

For easy testing, I'm used to defining environment settings in my config (e.g. define('ENV_DEV', 'localhost');). This can be done in the bootstrap as well. As I have built in security that never shows the call signature on a live server, but does on a test or staging server, please enter the appropriate values in the bootstrap file. If you'd like, you can remove the specific token info by commenting out lines 127 & 128 in RestController.php.

## Authentication

if 'requireSignature' is set to TRUE, all calls need to be signed with an (application-)key and a secret. This secret needs to generated clientside by the following function: hash_hmac("sha256", urlencode($requestUrl), $secret);

### Example: 

http://example.com/rest/0.1/info.json?key=$key&signature=$signature

$key = 'applicationKey';

$secret = 'applicationSecret';

$requestedUrl = 'http://example.com/rest/0.1/info.json'

$signature = hash_hmac("sha256", urlencode($requestUrl), $secret);

## Example usage

The framework is built up around a simple routing system: *version/noun.output

http://example.com/rest/0.1/object.json -> output in json

http://example.com/rest/0.1/object/1.xml -> output of record 1 in xml

Functionality is determined by the used http_method: GET/POST/PUT/DELETE

Also, there are some basic parameters you can use in the url (I know, this is not truly restful) for your convenience:

* ?debug: returns debug information
* ?suppress_response_code: always returns 200/OK header
* ?key=xxxx&signature=yyyyy: a signed request
* ?fields=id,name,xxxx: a partial selection of the fields you are requesting

## License

@license MIT License (http://www.opensource.org/licenses/mit-license.php)