<?php

namespace FeatureBranch\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
    }

    public function testSecuritycheck()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/securityCheck');
    }

}
