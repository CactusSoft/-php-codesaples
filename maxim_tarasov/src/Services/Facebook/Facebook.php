<?php

namespace SocialNetworksFacade\Services\Facebook;


use SocialNetworksFacade\Services\AbstractService;

class Facebook extends AbstractService
{

    const SERVICE_NAME = 'facebook';
    private $redirectUrl;
    private $scopes = [
        'email',
        'public_profile'
    ];

    public function __construct(FacebookUserInterface $user)
    {
        $config = [
            'app_id' => getenv('FB_APP_ID'),
            'app_secret' => getenv('FB_APP_SECRET'),
            'default_graph_version' => 'v2.10',
        ];
        $this->redirectUrl = getenv('FB_REDIRECT_URL');

        if (!is_null($token = $user->getFacebookToken()) && !$token->isExpired()) {
            $config['default_access_token'] = $user->getFacebookToken()->getValue();
        }
        $this->client = new \Facebook\Facebook($config);
    }

    public function isAuthorized()
    {
        $token = $this->getDefaultAccessToken();
        return (!is_null($token) && !$token->isExpired());
    }

    public function setAccessToken($token)
    {
        $this->client->setDefaultAccessToken($token);
    }

    public function getDefaultAccessToken()
    {
        return $this->client->getDefaultAccessToken();
    }

    public function getAccessToken()
    {
        $this->setAccessToken($this->client->getOAuth2Client()->getAccessTokenFromCode($this->getCode(), $this->redirectUrl));
        return $this->getDefaultAccessToken();
    }

    public function getLoginUrl()
    {
        return $this->client->getRedirectLoginHelper()->getLoginUrl(
            $this->redirectUrl,
            $this->scopes
        );
    }

    public function getProfile()
    {
        return $this->client->get('/me');
    }
}
