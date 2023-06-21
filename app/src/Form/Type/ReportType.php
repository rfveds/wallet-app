<?php
/**
 * Report Form Type.
 */

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Report;
use App\Entity\Tag;
use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

/**
 * Class ReportType.
 */
class ReportType extends AbstractType
{
    /**
     * Security.
     *
     * @var Security Security helper
     */
    private Security $security;

    /**
     * Constructor.
     *
     * @param Security $security Security helper
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }// end __construct()

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
                'attr' => ['max_length' => 64],
            ]
        );

        $builder->add(
            'category',
            EntityType::class,
            [
                'class' => Category::class,
                'choice_label' => function ($category) {
                    return $category->getTitle();
                },
                'label' => 'label.category',
                'required' => false,
                'placeholder' => 'label.none',
            ]
        );

        $builder->add(
            'tag',
            EntityType::class,
            [
                'class' => Tag::class,
                'choice_label' => function ($tag) {
                    return $tag->getTitle();
                },
                'label' => 'label.tag',
                'required' => false,
                'placeholder' => 'label.none',
            ]
        );

        $builder->add(
            'wallet',
            EntityType::class,
            [
                'class' => Wallet::class,
                'query_builder' => function (WalletRepository $walletRepository) {
                    return $walletRepository->queryByAuthor($this->security->getUser());
                },
                'choice_label' => function ($wallet) {
                    return $wallet->getTitle();
                },
                'label' => 'label_wallet',
                'placeholder' => 'label_none',
                'required' => false,
            ]
        );

        $builder->add(
            'date_from',
            DateType::class,
            [
                'label' => 'label.date_from',
                'required' => false,
            ]
        );

        $builder->add(
            'date_to',
            DateType::class,
            [
                'label' => 'label.date_to',
                'required' => false,
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
        $resolver->setDefaults(['data_class' => Report::class]);
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
        return 'report';
    }
}
