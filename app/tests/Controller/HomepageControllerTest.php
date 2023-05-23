<?php
/**
*  Hompage controller tests.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class HomepageControllerTest.
 */
class HomepageControllerTest extends WebTestCase
{
    /**
     * Test route.
     */
    public const TEST_ROUTE = '/';

    /**
     * Set up test.
     */
    public function setUp(): void
    {
        $this->httpCLient = static::createClient();
    }

    /**
     * Test index route for anonymous user.
     */
    public function testIndexRouteForAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 200;

        // when
        $this->httpCLient->request('GET', self::TEST_ROUTE);
        $response = $this->httpCLient->getResponse();

        // then
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }
}
