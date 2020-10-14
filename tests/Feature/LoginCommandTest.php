<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoginCommandTest extends TestCase
{
    protected function getCookieFullPath()
    {
        return Storage::path('cookies/'.md5(config('sister.nim')).'.json');
    }

    /**
     * Test basic command for login.
     *
     * @return void
     */
    public function testLoginCommand()
    {
        $this->artisan('login')
            ->assertExitCode(0);

        $this->assertCommandCalled('login');
    }

    public function testCookieDirExists()
    {
        $this->assertDirectoryExists(Storage::path('cookies'));
    }

    public function testCookieDirWriteable()
    {
        $this->assertDirectoryIsWritable(Storage::path('cookies'));
    }

    public function testCookieExists()
    {
        $this->assertFileExists($this->getCookieFullPath());
    }

    public function testCookieFileIsReadable()
    {
        $this->assertFileIsReadable($this->getCookieFullPath());
    }
}
