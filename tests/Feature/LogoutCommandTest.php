<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LogoutCommandTest extends TestCase
{
    private $question = "This command will delete the storage's contents? Continue";

    public function setUp(): void
    {
        parent::setUp();
        if (File::isDirectory(Storage::path('cookies')) && File::isDirectory(Storage::path('responses'))) {
            File::copyDirectory(Storage::path('cookies'), Storage::path('temp-cookies'));
            File::copyDirectory(Storage::path('responses'), Storage::path('temp-responses'));
        }
    }

    /**
     * Test logout with answer no/false.
     *
     * @return void
     */
    public function testLogoutAnswerNoCommand()
    {
        $this->artisan('logout')
            ->expectsConfirmation($this->question, 'no')
            ->expectsOutput('Logout cancelled')
            ->assertExitCode(0);
    }

    /**
     * Test logout with answer yes/true.
     *
     * @return void
     */
    public function testLogoutAnswerYesCommand()
    {
        $this->artisan('logout')
            ->expectsConfirmation($this->question, 'yes')
            ->expectsOutput('Logout completed')
            ->assertExitCode(0);

        $this->assertDirectoryDoesNotExist(Storage::path('cookies'));
        $this->assertDirectoryDoesNotExist(Storage::path('responses'));
    }

    public function tearDown(): void
    {
        if (File::isDirectory(Storage::path('temp-cookies')) && File::isDirectory(Storage::path('temp-responses'))) {
            File::moveDirectory(Storage::path('temp-cookies'), Storage::path('cookies'));
            File::moveDirectory(Storage::path('temp-responses'), Storage::path('responses'));
        }
    }
}
