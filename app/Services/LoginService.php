<?php

namespace App\Services;

use App\Traits\AuthenticatedCookie;
use Exception;
use Illuminate\Support\Str;
use KubAT\PhpSimple\HtmlDomParser;

class LoginService
{
    use AuthenticatedCookie;

    private $service_url = null;
    private $credentials = [];

    public function setService($service): LoginService
    {
        $this->service_url = $service;

        return $this;
    }

    public function getSSOServiceUrl(): string
    {
        return $this->sso.'?service='.urlencode(
            $this->service_url ?: $this->mmp
        );
    }

    public function setUsername($username): LoginService
    {
        $this->credentials['username'] = $username;

        return $this;
    }

    public function setPassword($password): LoginService
    {
        $this->credentials['password'] = $password;

        return $this;
    }

    public function withCredential(array $cred): LoginService
    {
        if (empty($cred['username']) && $cred['nim']) {
            $cred['username'] = $cred['nim'];
            unset($cred['nim']);
        }

        if (empty($cred['username']) || empty($cred['password'])) {
            throw new \InvalidArgumentException('Username or Password is required');
        }

        $this->credentials = $cred;

        return $this;
    }

    protected function extractTokenUsingRegex($response)
    {
        preg_match_all('/execution"\s*value="(.*?)"/im', $response, $tokens);

        return $tokens[1][0] ?? null;
    }

    protected function extractTokenUsingHtmlParser($response)
    {
        $parser = HtmlDomParser::str_get_html($response);

        return $parser->find('input[type=hidden][name=execution]', 0)->value ?? null;
    }

    /**
     * Extract execution token from login page.
     *
     * @param string $response
     *
     * @throws Exception
     *
     * @return string
     */
    protected function extractExecutionToken(string $response): string
    {
        $executionToken = $this->extractTokenUsingRegex($response) ?: $this->extractTokenUsingHtmlParser($response);

        if (!$executionToken || strlen($executionToken) < 32) {
            throw new \Exception('Invalid execution token, given:'.$executionToken);
        }

        return $executionToken;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function execute()
    {
        $url = $this->getSSOServiceUrl();
        $loginPage = $this->client()->timeout(30)->get($url);

        // check if session still active
        if (Str::startsWith($loginPage->effectiveUri()->__toString(), $this->mmp_main)) {
            $this->saveCookies($loginPage->cookies());
            $this->saveResponse('dashboard.html', $loginPage->body());

            return true;
        }

        if (empty($this->credentials)) {
            throw new Exception('No credentials were set.');
        }

        $submitLogin = $this->client()
            ->withoutVerifying()
            ->asForm()
            ->post($url, $this->credentials + [
                'submit'      => 'LOGIN',
                '_eventId'    => 'submit',
                'execution'   => $this->extractExecutionToken($loginPage->body()),
                'geolocation' => '',
            ]);

        if ($submitLogin->clientError()) {
            throw new Exception('Login failed. Invalid credentials!');
        }

        if (!preg_match('/Invalid credentials/mi', $submitLogin->body())) {
            $this->saveCookies($submitLogin->cookies());
            $this->saveResponse('dashboard.html', $submitLogin->body());

            return true;
        }

        return false;
    }
}
