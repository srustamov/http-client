<?php

namespace Srustamov\HttpClient;


use ArrayAccess;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RedirectMiddleware;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\RequestOptions;

/**
 * @mixin Client
 */
class Http implements ArrayAccess
{
    use Conditional;

    /**@var array */
    protected $options = [
        RequestOptions::DEBUG     => false,
        RequestOptions::VERIFY    => true,
        RequestOptions::TIMEOUT   => 10,
        RequestOptions::HEADERS   => [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ],
        'base_uri'                => null,
    ];

    protected $body_option_key = RequestOptions::JSON;

    /**@var ResponseInterface */
    protected $response;

    protected $content;

    public function __construct($base_uri = null)
    {
        $this->options[RequestOptions::ALLOW_REDIRECTS] = RedirectMiddleware::$defaultSettings;

        $this->setBaseUrI($base_uri);
    }

    public static function create($base_uri = null): self
    {
        return new static($base_uri);
    }

    public function setOption($key,$value): self
    {
        $this->options[$key] = $value;

        return $this;
    }

    public function setBaseUrI(?string $url): self
    {
        $this->options['base_uri'] = $url;

        return $this;
    }

    public function getClient(): Client
    {
        return new Client($this->options);
    }

    public function debug($boolean = true): self
    {
        $this->options[RequestOptions::DEBUG] = $boolean;

        return $this;
    }

    public function asForm(): self
    {
        $this->body_option_key = RequestOptions::FORM_PARAMS;

        return $this;
    }

    public function withoutVerify(): self
    {
        $this->options[RequestOptions::VERIFY] = false;

        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->options[RequestOptions::HEADERS] = array_merge($this->options[RequestOptions::HEADERS] ?? [],$headers);

        return $this;
    }

    public function bearer($value): self
    {
        return $this->authorization("Bearer $value");
    }

    public function authorization($value): self
    {
        return $this->addHeader('Authorization',$value);
    }

    public function acceptJson(): self
    {
        return $this->addHeader('Accept','application/json');
    }

    public function isJson(): self
    {
        return $this->addHeader('Content-type','application/json');
    }

    public function addHeader($key,$value): self
    {
        $this->options[RequestOptions::HEADERS][$key] = $value;

        return $this;
    }

    public function timeout($timeout): self
    {
        $this->options[RequestOptions::TIMEOUT] = $timeout;

        return $this;
    }

    public function get($url, array $query = [],$method = 'get'): self
    {
        try {
            $this->response = $this->getClient()->$method($url, [RequestOptions::QUERY => $query]);
        } catch (ClientException|ServerException $exception) {
            $this->response = $exception->getResponse();
        }

        return $this;
    }

    public function head($url,array $query): self
    {
        return $this->get($url,$query,'head');
    }

    public function post($url, array $data = []): self
    {
        return $this->sendWithBodyMethod('post',$url,$data);
    }

    public function put($url, array $data = []): self
    {
        return $this->sendWithBodyMethod('put',$url,$data);
    }

    public function patch($url, array $data = []): self
    {
        return $this->sendWithBodyMethod('patch',$url,$data);
    }


    /**
     * @param $url
     * @return $this
     * @throws GuzzleException
     */
    public function delete($url): self
    {
        try {
            $this->response = $this->getClient()->delete($url);
        } catch (ClientException|ServerException $exception) {
            $this->response = $exception->getResponse();
        }
        return $this;
    }

    public function isOk(): bool
    {
        return $this->status() == 200;
    }

    public function serverError(): bool
    {
        return $this->status() >= 500;
    }

    public function status(): ?int
    {
        return $this->getResponse()->getStatusCode();
    }

    public function successful(): bool
    {
        return $this->status() >= 200 && 300 > $this->status();
    }

    public function sendWithBodyMethod($method,$url,array $data = []): self
    {
        try {
            $this->response = $this->getClient()->$method($url, [
                $this->body_option_key => $data
            ]);
        } catch (ClientException|ServerException $exception) {
            $this->response = $exception->getResponse();
        }

        return $this;
    }

    public function json()
    {
        if ($body = $this->body()) {
            return json_decode($body, true);
        }
        return null;
    }

    public function body(): ?string
    {
        if (!$this->content && $this->getResponse()) {
            return $this->content =  $this->getResponse()->getBody()->getContents();
        } elseif ($this->content) {
            return $this->content;
        }
        return null;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public static function __callStatic($name, $arguments)
    {
        return self::create()->$name(...$arguments);
    }

    public function __call($name, $arguments)
    {
        try {
            $this->response = $this->getClient()->$name(...$arguments);
        } catch (ClientException|ServerException $exception) {
            $this->response = $exception->getResponse();
        }

        return $this;
    }

    public function offsetExists($offset)
    {
        return $this->json()[$offset] ?? false;
    }

    public function offsetGet($offset)
    {
        return $this->json()[$offset] ?? false;
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    public function toArray()
    {
        return $this->json() ?? [];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}