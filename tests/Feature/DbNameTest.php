<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DbNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_db_name()
    {
        file_put_contents('/tmp/opencode/dbname.txt', config('database.connections.mysql.database'));
        $this->assertTrue(true);
    }
}
