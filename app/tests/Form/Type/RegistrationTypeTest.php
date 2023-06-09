<?php
/**
 * Registration form type tests.
 */

namespace App\Tests\Form\Type;

use App\Entity\User;
use App\Form\Type\RegistrationType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class RegistrationTypeTest.
 */
class RegistrationTypeTest extends TypeTestCase
{
    /**
     * Test build form.
     */
    public function testSubmitValidData(): void
    {
        // given
        $formData =
            [
                'email' => 'test_registration@example.com',
                'password' => 'test_password',
            ];

        $model = new User();
        $form = $this->factory->create(RegistrationType::class, $model);

        $expected = new User();
        $expected->setEmail($formData['email']);
        $expected->setPassword($formData['password']);

        // when
        $form->submit($formData);

        // then
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }
}