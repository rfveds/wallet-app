<?php

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Tests\WebBaseTestCase;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebBaseTestCase
{
    /**
     * Test route.
     */
    public const TEST_ROUTE = '/category';

    /**
     * Set up test.
     */
    public function setUp(): void
    {
        $this->httpCLient = static::createClient();
    }

    public function testIndexRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 200;
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }
}
