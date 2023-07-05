<?php

namespace Tests\Feature\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * Test User API endpoints
 */
class UserTest extends TestCase {
    /*
     * Test field validation of registration api endpoint
     */
    public function testRegisterRequiresNameAndEmailAndLogin() {
        $this->json('POST', 'api/register')
                ->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJsonFragment([
                    'name' => ['The name field is required.'],
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
        ]);
    }

    /**
     * Test field validation of login api endpoint
     */
    public function testLoginRequiresEmailAndLogin() {
        $this->json('POST', 'api/login')
                ->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJsonFragment([
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
        ]);
    }
    
    /**
     * Test logout api endpoint is restricted to logged in users with an
     * authorization token only.
     */
    public function testLogoutRequiresAuthorizationToken() {
        $this->json('POST', 'api/logout')
                ->assertStatus(Response::HTTP_UNAUTHORIZED)
                ->assertJsonFragment([
                    'status' => 'Authorization Token not found'
        ]);
    }
}
