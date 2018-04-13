<?php

namespace TwentyThree;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

/**
 * Class OAuthHelper for working with OAuth1 for TwentyThree videos service
 * see doc:
 * https://www.twentythree.net/api/oauth
 * https://oauth.net/core/1.0/#anchor26
 * @package TwentyThree
 */
class OAuthHelper
{
    // oAuth links for request_token, access_token, authorize
    const OAUTH_REALM = "http://api.visualplatform.net/";
    const OAUTH_DOMAIN = self::OAUTH_REALM . "oauth";
    const REQUEST_TOKEN_URL = self::OAUTH_DOMAIN . "/request_token";
    const AUTH_URL = self::OAUTH_DOMAIN . "/authorize";
    const ACCESS_TOKEN_URL = self::OAUTH_DOMAIN . "/access_token";

    //credentials
    protected $consumerKey = '';
    protected $consumerSecret = '';
    protected $domain = '';
    protected $oauthToken = '';
    protected $oauthTokenSecret = '';
    protected $accessToken = '';
    protected $accessTokenSecret = '';

    /**
     * guzzle client
     * @var Client
     */
    protected $client;
    protected $errors = [];

    /**
     * @return string
     */
    public function getOAuthToken()
    {
        return $this->oauthToken;
    }

    /**
     * @return string
     */
    public function getOAuthTokenSecret()
    {
        return $this->oauthTokenSecret;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getAccessTokenSecret()
    {
        return $this->accessTokenSecret;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * OAuthHelper constructor.
     * @param array $credentials
     * needed list of credentials. consumer_key and consumer_secret are required
     */
    public function __construct($credentials)
    {
        $this->client = new Client();
        if (!empty($credentials)) {
            $this->consumerKey =  Common::emptyDefault($credentials, 'consumer_key', '');
            $this->consumerSecret =  Common::emptyDefault($credentials, 'consumer_secret', '');
            $this->domain =  Common::emptyDefault($credentials, 'domain', '');
            $this->oauthToken =  Common::emptyDefault($credentials, 'oauth_token', '');
            $this->oauthTokenSecret =  Common::emptyDefault($credentials, 'oauth_token_secret', '');
            $this->accessToken =  Common::emptyDefault($credentials, 'access_token', '');
            $this->accessTokenSecret =  Common::emptyDefault($credentials, 'access_token_secret', '');
        }
    }

    /**
     * send oAuth request
     * @param $config
     * @param $url
     * @param string $type
     * @param array $options
     * @return array
     */
    private function send($config, $url, $type='get', $options = [])
    {
        $result = [];
        $stack = HandlerStack::create();
        $middleware = new Oauth1($config);
        $stack->push($middleware);
        $options = array_merge([
            'handler' => $stack,
            'auth' => 'oauth'
        ], $options);

        try {
            if ($type == 'post') {
                $response = $this->client->post($url, $options);
            } else {
                $response = $this->client->get($url, $options);
            }

            $contents = $response->getBody()->getContents();

            if (!empty($contents)) {
                $result['contents'] = $contents;
            } else {
                $result['error'] = 'no content';
            }
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * step 1
     * request_token request to get oauth token and secret
     * @param string $callback
     * @return bool
     */
    public function requestToken($callback = '')
    {
        $config = [
            'realm'           => self::OAUTH_REALM,
            'callback'        => $callback,
            'consumer_key'    => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret,
            'token_secret'    => ''
        ];
        $result = $this->send($config, self::REQUEST_TOKEN_URL);

        if (!empty($result['error'])) {
            $this->errors[] = $result['error'];
            return false;
        }

        parse_str($result['contents'], $parameters);
        if (!empty($parameters['oauth_token'])) {
            $this->oauthToken = Common::emptyDefault($parameters, 'oauth_token');
            $this->oauthTokenSecret = Common::emptyDefault($parameters, 'oauth_token_secret');
            return true;
        } else {
            $this->errors[] = $parameters['error'];
            return false;
        }
    }

    /**
     * step 2
     * go to authorize link with needed oauthToken to go to callback
     */
    public function authorize()
    {
        header('Location: ' . self::AUTH_URL . '?oauth_token=' . $this->oauthToken);
    }

    /**
     * step 3
     * access_token request to get needed access token and secret
     * it is requested on callback link (we are redirected to it on authorize step), which is set in request_token request
     * @param string $verifier
     * generated oauth_verifier
     * @return bool
     */
    public function accessToken($verifier)
    {
        $config = [
            'realm'           => self::OAUTH_REALM,
            'consumer_key'    => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret,
            'token'           => $this->oauthToken,
            'token_secret'    => $this->oauthTokenSecret,
            'verifier'        => $verifier
        ];
        $result = $this->send($config, self::ACCESS_TOKEN_URL);
        if (!empty($result['error'])) {
            $this->errors[] = $result['error'];
            return false;
        }

        parse_str($result['contents'], $parameters);
        if (!empty($parameters['oauth_token'])) {
            $this->accessToken = Common::emptyDefault($parameters, 'oauth_token');
            $this->accessTokenSecret = Common::emptyDefault($parameters, 'oauth_token_secret');
            $this->domain = Common::emptyDefault($parameters, 'url');
        }
        return true;
    }

    /**
     * step 4
     * post request to url with needed after getting of access token/secret
     * available actions in doc https://www.twentythree.net/api/
     * @param string $url
     * @param array $params
     * @return array
     */
    public function sendPostRequest($url, $params = [])
    {
        $config = [
            'realm'           => $this->domain,
            'consumer_key'    => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret,
            'token'           => $this->accessToken,
            'token_secret'    => $this->accessTokenSecret
        ];
        $options = [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Content-Length' => 10
            ],
            'form_params' => $params
        ];
        $result = $this->send($config, $this->domain . $url, 'post', $options);
        return $result;
    }

    /**
     * anonymous get request to url without access token/secret
     * @param $url
     * @param array $params
     * @return mixed
     */
    public function getAnonymousRequest($url, $params = [])
    {
        try {
            $options = !empty($params) ? ['query' => $params] : [];
            $response = $this->client->get($url, $options);
            $contents = $response->getBody()->getContents();

            if (!empty($contents)) {
                $result['contents'] = $contents;
            } else {
                $result['error'] = 'no content';
            }
        } catch (ClientException $e) {
            $result['error'] = $e->getMessage();
        }
        return $result;
    }
}