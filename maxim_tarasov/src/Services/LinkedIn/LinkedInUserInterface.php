<?php

namespace SocialNetworksFacade\Services\LinkedIn;

use LinkedIn\AccessToken;

interface LinkedInUserInterface
{
    public function getLinkedInToken(): ?AccessToken;
}