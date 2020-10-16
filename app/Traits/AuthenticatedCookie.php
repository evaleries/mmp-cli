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

    protected $sso = 'https://sso.unej.ac.id/cas/login';
    protected $mmp = 'https://mmp.unej.ac.id/login/index.php';
    protected $mmp_main = 'https://mmp.unej.ac.id/';

    /**
     * Retrieved sesskey.
     *
     * @var string
     */
    private $sesskey;

    /**
     * Loaded cookies.
     *
     * @var Collection
     */
    private $loadedCookies;

    /**
     * Cookie filename.
     *
     * @var string
     */
    private $cookieFile;

    /**
     * Save response to storage.
     *
     * @param string $name
     * @param string $response
     *
     * @return void
     */
    public function saveResponse($name, $response)
    {
        Storage::put('responses/'.$name, $response);
    }

    public function cookieFile()
    {
        return 'cookies/'.md5(config('sister.nim')).'.json';
    }

    /**
     * Get the cookie path.
     *
     * @return string
     */
    public function cookiePath()
    {
        return Storage::path($this->cookieFile());
    }

    /**
     * Save cookies from array.
     *
     * @param array|CookieJar|null $cookies
     *
     * @return int|bool
     */
    protected function saveCookies($cookies = null)
    {
        if ($cookies === null) {
            return $this->cookieJar()->save($this->cookiePath());
        }

        if ($cookies instanceof CookieJar) {
            $cookies = $cookies->toArray();
        }

        if (!is_array($cookies)) {
            throw new \InvalidArgumentException('Cookies is not an array, but '.gettype($cookies).' given');
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
            return [];
        }

        return $this->cookieJar()->load($this->cookiePath());
    }

    /**
     * Cookie manager.
     *
     * @return FileCookieJar
     */
    protected function cookieJar()
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
        $response ??= (Storage::has('responses/dashboard.html') ? Storage::get('responses/dashboard.html') : '');

        preg_match_all('/"sesskey":"(.*?)"/si', $response, $tokens);

        return $tokens[1][0] ?? null;
    }
}
