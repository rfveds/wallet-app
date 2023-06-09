<?php
/**
 * Tag Form Type tests.
 */

namespace App\Tests\Form\Type;

use App\Entity\Tag;
use App\Form\Type\TagType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class TagTypeTest.
 */
class TagTypeTest extends TypeTestCase
{
    /**
     * Test build form.
     */
    public function testSubmitValidData(): void
    {
        // given
        $formData =
            [
                'title' => 'test',
            ];

        $model = new Tag();
        $form = $this->factory->create(TagType::class, $model);

        $expected = new Tag();
        $expected->setTitle($formData['title']);

        // when
        $form->submit($formData);

        // then
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }
}
