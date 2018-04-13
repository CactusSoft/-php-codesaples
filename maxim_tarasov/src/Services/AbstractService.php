<?php

namespace SocialNetworksFacade\Services;


abstract class AbstractService implements ServiceInterface
{
    const SERVICE_NAME = '';
    protected $client;

    protected function getCode()
    {
        return $this->getInput('code');
    }

    protected function getInput($key)
    {
        if (isset($_GET[$key])) {
            return isset($_GET[$key]) ? $_GET[$key] : null;
        }
    }

    public function getServiceName()
    {
        return $this::SERVICE_NAME;
    }

    abstract public function getProfile();

    abstract public function getAccessToken();

    abstract public function setAccessToken($token);

    abstract public function getLoginUrl();

    abstract public function isAuthorized();


}