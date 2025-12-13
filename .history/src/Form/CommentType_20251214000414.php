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
            'disabled' => false,
        ]);

        // 2. Wymuś, aby 'is_enabled' było boolowskie (opcjonalnie, ale dobra praktyka)
        $resolver->setAllowedTypes('disabled', 'bool');
    }
}
...skipping...
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Opcja 'disabled' na całym formularzu
        $builder->setDisabled($options['disabled']);

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
            'disabled' => false,
        ]);
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Opcja 'disabled' na całym formularzu
        $builder->setDisabled($options['disabled']);

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
            'disabled' => false,
        ]);
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Opcja 'disabled' na całym formularzu
        $builder->setDisabled($options['disabled']);

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
            'disabled' => false,
        ]);

        // 2. Wymuś, aby 'is_enabled' było boolowskie (opcjonalnie, ale dobra praktyka)
        $resolver->setAllowedTypes('disabled', 'bool');
    }
}
