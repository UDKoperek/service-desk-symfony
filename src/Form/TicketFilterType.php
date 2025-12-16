<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use App\Enum\TicketStatus;
use App\Enum\TicketPriority;
use App\Dto\TicketFilterDto;

class TicketFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')

            ->add('search', TextType::class, [
                'label' => 'Szukaj (Tytuł/Treść)',
                'required' => false,
            ])

            ->add('priority', EnumType::class, [
                'class' => TicketPriority::class,
                'choice_label' => fn (TicketPriority $choice) => $choice->value,
                'choice_value' => fn (?TicketPriority $choice) => $choice?->name,
                'placeholder' => '-- Wszystkie --',
                'required' => false,
                'empty_data' => null,
            ])

            ->add('status', EnumType::class, [
                'class' => TicketStatus::class, 
                'choice_label' => fn (TicketStatus $choice) => $choice->name,
                'choice_value' => fn (?TicketStatus $choice) => $choice?->value,
                'placeholder' => '-- Wszystkie --',
                'required' => false,
                'empty_data' => null,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TicketFilterDto::class, 
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    

        
    }
    public function getBlockPrefix(): string
    {
        return '';
    }
}

    