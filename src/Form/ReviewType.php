<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<Review> */
class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Cég neve',
                'attr' => ['placeholder' => 'pl. Trustindex Kft.'],
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'Értékelés',
                'choices' => [
                    '★★★★★ 5' => 5,
                    '★★★★ 4' => 4,
                    '★★★ 3' => 3,
                    '★★ 2' => 2,
                    '★ 1' => 1,
                ],
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('reviewText', TextareaType::class, [
                'label' => 'Vélemény szövege',
                'attr' => ['rows' => 5, 'placeholder' => 'Írd le a tapasztalataidat...'],
            ])
            ->add('authorEmail', EmailType::class, [
                'label' => 'E-mail cím',
                'attr' => ['placeholder' => 'pelda@email.hu'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Elküldés',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
