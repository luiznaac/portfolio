<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;
    use InteractsWithAuthentication;

    public function loginWithFakeUser() {
        /** @var User $user */
        $user = User::query()->create([
            'name' => 'Fake User',
            'email' => rand() . '@fake.com',
            'password' => 'aaaa',
        ]);

        $this->be($user);

        return $user;
    }
}
