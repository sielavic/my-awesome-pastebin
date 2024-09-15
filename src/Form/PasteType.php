<?php
namespace App\Form;

use App\Entity\Paste;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('content', TextareaType::class)
            ->add('access', ChoiceType::class, [
                'choices' => [
                    'Public' => 'public',
                    'Unlisted' => 'unlisted',
                ],
                'expanded' => true,
            ])
            ->add('expirationDuration', ChoiceType::class, [
                'choices' => [
                    'Без ограничения' => '2147483647',
                    '1 час' => '3600',
                    '1 день' => '86400',
                    '1 неделя' => '604800',
                    '1 месяц' => '2592000',
                ],
            ])
            ->add('language', ChoiceType::class, [
                'choices' => [
                    'Русский' => 'ru',
                    'English' => 'en',
                    'Deutsch' => 'de',
                    'Français' => 'fr',
                ],
                'placeholder' => 'Выберите язык', // Добавляет значение по умолчанию
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Paste::class,
        ]);
    }
}