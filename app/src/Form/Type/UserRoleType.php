<?php
/**
 * UserRole Type.
 */

namespace App\Form\Type;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserRoleType.
 */
class UserRoleType extends AbstractType
{
    /**
     * Build form.
     *
     * @param FormBuilderInterface $builder Form builder interface
     * @param array                $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // choice from two options ROLE_USER and ROLE_ADMIN
        $builder->add(
            'roles',
            ChoiceType::class,
            [
                'label' => 'label.roles',
                'required' => true,
                'choices' => [
                    'label.admin' => UserRole::ROLE_ADMIN->value,
                    'label.user' => UserRole::ROLE_USER->value,
                ],
                'expanded' => true,
                'multiple' => true,
                'attr' => ['max_length' => 64],
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver resolver
     *
     * @return void return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}// end class
