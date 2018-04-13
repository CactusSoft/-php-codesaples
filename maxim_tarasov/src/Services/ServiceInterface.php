<?php

namespace SocialNetworksFacade\Services;
interface ServiceInterface
{
    public function getServiceName();
    public function isAuthorized();
    public function getLoginUrl();
    public function getAccessToken();
    public function setAccessToken($token);
    public function getProfile();
}