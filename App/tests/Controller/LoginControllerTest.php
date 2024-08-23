<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    public function testLoginPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div', 'Login');

        restore_exception_handler();
}
    public function testAuthentication()
    {
        $client = static::createClient();
        $client->request('POST', '/login', [
            '_username' => 'big2',
            '_password' => 'banana',
        ]);

        $this->assertResponseRedirects('/');
        restore_exception_handler();
    }


}
