<?php

namespace App\Form;

use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('status')
            ->add('priority')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',  // <--- Tutaj wpisz nazwę pola z Kategorii (np. 'name', 'title' lub 'nazwa')
                'placeholder' => 'Wybierz kategorię', // Opcjonalnie: pusty wybór na początku
            ])
            // -----------------------------------------------------
        ;
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
