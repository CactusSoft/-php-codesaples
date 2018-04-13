<?php

namespace SocialNetworksFacade\Services\LinkedIn;

use LinkedIn\AccessToken;
use LinkedIn\Client;
use LinkedIn\Exception;
use LinkedIn\Http\Method;
use LinkedIn\Scope;
use SocialNetworksFacade\Services\AbstractService;
use SocialNetworksFacade\Services\ServiceInterface;

class LinkedIn extends AbstractService
{
    CONST SERVICE_NAME = 'LinkedIn';
    private $scopes = [
        Scope::READ_BASIC_PROFILE,
        Scope::READ_EMAIL_ADDRESS,
    ];

    /**
     * LinkedIn constructor.
     * @param LinkedInUserInterface $user
     */
    public function __construct(LinkedInUserInterface $user)
    {
        $this->client = new Client(getenv('LINKEDIN_CLIENT_ID'), getenv('LINKEDIN_CLIENT_SECRET'));
        $this->client->setRedirectUrl(getenv('LINKEDIN_REDIRECT_URL'));
        if (!$this->isTokenExpired($user->getLinkedInToken())) {
            $this->client->setAccessToken($user->getLinkedInToken());
        }
    }

    private function isTokenExpired(AccessToken $token=null)
    {
        return is_null($token) || time() > $token->getExpiresAt();
    }

    public function isAuthorized()
    {
        $token = $this->client->getAccessToken();
        return (!is_null($token) && !$this->isTokenExpired($token));
    }

    public function getProfile()
    {
        return $this->client->get('people/~:(id,email-address,first-name,last-name)');
    }

    public function getAccessToken()
    {
        return $this->client->getAccessToken($this->getCode());
    }

    public function setAccessToken($token)
    {
        $this->client->setAccessToken($token);
    }

    public function getLoginUrl()
    {
        return $this->client->getLoginUrl($this->scopes);
    }
}