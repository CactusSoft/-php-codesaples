<?php
namespace SocialNetworksFacade;
use Facebook\Authentication\AccessToken as FBToken;
use LinkedIn\AccessToken as LinkedInToken;
use SocialNetworksFacade\Services\SocialNetworksUserInterface;

class FakeUser implements SocialNetworksUserInterface
{
    private $fbToken, $linkedInToken;

    public function getFacebookToken(): ?FBToken
    {
        return $this->fbToken;
    }

    public function getLinkedInToken(): ?LinkedInToken
    {
        return $this->linkedInToken;
    }

    /**
     * @param mixed $fbToken
     */
    public function setFbToken($fbToken): void
    {
        $this->fbToken = $fbToken;
    }

    /**
     * @param mixed $linkedInToken
     */
    public function setLinkedInToken($linkedInToken): void
    {
        $this->linkedInToken = $linkedInToken;
    }


}