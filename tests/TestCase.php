<?php

namespace Tests;

use App\Http\Middleware\EnsureRoleIsCurrent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Act as the given user, recording the access signature the session would
     * receive from a real login.
     */
    public function actingAs(Authenticatable $user, $guard = null)
    {
        parent::actingAs($user, $guard);

        if (method_exists($user, 'accessSignature')) {
            $this->withSession([EnsureRoleIsCurrent::SESSION_KEY => $user->accessSignature()]);
        }

        return $this;
    }
}
