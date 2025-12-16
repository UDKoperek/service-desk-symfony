<?php

namespace App\Form;

use App\Entity\Ticket;
use App\Entity\Category;
use App\Enum\TicketStatus;
use App\Enum\TicketPriority;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType; 
use Symfony\Component\Form\Extension\Core\Type\TextareaType; 
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Tytuł zgłoszenia',
                'attr' => ['placeholder' => 'Np. Błąd logowania']
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Opis problemu',
                'attr' => ['rows' => 5],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Wybierz kategorię',
                'label' => 'Kategoria',
            ]);
            if ($options['priority_disabled'])
            {
                $builder->add('priority', EnumType::class, [
                    'class' => TicketPriority::class,
                    'choice_label' => fn (TicketPriority $choice) => $choice->value,
                    'placeholder' => 'Wybierz status',
                    'label' => 'Priorytet',
                ]);
            }

            if ($options['status_disabled'])
            {
                $builder->add('status', EnumType::class, [
                    'class' => TicketStatus::class,
                    'choice_label' => fn (TicketStatus $choice) => $choice->value,
                    'placeholder' => 'Wybierz status',
                    'label' => 'Status',
                ]);
            }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
            'status_disabled' => true,
            'priority_disabled' => true,
        ]);

        $resolver->setAllowedTypes('status_disabled', 'bool');
        $resolver->setAllowedTypes('priority_disabled', 'bool');
    }
}
