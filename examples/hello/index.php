<?php

include_once '../../Stooge.php';

use Stooge\Handler;
use Stooge\Request;
use Stooge\Response;
use Stooge\SetHeaderHandler;
use Stooge\StaticHandler;
use Stooge\Stooge;

(new Stooge())
    ->get('/hello', new StaticHandler(200, 'Hello stranger'))
    ->get('/hello/{name}', new class implements Handler
    {
        function handle(Request $request, Response $response)
        {
            $response->setEntity('Hello ' . $request->getPathParameter('name'));
        }
    })
    ->get('/hello/anybody/*', new StaticHandler(200, 'Hello anybody'))
    ->postHook(new SetHeaderHandler('X-Header', 'Look ma\' a header!'))
    ->foolAround();