<?php
/**
 * Wallet Form Type tests.
 */

namespace App\Tests\Form\Type;

use App\Entity\Wallet;
use App\Form\Type\WalletType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class WalletTypeTest.
 */
class WalletTypeTest extends TypeTestCase
{
    /**
     * Test build form.
     */
    public function testSubmitValidData(): void
    {
        $formData =
            [
                'title' => 'test',
                'type' => 'cash',
                'balance' => 100,
            ];

        $model = new Wallet();
        $form = $this->factory->create(WalletType::class, $model);

        $expected = new Wallet();
        $expected->setTitle($formData['title']);
        $expected->setType($formData['type']);
        $expected->setBalance($formData['balance']);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($expected, $model);
    }
}
