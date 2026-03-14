<?php

namespace App\Form;

use App\Entity\Warehouse;
use App\Entity\Location; // AA LINE JARUR UMARJO
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // AA PAN JARURI CHE
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Warehouse Name']
            ])
            ->add('shortCode', TextType::class, [
                'label' => 'Short Code',
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g. WH01']
            ])
            // --- NAVU DROPDOWN AHIYA CHE ---
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'name', // Location nu name dekhase
                'placeholder' => '--- Select City/Location ---',
                'attr' => ['class' => 'form-select']
            ])
            ->add('address', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Full Address']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Warehouse::class,
        ]);
    }
}