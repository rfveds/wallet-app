<?php
///**
// * Operation Form Type tests.
// */
//
//namespace App\Tests\Form\Type;
//
//use App\Entity\Operation;
//use App\Form\DataTransformer\TagsDataTransformer;
//use App\Form\Type\OperationType;
//use Symfony\Component\Form\PreloadedExtension;
//use Symfony\Component\Form\Test\TypeTestCase;
//
///**
// * Class OperationTypeTest.
// */
//class OperationTypeTest extends TypeTestCase
//{
//    private TagsDataTransformer $objectManager;
//
//    protected function setUp(): void
//    {
//        $this->objectManager = $this->createMock(TagsDataTransformer::class);
//        parent::setUp();
//    }
//
//    protected function getExtensions(): array
//    {
//        $type = new OperationType($this->objectManager);
//
//        return [
//            new PreloadedExtension([$type, $this->objectManager], []),
//        ];
//    }
//
//    /**
//     * Test build form.
//     */
//    public function testSubmitValidData(): void
//    {
//        // given
//        $formData =
//            [
//                'title' => 'test',
//                'amount' => 100,
//                'category' => 'test',
//                'wallet' => 'test',
//                'tags' => 'test, test2',
//            ];
//
//        $model = new Operation();
//        $form = $this->factory->create(OperationType::class, $formData);
//
//        $expected = new Operation();
//        $expected->setTitle($formData['title']);
//        $expected->setAmount($formData['amount']);
//        $expected->setCategory($formData['category']);
//        $expected->setWallet($formData['wallet']);
//        $expected->addTag($formData['tags']);
//
//        // when
//        $form->submit($formData);
//
//        // then
//        $this->assertTrue($form->isSynchronized());
//        $this->assertEquals($expected, $model);
//    }
//}
