<?php

namespace TwentyThree;

use GuzzleHttp\Exception\ServerException;
use Helpers\XmlHelper;

/**
 * steps of authorization in doc https://www.twentythree.net/api/oauth
 * Class TwentyThree
 * @package TwentyThree
 */
class TwentyThreeClient
{
    const AUTH_CALLBACK = "/23_access_token.php";
    const GET_VIDEOS_LIST_URL = "/api/photo/list";

    const BASE_HOST = 'videomarketingplatform.co';
    const BASE_URL = 'http://test-domain.' . self::BASE_HOST;

    protected $shopId;
    protected $shopUrl;

    /**
     * @var OAuthHelper
     */
    protected $oauth;

    /**
     * TwentyThree constructor.
     * @param int $shopId
     * shop id of need shop
     * @param string $shopUrl
     * for callback link
     */
    public function __construct($shopId, $shopUrl = '')
    {
        $this->shopId = $shopId;
        $this->shopUrl = $shopUrl;
        $credentials = TwentyThreeHelper::getCredentialsByShop($shopId);
        $credentials['domain'] = Common::emptyDefault($credentials, 'videos_domain', '');
        $this->oauth = new OAuthHelper($credentials);
    }

    /**
     * request tokens, save them and go to authorize page
     */
    public function requestTokenAndGetAuthToken()
    {
        $domainPrefix = !empty(SHOP_DOMAIN_PREFIX) ? SHOP_DOMAIN_PREFIX  . '.' : '';
        $callback = 'http://' . $domainPrefix . $this->shopUrl . self::AUTH_CALLBACK;
        if(!$this->oauth->requestToken($callback)) {
            return $this->oauth->getErrors();
        }

        //save oauth tokens in configs
        TwentyThreeHelper::updateShopCredentials(
            $this->shopId,
            [
                'oauth_token' => $this->oauth->getOAuthToken(),
                'oauth_token_secret' => $this->oauth->getOAuthTokenSecret()
            ]
        );
        $this->oauth->authorize();
        return [];
    }

    /**
     * function for callback page from request_token params
     * @param string $verifier
     * oauth_verifier on callback page
     * @return array $errors
     */
    public function accessToken($verifier)
    {
        $errors = [];
        if ($this->oauth->accessToken($verifier)) {
            //save domain access token and token_secret and remove oauth tokens
            TwentyThreeHelper::updateShopCredentials(
                $this->shopId,
                [
                    'access_token' => $this->oauth->getAccessToken(),
                    'access_token_secret' => $this->oauth->getAccessTokenSecret(),
                    'videos_domain' => $this->oauth->getDomain()
                ]
            );
        } else {
            $errors = $this->oauth->getErrors();
        }
        return $errors;
    }

    /**
     * get 23 video by id
     * @param int $id
     * @return array
     * video data and error data (if it exists)
     */
    public function getVideoById($id)
    {
        $return = [
            'error' => '',
            'data'  => ''
        ];

        $params = [
            'photo_id' => $id
        ];
        try {
            if (!empty($this->shopId)) {
                $result = $this->oauth->getAnonymousRequest(self::BASE_URL . self::GET_VIDEOS_LIST_URL, $params);
            } else {
                $result = $this->oauth->sendPostRequest(self::GET_VIDEOS_LIST_URL, $params);
            }
        } catch(ServerException $ex){
            \Logger::log('23 server failed to respond properly on url: '.self::GET_VIDEOS_LIST_URL,\Psr\Log\LogLevel::ERROR,'23VIDEO',['params'=>$params]);
        }

        if (!empty($result['contents'])) {
            $xml = new \SimpleXMLElement($result['contents']);
            $data = XmlHelper::XmlToArray($xml, ['attributePrefix' => '']);
            if (!empty($data['response']['photo'])) {
                $return['data'] = $data['response']['photo'];
            } elseif(!empty($data['response']['status']) && $data['response']['status'] == 'error') {
                $return['error'] = $data['response']['message'];
            } else {
                $return['error'] = 'photo not found';
            }
        } else {
            $return['error'] = $result['error'];
        }
        return $return;
    }

    /**
     * base domain
     * @return string
     */
    public function getDomain()
    {
        return $this->oauth->getDomain();

    }
}