<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Opcja 'disabled' na całym formularzu
        $builder->setDisabled(!$options['is_enabled']); 

        $builder
            ->add('content', TextareaType::class, [
                // Inne opcje dla pola content
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Dodaj komentarz',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            // 1. Zdefiniowanie niestandardowej opcji 'is_enabled'
            'is_enabled' => true, 
        ]);
        
        // 2. Wymuś, aby 'is_enabled' było boolowskie (opcjonalnie, ale dobra praktyka)
        $resolver->setAllowedTypes('is_enabled', 'bool');
    }
}
