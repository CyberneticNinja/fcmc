<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes


$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'home.twig', array(
        "title" => "Home"
    ));
});
$app->post('/', function ($request, $response, $args) {

});
$app->get('/about', function ($request, $response, $args) {
    return $this->view->render($response, 'about.twig', array(
        "title" => "About"
    ));
});
$app->get('/services', function ($request, $response, $args) {
    return $this->view->render($response, 'services.twig', array(
        "title" => "Services"
    ));
});
$app->get('/faq', function ($request, $response, $args) {
    return $this->view->render($response, 'faq.twig', array(
        "title" => "FAQ"
    ));
});