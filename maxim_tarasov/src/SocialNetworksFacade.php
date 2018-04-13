<?php

namespace SocialNetworksFacade;


use SocialNetworksFacade\Services\LinkedIn\LinkedIn;
use SocialNetworksFacade\Services\Facebook\Facebook;
use SocialNetworksFacade\Services\SocialNetworksUserInterface;
use SocialNetworksFacade\Services\ServiceInterface;

class SocialNetworksFacade
{
    private $services = [];


    public function __construct(SocialNetworksUserInterface $user)
    {
        $this->services[LinkedIn::SERVICE_NAME] = new LinkedIn($user);
        $this->services[Facebook::SERVICE_NAME] = new Facebook($user);
    }

    public function getServices()
    {
        return $this->services;
    }

    public function getProfiles()
    {
        $profiles = [];
        /**
         * @var ServiceInterface $service ;
         */
        foreach ($this->services as $service) {
            if ($service->isAuthorized()) {
                $profiles[$service->getServiceName()] = $service->getProfile();
            }
        }
        return $profiles;
    }

    public function getNotAuthorizedServices()
    {
        return array_filter($this->services, function ($s) {
            return !$s->isAuthorized();
        });
    }

    public function authorizeFacebook()
    {
        $this->authorizeCustomService(Facebook::SERVICE_NAME);
    }

    public function authorizeLinkedIn()
    {
        return $this->authorizeCustomService(LinkedIn::SERVICE_NAME);
    }

    private function authorizeCustomService($serviceName)
    {
        return $this->services[$serviceName]->getAccessToken();
    }
}