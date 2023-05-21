<?php
/**
 * Hello World controller tests.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * class HelloControllerTest.
 */
class HelloControllerTest extends WebTestCase
{
    /**
     * Test '/hello' route.
     */
    public function testHelloWorldRoute(): void
    {
        // given
        $client = static::createClient();

        // when
        $client->request('GET', '/hello');
        $resultHttpStatusCode = $client->getResponse()->getStatusCode();

        // then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Test default greetings.
     */
    public function testDefaultGreetings(): void
    {
        // given
        $client = static::createClient();

        // when
        $client->request('GET', '/hello');

        // then
        $this->assertSelectorTextContains('html title', 'Hello World!');
        $this->assertSelectorTextContains('html p', 'Hello World!');
    }

    /**
     * Test pesonalized greetings.
     *
     * @param string $name              Name
     * @param string $expectedGreetings Expected greetings
     *
     * @dataProvider dataProviderForTestPersonalizedGreetings
     */
    public function testPersonalizedGreetings(string $name, string $expectedGreetings): void
    {
        // given
        $client = static::createClient();

        // when
        $client->request('GET', '/hello/'.$name);

        // then
        $this->assertSelectorTextContains('html title', $expectedGreetings);
        $this->assertSelectorTextContains('html p', $expectedGreetings);
    }

    /**
     * Data provider for testPersonalizedGreetings() method.
     *
     * @return \Generator Test case
     */
    public function dataProviderForTestPersonalizedGreetings(): \Generator
    {
        yield 'Hello Ann' => [
            'name' => 'Ann',
            'expectedGreetings' => 'Hello Ann!',
        ];
        yield 'Hello John' => [
            'name' => 'John',
            'expectedGreetings' => 'Hello John!',
        ];
        yield 'Hello Beth' => [
            'name' => 'Beth',
            'expectedGreetings' => 'Hello Beth!',
        ];
    }
}
