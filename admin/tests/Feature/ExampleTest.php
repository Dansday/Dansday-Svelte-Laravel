<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_route_sends_a_fresh_install_to_registration(): void
    {
        $this->get('/')->assertRedirect('/register');
    }
}
