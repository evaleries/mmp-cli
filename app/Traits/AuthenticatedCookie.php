<?php

namespace App\Traits;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

trait AuthenticatedCookie
{
    use CustomHttpClient;

    protected string $sso;
    protected string $mmp;
    protected string $mmp_main;
    protected string $responseDashboard = 'responses/dashboard.html';

    /**
     * Retrieved sesskey.
     *
     * @var string
     */
    private string $sesskey;

    /**
     * Loaded cookies.
     *
     * @var Collection
     */
    private Collection $loadedCookies;

    /**
     * Cookie filename.
     *
     * @var string
     */
    private string $cookieFile;

    /**
     * Save response to storage.
     *
     * @param string $name
     * @param string $response
     *
     * @return void
     */
    public function saveResponse(string $name, string $response)
    {
        Storage::put('responses/'.$name, $response);
    }

    public function cookieFile(): string
    {
        return 'cookies/'.md5(config('sister.nim')).'.json';
    }

    /**
     * Get the cookie path.
     *
     * @return string
     */
    public function cookiePath(): string
    {
        return Storage::path($this->cookieFile());
    }

    /**
     * Save cookies from array.
     *
     * @param array|CookieJar|null $cookies
     *
     * @return bool
     */
    protected function saveCookies($cookies = null): bool
    {
        if ($cookies === null) {
            $this->cookieJar()->save($this->cookiePath());
            return true;
        }

        if ($cookies instanceof CookieJar) {
            $cookies = $cookies->toArray();
        }

        if (!is_array($cookies)) {
            throw new \InvalidArgumentException('Cookies is not an array.');
        }

        return Storage::put($this->cookieFile(), json_encode($cookies, JSON_PRETTY_PRINT));
    }

    /**
     * Load cookies from file.
     *
     * @return void
     */
    protected function loadCookies()
    {
        if (!File::exists($this->cookiePath())) {
            return;
        }

        $this->cookieJar()->load($this->cookiePath());
    }

    /**
     * Cookie manager.
     *
     * @return FileCookieJar
     */
    protected function cookieJar(): FileCookieJar
    {
        return new FileCookieJar(
            $this->cookiePath(),
            true
        );
    }

    /**
     * Setup Http client with custom options.
     *
     * @return PendingRequest
     */
    public function client(): PendingRequest
    {
        return $this->http()
            ->withOptions([
                'cookies' => $this->cookieJar(),
            ]);
    }

    /**
     * Get sesskey from response.
     *
     * @param mixed $response
     *
     * @return void
     */
    public function getSesskey($response = null)
    {
        $response ??= (Storage::has($this->responseDashboard) ? Storage::get($this->responseDashboard) : '');

        preg_match_all('/"sesskey":"(.*?)"/si', $response, $tokens);

        return $tokens[1][0] ?? null;
    }

    public function sso(): string
    {
        if (!isset($this->sso)) {
            return $this->sso = config('mmp.sso_baseurl');
        }

        return $this->sso;
    }

    public function mmp(): string
    {
        if (!isset($this->mmp)) {
            return $this->mmp = config('mmp.moodle_baseurl') . 'login/index.php';
        }

        return $this->mmp;
    }

    public function mmp_main(): string
    {
        if (!isset($this->mmp_main)) {
            return $this->mmp_main = config('mmp.moodle_baseurl');
        }

        return $this->mmp_main;
    }
}
