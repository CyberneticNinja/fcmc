<?php
/**
 * CRSF Middleware
 *
 * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
 * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
 * @param  callable                                 $next     Next middleware
 *
 * @return \Psr\Http\Message\ResponseInterface
 */

$csrf = function($request, $response, $next) {
    $formValue = $request->getParsedBody()['crsf'];
    $sessionValue = $_SESSION['crsf'];

    if($formValue == $sessionValue)
    {
        $response = $next($request, $response);
        return $response;
    }
    else
    {
        $response = $response->withRedirect('/');
        return $response;
    }
};