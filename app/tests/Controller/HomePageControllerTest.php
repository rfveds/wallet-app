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
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/';

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * Test index page.
     */
    public function testIndex(): void
    {

        // when
        $this->httpClient->request('GET', $this::TEST_ROUTE);

        // then
        $this->assertResponseIsSuccessful();
    }
}
