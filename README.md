# Code snippets for customer reviews

# Mikhail Dyuldya
A module to create a data feeds from given dataset retrieved from any source using specified mapping, different data formats and output methods.
Both new output methods and new data formats are easy to add by extending the base outputter and formatter classes respectively. 
It's also handy to manipulate the mapping using a list of settings which makes it possible to process the raw data via callback functions 

# Daria Ermakova
Classes to work with TwentyThree videos services via OAuth1 protocol. It is example of sending request to TwentyThree API.

Example of authorizing:
we need to request auth token and go to authorize page with this token
```php
$shop_url = "test.com";
$client = new \TwentyThree\TwentyThreeClient($shop_id, $shop_url);
$client->requestTokenAndGetAuthToken();
```

When we were logged on authorize page, we will be redirected on shop page with oauth_verifier. It helps to generate access and secret tokens
```php
$shop_url = "test.com";
$client = new \TwentyThree\TwentyThreeClient($shop_id, $shop_url);
$errors = $client->accessToken($_GET['oauth_verifier']);
```
Results of every auth step are saved in database (Сonsumer data, temporary tokens and access tokens)

Example of classes using:
```php
$video->video_origin_shop_id = 1;
$video->video_link = '1234567';
$client = new \TwentyThree\TwentyThreeClient($video->video_origin_shop_id);
$videoData = $client->getVideoById($video->video_link);
```
--------------------
OAuhtHelper class describes step working with authorization and sending requests by OAuth1
TwentyThree\TwentyThreeClient class uses OAuth class for aggregating and sending requests to video service
TwentyThreeHelper - just helper for getting of additional data for TwentyThreeClient

# Nikolai Koleda
Symfony based implementations: <br />
Small service to send emails to customers with a specific mail template<br />
Command/query class to get list of reservations according to parameters in request

# Maxim Tarasov
From-scratch task:
add Symfony scoped wrapper for interacting with multiple social networks via one language.<br /> As a reference, LinkedIn 
and Facebook networks implementations were added, which implement ServiceInterface which interacts with SocialNetworksFacade


