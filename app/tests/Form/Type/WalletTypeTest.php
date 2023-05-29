<?php
///**
//* Wallet type tests.
// */
//
//namespace App\Tests\Form\Type;
//
//use App\Entity\Wallet;
//use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\Form\FormInterface;
//use Symfony\Component\Form\FormTypeInterface;
//use Symfony\Component\Form\FormView;
//use Symfony\Component\Form\Test\TypeTestCase;
//use Symfony\Component\OptionsResolver\OptionsResolver;
//
///**
// * Class WalletTypeTest.
// */
//class WalletTypeTest extends TypeTestCase implements FormTypeInterface
//{
//    public function testSubmitValidDate()
//    {
//        $formData = [
//            'title' => 'testWallet',
//            'balance' => 2000,
//            'type' => 'cash',
//        ];
//
//        $model = new Wallet();
//        $form = $this->factory->create(WalletTypeTest::class, $model);
//
//        $expected = new Wallet();
//        $expected->setType('cash');
//        $expected->setTitle('testWallet');
//        $expected->setBalance(2000);
//
//        $form->submit($formData);
//        $this->assertTrue($form->isSynchronized());
//
//        $this->assertEquals($expected, $model);
//    }
//
//    public function buildForm(FormBuilderInterface $builder, array $options)
//    {
//        // TODO: Implement buildForm() method.
//    }
//
//    public function buildView(FormView $view, FormInterface $form, array $options)
//    {
//        // TODO: Implement buildView() method.
//    }
//
//    public function finishView(FormView $view, FormInterface $form, array $options)
//    {
//        // TODO: Implement finishView() method.
//    }
//
//    public function configureOptions(OptionsResolver $resolver)
//    {
//        // TODO: Implement configureOptions() method.
//    }
//
//    public function getBlockPrefix()
//    {
//        // TODO: Implement getBlockPrefix() method.
//    }
//
//    public function getParent()
//    {
//        // TODO: Implement getParent() method.
//    }
//}
