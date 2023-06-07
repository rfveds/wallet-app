<?php
/**
 * Security controller tests.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTest.
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Test login route for anonymous user.
     */
    public function testLoginRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $expectedTitle = 'Log in!';

        // when
        $client = static::createClient();
        $client->request('GET', '/login');
        $resultStatusCode = $client->getResponse()->getStatusCode();
        $resultTitle = $client->getResponse()->getContent();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
        $this->assertStringContainsString($expectedTitle, $resultTitle);
    }

    /**
     * Test logout route for logged user.
     */
    public function testLogoutRouteLoggedUser(): void
    {
        // given
        $expectedStatusCode = 302;
        $expectedRoute = '/login';

        // when
        $client = static::createClient();
        $client->request('GET', '/logout');
        $resultStatusCode = $client->getResponse()->getStatusCode();
        $resultRoute = $client->getResponse()->headers->get('location');

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
        $this->assertStringContainsString($expectedRoute, $resultRoute);
    }
}
