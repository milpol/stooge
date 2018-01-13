<?php

namespace Stooge;

use Exception;
use LogicException;
use RuntimeException;

interface Handler
{
    function handle(Request $request, Response $response);
}

class NullHandler implements Handler
{
    function handle(Request $request, Response $response)
    {
        throw new LogicException('Null pointer exception.');
    }
}

class PassHandler implements Handler
{
    function handle(Request $request, Response $response)
    {
    }
}

class SetHeaderHandler implements Handler
{
    private $name;
    private $value;

    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    function handle(Request $request, Response $response)
    {
        $response->setHeader($this->name, $this->value);
    }
}

class StaticHandler implements Handler
{
    private $statusCode;
    private $entity;

    public function __construct($statusCode = 200, $entity = '')
    {
        $this->statusCode = $statusCode;
        $this->entity = $entity;
    }

    function handle(Request $request, Response $response)
    {
        $response
            ->setStatusCode($this->statusCode)
            ->setEntity($this->entity);
    }
}

class Request
{
    private $time;
    private $scheme;
    private $method;
    private $rootPath = null;
    private $requestUri = null;
    private $headers = [];
    private $queryParameters = [];
    private $pathParameters = [];
    private $sessionParameters = [];
    private $cookieParameters = [];
    private $body = '';
    private $stickyHandler;

    public function __construct()
    {
        $this->stickyHandler = new NullHandler();
    }

    public function setTime(int $time): Request
    {
        $this->time = $time;
        return $this;
    }

    public function setScheme(string $scheme): Request
    {
        $this->scheme = $scheme;
        return $this;
    }

    public function setMethod(string $method): Request
    {
        $this->method = strtoupper($method);
        return $this;
    }

    public function setRootPath(string $rootPath): Request
    {
        $this->rootPath = $rootPath;
        return $this;
    }

    public function setRequestUri(string $requestUri): Request
    {
        $this->requestUri = $requestUri;
        return $this;
    }

    public function setHeaders(array $headers): Request
    {
        $this->headers = $headers;
        return $this;
    }

    public function setQueryParameters(array $queryParameters): Request
    {
        $this->queryParameters = $queryParameters;
        return $this;
    }

    public function setPathParameters(array $pathParameters): Request
    {
        $this->pathParameters = $pathParameters;
        return $this;
    }

    public function setSessionParameters(array $sessionParameters): Request
    {
        $this->sessionParameters = $sessionParameters;
        return $this;
    }

    public function setCookieParameters(array $cookieParameters): Request
    {
        $this->cookieParameters = $cookieParameters;
        return $this;
    }

    public function setBody(string $body, array $bodyArray): Request
    {
        if (!empty($body)) {
            $this->body = $body;
        } else if (!empty($bodyArray)) {
            $this->body = serialize($bodyArray);
        }
        return $this;
    }

    public function setStickyHandler(Handler $stickyHandler): Request
    {
        $this->stickyHandler = $stickyHandler;
        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function isHeader($name, $value): bool
    {
        return isset($this->headers[$name]) &&
            strpos($this->headers[$name], $value) !== false;
    }

    public function isJsonContentTypeRequest(): bool
    {
        return $this->isHeader('Content-Type', 'application/json');
    }

    public function getPathParameterAsInt(string $name, int $defaultValue = -1): int
    {
        return (int)$this->getPathParameter($name, $defaultValue);
    }

    public function getPathParameter(string $name, string $defaultValue = ''): string
    {
        return isset($this->pathParameters[$name]) ?
            $this->pathParameters[$name] : $defaultValue;
    }

    public function getQueryParameterAsInt(string $name, int $defaultValue = -1): int
    {
        return (int)$this->getQueryParameter($name, $defaultValue);
    }

    public function getQueryParameter(string $name, string $defaultValue = ''): string
    {
        return isset($this->queryParameters[$name]) ?
            $this->queryParameters[$name] : $defaultValue;
    }

    public function getSessionParameterAsInt(string $name, int $defaultValue = -1): int
    {
        return (int)$this->getSessionParameter($name, $defaultValue);
    }

    public function getSessionParameter(string $name, string $defaultValue = ''): string
    {
        return isset($this->sessionParameters[$name]) ?
            $this->sessionParameters[$name] : $defaultValue;
    }

    public function getCookieParameterAsInt(string $name, int $defaultValue = -1): int
    {
        return (int)$this->getCookieParameter($name, $defaultValue);
    }

    public function getCookieParameter(string $name, string $defaultValue = ''): string
    {
        return isset($this->cookieParameters[$name]) ?
            $this->cookieParameters[$name] : $defaultValue;
    }

    public function getRequestPath(): string
    {
        if ($this->rootPath == null || $this->requestUri == null) {
            throw new RuntimeException();
        }
        return str_replace($this->rootPath, '', $this->requestUri);
    }

    public function isRequestPathStartWith(string $part): bool
    {
        return substr($this->getRequestPath(), 0, strlen($part)) == $part;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getBodyAsArray(): array
    {
        if (empty($this->body)) {
            return [];
        } else {
            return $this->isJsonContentTypeRequest() ?
                json_decode($this->body, true) :
                unserialize($this->body);
        }
    }

    public function getStickyHandler(): Handler
    {
        return $this->stickyHandler;
    }
}

class Response
{
    private $statusCode = 200;
    private $headers = [];
    private $cookies = [];
    private $entity = '';

    public function setStatusCode(int $statusCode): Response
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function setHeader(string $name, string $value): Response
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setCookie(Cookie $cookie): Response
    {
        $this->cookies[] = $cookie;
        return $this;
    }

    public function setEntity(string $entity): Response
    {
        $this->entity = $entity;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }
}

class Cookie
{
    private $name;
    private $value;
    private $ttl;

    public function __construct($name, $value, $ttl = 60 * 60)
    {
        $this->name = $name;
        $this->value = $value;
        $this->ttl = $ttl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public static function drop($name): Cookie
    {
        return new Cookie($name, '', -1);
    }
}

class Stooge
{
    const VERSION = '0.0.1';

    private $hooks = [
        'PRE' => [],
        'POST' => []
    ];
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => []
    ];
    private $notFoundHandler;
    private $serverErrorHandler;
    private $cookiePath;
    private $cookieDomain;
    private $cookieSecure;

    public function __construct()
    {
        $this->notFoundHandler = new StaticHandler(404);
        $this->serverErrorHandler = new StaticHandler(500);
    }

    public function preHook(Handler $handler): Stooge
    {
        $this->hooks['PRE'][] = $handler;
        return $this;
    }

    public function postHook(Handler $handler): Stooge
    {
        $this->hooks['POST'][] = $handler;
        return $this;
    }

    public function get(string $path, Handler $handler): Stooge
    {
        $this->setHandler('GET', $path, $handler);
        return $this;
    }

    public function post(string $path, Handler $handler): Stooge
    {
        $this->setHandler('POST', $path, $handler);
        return $this;
    }

    public function put(string $path, Handler $handler): Stooge
    {
        $this->setHandler('PUT', $path, $handler);
        return $this;
    }

    public function patch(string $path, Handler $handler): Stooge
    {
        $this->setHandler('PATCH', $path, $handler);
        return $this;
    }

    public function delete(string $path, Handler $handler): Stooge
    {
        $this->setHandler('DELETE', $path, $handler);
        return $this;
    }

    private function setHandler(string $method, string $path, Handler $handler)
    {
        $this->routes[$method][$path] = $handler;
    }

    public function setNotFoundHandler(Handler $notFoundHandler): Stooge
    {
        $this->notFoundHandler = $notFoundHandler;
        return $this;
    }

    public function setServerErrorHandler(Handler $serverErrorHandler): Stooge
    {
        $this->serverErrorHandler = $serverErrorHandler;
        return $this;
    }

    public function setCookiePath(string $cookiePath): Stooge
    {
        $this->cookiePath = $cookiePath;
        return $this;
    }

    public function setCookieDomain(string $cookieDomain): Stooge
    {
        $this->cookieDomain = $cookieDomain;
        return $this;
    }

    public function setCookieSecure(bool $cookieSecure): Stooge
    {
        $this->cookieSecure = $cookieSecure;
        return $this;
    }

    private function getRequest(): Request
    {
        return (new Request())
            ->setTime($_SERVER['REQUEST_TIME'] ?? time())
            ->setScheme($_SERVER['REQUEST_SCHEME'])
            ->setMethod($_SERVER['REQUEST_METHOD'])
            ->setRootPath($this->getRootPath($_SERVER))
            ->setRequestUri($_SERVER['REQUEST_URI'])
            ->setHeaders($this->getRequestHeaders($_SERVER))
            ->setQueryParameters($_GET)
            ->setCookieParameters($_COOKIE ?? [])
            ->setSessionParameters($_SESSION ?? [])
            ->setBody(file_get_contents('php://input'), $_POST);
    }

    private function getRootPath(array $rawRequest)
    {
        $scriptName = $rawRequest['SCRIPT_NAME'];
        $requestUri = $rawRequest['REQUEST_URI'];
        return (strpos($requestUri, $scriptName) !== false) ?
            $scriptName :
            str_replace('\\', '', dirname($scriptName));
    }

    private function getRequestHeaders(array $rawRequest)
    {
        $headers = array();
        foreach ($rawRequest as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $header = str_replace(' ', '-',
                    ucwords(str_replace('_', ' ',
                        strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        if (isset($rawRequest['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $rawRequest['CONTENT_TYPE'];
        }
        return $headers;
    }

    private function getHandler(Request $request): Handler
    {
        $methodRoutes = $this->routes[$request->getMethod()];
        if (!empty($methodRoutes)) {
            foreach ($methodRoutes as $route => $handler) {
                if ($this->matchRoute($request, $route)) {
                    return ($request->getStickyHandler() instanceof NullHandler) ?
                        $handler :
                        $request->getStickyHandler();
                }
            }
        }
        return new NullHandler();
    }

    private function matchRoute(Request $request, $route): bool
    {
        $requestPathParts = explode('/', $request->getRequestPath());
        $routeParts = explode('/', $route);
        $pathLevels = sizeof($requestPathParts);
        if ($pathLevels == sizeof($routeParts)) {
            $pathParameters = [];
            for ($i = 0; $i < $pathLevels; ++$i) {
                if ($this->isParameter($routeParts[$i])) {
                    $pathParameters[substr($routeParts[$i], 1, -1)] =
                        $requestPathParts[$i];
                } else {
                    if ($routeParts[$i] != '*' &&
                        strcasecmp($routeParts[$i], $requestPathParts[$i]) != 0) {
                        return false;
                    }
                }
            }
            $request->setPathParameters($pathParameters);
            return true;
        }
        return false;
    }

    private function isParameter(string $routePart): bool
    {
        return (substr($routePart, 0, 1) === '{' &&
            substr($routePart, -1) === '}');
    }

    private function respond(Response $response)
    {
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $value) {
            header(implode(': ', [$name, $value]));
        }
        foreach ($response->getCookies() as $cookie) {
            setcookie($cookie->getName(),
                $cookie->getValue(),
                time() + $cookie->getTtl(),
                $this->cookiePath,
                $this->cookieDomain,
                $this->cookieSecure);
        }
        echo $response->getEntity();
    }

    public function foolAround()
    {
        $response = new Response();
        $request = $this->getRequest();
        try {
            foreach ($this->hooks['PRE'] as $preHook) {
                /** @noinspection PhpUndefinedMethodInspection */
                $preHook->handle($request, $response);
            }
            $handler = $this->getHandler($request);
            if ($handler instanceof NullHandler) {
                $this->notFoundHandler->handle($request, $response);
            } else {
                $handler->handle($request, $response);
            }
            foreach ($this->hooks['POST'] as $postHook) {
                /** @noinspection PhpUndefinedMethodInspection */
                $postHook->handle($request, $response);
            }
        } catch (Exception $e) {
            $this->serverErrorHandler->handle($request, $response);
        }
        $this->respond($response);
    }
}