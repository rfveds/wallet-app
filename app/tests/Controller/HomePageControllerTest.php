<?php
/**
* HomePageControllerTest.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class HomePageControllerTest.
 */
class HomePageControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        // given
        $client = static::createClient();

        // when
        $client->request('GET', '/');

        // then
        $this->assertResponseIsSuccessful();
    }
}
