<?php

include_once '../../Stooge.php';

use Stooge\Cookie;
use Stooge\Handler;
use Stooge\PassHandler;
use Stooge\Request;
use Stooge\Response;
use Stooge\StaticHandler;
use Stooge\Stooge;

(new Stooge())
    ->preHook(new class implements Handler
    {
        function handle(Request $request, Response $response)
        {
            if ($request->isRequestPathStartWith('/secret') &&
                $request->getSessionParameter('StoogeSession') !== 'supersecret') {
                $response->setStatusCode(403);
                $request->setStickyHandler(new PassHandler());
            }
        }
    })
    ->get('/', new StaticHandler(200, 'Home, not secret.'))
    ->get('/notsecret', new StaticHandler(200, 'Not secret at all.'))
    ->get('/secret', new StaticHandler(200, 'Super secret.'))
    ->post('/login', new class implements Handler
    {
        function handle(Request $request, Response $response)
        {
            if ($this->isSecretUser($request->getBodyAsArray())) {
                $response->setCookie(new Cookie('StoogeSession', 'supersecret'));
            } else {
                $response->setStatusCode(403);
            }
        }

        private function isSecretUser(array $requestBodyParameters): bool
        {
            return isset($requestBodyParameters['login']) &&
                $requestBodyParameters['login'] === 'john' &&
                isset($requestBodyParameters['password']) &&
                $requestBodyParameters['password'] === 'doe';
        }
    })
    ->get('/logout', new class implements Handler
    {
        function handle(Request $request, Response $response)
        {
            $response->setCookie(Cookie::drop('StoogeSession'))
                ->setStatusCode(200);
        }
    })
    ->foolAround();