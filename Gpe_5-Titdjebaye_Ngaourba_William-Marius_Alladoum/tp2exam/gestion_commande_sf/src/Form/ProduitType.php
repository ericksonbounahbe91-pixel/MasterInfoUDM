<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', TextType::class, ['label' => 'Désignation'])
            ->add('stock', IntegerType::class, ['label' => 'Quantité en stock', 'attr' => ['min' => 0]])
            ->add('prixUnitaire', IntegerType::class, ['label' => 'Prix unitaire (FCFA)', 'attr' => ['min' => 0]])
            ->add('photoFichier', FileType::class, [
                'label' => 'Photo (JPG, PNG ou WebP, 2 Mo maximum)',
                'mapped' => false,        // ce champ n'existe pas dans l'entité
                'required' => false,
                'constraints' => [new Image(
                    maxSize: '2M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                    mimeTypesMessage: 'Format refusé : utilisez JPG, PNG ou WebP.',
                )],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Produit::class]);
    }
}
