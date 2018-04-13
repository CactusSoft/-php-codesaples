<?php

namespace SocialNetworksFacade\Services\Facebook;


use Facebook\Authentication\AccessToken;

interface FacebookUserInterface
{
    public function getFacebookToken(): ?AccessToken;
}