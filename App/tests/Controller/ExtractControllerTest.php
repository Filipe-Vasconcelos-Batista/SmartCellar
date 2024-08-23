<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExtractControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('POST', '/login', [
            '_username' => 'big2',
            '_password' => 'banana',
        ]);


        $crawler = $client->request('GET', 'extract/photo/7');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('button', 'Return to storage');
        restore_exception_handler();
    }
    public function testExtractBarcode()
    {
        $client = static::createClient();
        $client->request('POST', '/login', [
            '_username' => 'big2',
            '_password' => 'banana',
        ]);


        $crawler = $client->request('GET', 'extract/barcode/7');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('button', 'Return to storage');
        restore_exception_handler();
    }

}