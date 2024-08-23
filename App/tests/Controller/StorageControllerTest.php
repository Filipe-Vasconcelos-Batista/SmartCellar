<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StorageControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('POST', '/login', [
            '_username' => 'big2',
            '_password' => 'banana',
        ]);
        $crawler = $client->request('GET', 'user/storage');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Your Storages');
        restore_exception_handler();
    }
    public function testIndexWithStorage()
    {
        $client = static::createClient();
        $client->request('POST', '/login', [
            '_username' => 'big2',
            '_password' => 'banana',
        ]);
        $crawler = $client->request('GET', 'user/storage/7');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Your Storages');
        restore_exception_handler();
    }

    public function testCreateStorage()
    {
        $client = static::createClient();
        $client->request('POST', '/login', [
            '_username' => 'big2',
            '_password' => 'banana',
        ]);
         $client->request('POST', '/storage/create',[
             'name'=>'big bazooka'
         ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseIsSuccessful();

        restore_exception_handler();
    }



}