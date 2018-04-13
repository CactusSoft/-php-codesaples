<?php

namespace SocialNetworksFacade\Services;

use SocialNetworksFacade\Services\Facebook\FacebookUserInterface;
use SocialNetworksFacade\Services\LinkedIn\LinkedInUserInterface;

interface SocialNetworksUserInterface extends LinkedInUserInterface, FacebookUserInterface
{

}