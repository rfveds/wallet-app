<?php
/**
 * Wallet Form Type.
 */

namespace App\Form\Type;

use App\Entity\Wallet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WalletType.
 */
class WalletType extends AbstractType
{
    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options The options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'label.title',
                'required' => true,
                'attr' => [
                    'minlength' => 3,
                    'max_length' => 64,
                ],
            ]
        );
        $builder->add(
            'type',
            ChoiceType::class,
            [
                'label' => 'label.type',
                'required' => true,
                'choices' => [
                    'label.type.cash' => 'cash',
                    'label.type.bank' => 'bank',
                    'label.type.credit_card' => 'credit_card',
                    'label.type.other' => 'other',
                ],
                'attr' => [
                    'minlength' => 3,
                    'max_length' => 64,
                ],
            ]
        );

        $builder->add(
            'balance',
            NumberType::class,
            [
                'label' => 'label.balance',
                'required' => true,
                'attr' => ['min' => 0],
            ]
        );
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Wallet::class]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix(): string
    {
        return 'category';
    }
}
