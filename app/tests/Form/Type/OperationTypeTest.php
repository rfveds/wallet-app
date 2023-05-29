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
//    /**
//     * Tags data transformer.
//     */
//    private TagsDataTransformer $tagsDataTransformer;
//
//    /**
//     * Set up.
//     */
//    protected function setUp(): void
//    {
//        $this->tagsDataTransformer = $this->createMock(TagsDataTransformer::class);
//        parent::setUp();
//    }
//
//    /**
//     * Get extensions.
//     */
//    protected function getExtensions(): array
//    {
//        // create a type instance with the mocked dependencies
//        $type = new OperationType($this->tagsDataTransformer);
//
//        return [
//            // register the type instances with the PreloadedExtension
//            new PreloadedExtension([$type], []),
//        ];
//    }
////
////    /**
////     * Test build form.
////     */
////    public function testSubmitValidData(): void
////    {
////        $formData =
////            [
////                'title' => 'test',
////                'amount' => 100,
////                'category' => 'test',
////                'wallet' => 'wallet',
////            ];
////
////        $model = new Operation();
////        $form = $this->factory->create(OperationType::class, $model);
////
////    }
//}
