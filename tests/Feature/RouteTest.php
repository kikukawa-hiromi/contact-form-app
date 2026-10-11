<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_トップページにアクセスするとお問い合わせページが表示されること(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
    }

    public function test_サンクスページが表示されること(): void
    {
        $response = $this->get('/contacts/thanks');

        $response->assertStatus(200);
    }
}
