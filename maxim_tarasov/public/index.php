<?php
use Dotenv\Dotenv;
require __DIR__ . '/../vendor/autoload.php';
$dotenv = new Dotenv(__DIR__ . '/../');
$dotenv->load();
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
$user = new \SocialNetworksFacade\FakeUser();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!array_key_exists('user', $_SESSION)) {
    $_SESSION['user'] = $user;
}
$snf = new \SocialNetworksFacade\SocialNetworksFacade($_SESSION['user']);
echo "<pre>";
switch ($request_uri[0]) {
    // Home page
    case '/':
        echo "ok";
        break;
    // Facebook redirectUrl;
    case '/fb':
        $token = $snf->authorizeFacebook();
        $user->setFbToken($token);
        $_SESSION['user'] = $user;
        var_dump($snf->getProfiles());
        var_dump(array_map(function ($s) {
            return $s->getServiceName();
        }, $snf->getNotAuthorizedServices()));
        break;
    // LinkedIn redirectUrl;
    case '/li':
        $token = $snf->authorizeLinkedIn();
        $user->setLinkedInToken($token);
        $_SESSION['user'] = $user;
        var_dump($snf->getProfiles());
        var_dump(array_map(function ($s) {
            return $s->getServiceName();
        }, $snf->getNotAuthorizedServices()));
        break;
    case '/login':
        echo implode("<br>", array_map(function ($s) {
            return sprintf("<a href='%s''>login %s</a>", $s->getLoginUrl(), $s->getServiceName());
        }, $snf->getServices()));
        break;
    case '/not_auth':
        echo implode("<br>", array_map(function ($s) {
            return sprintf("%s: <a href='%s''>login</a><br>", $s->getServiceName(),$s->getLoginUrl());
        }, $snf->getNotAuthorizedServices()));
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        echo ":(";
        break;
}
echo "<br><br><br><hr>";
echo "<a href='/login'>login</a><br>";
echo "<a href='/not_auth'>not_auth</a><br>";