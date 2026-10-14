<?php

namespace App\Form;

use App\Dto\LigneSaisie;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneSaisieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'label' => false,
                'required' => false,
                'placeholder' => 'Choisir un produit',
                'query_builder' => fn (ProduitRepository $r) => $r->createQueryBuilder('p')->orderBy('p.designation'),
                'choice_label' => fn (Produit $p) => sprintf(
                    '%s (stock %d, %s FCFA)',
                    $p->getDesignation(),
                    $p->getStock(),
                    number_format($p->getPrixUnitaire(), 0, ',', ' ')
                ),
                // Produit en rupture : option grisée
                'choice_attr' => fn (Produit $p) => $p->getStock() <= 0 ? ['disabled' => 'disabled'] : [],
                'attr' => ['aria-label' => 'Produit'],
            ])
            ->add('quantite', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['min' => 1, 'placeholder' => 'Quantité', 'aria-label' => 'Quantité'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => LigneSaisie::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'ligne_saisie';
    }
}
