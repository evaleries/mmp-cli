<?php

namespace App\Traits;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

trait CustomHttpClient
{
    /**
     * User Agent.
     *
     * @var string
     */
    protected $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36';

    /**
     * Request timeout in a second.
     *
     * @var int
     */
    protected $timeout = 60;

    /**
     * Indicate that TLS certificates should be verified.
     *
     * @return PendingRequest
     */
    protected $verifyCertificates = false;

    /**
     * Indicate that redirects should be followed.
     *
     * @var bool
     */
    protected $followRedirects = true;

    /**
     * Customized Http client.
     *
     * @return PendingRequest
     */
    protected function http(): PendingRequest
    {
        $http = Http::timeout($this->timeout)
            ->withUserAgent($this->userAgent);

        if (!$this->followRedirects) {
            $http->withoutRedirecting();
        }

        if ($this->verifyCertificates) {
            $http->withoutVerifying();
        }

        return $http;
    }
}
