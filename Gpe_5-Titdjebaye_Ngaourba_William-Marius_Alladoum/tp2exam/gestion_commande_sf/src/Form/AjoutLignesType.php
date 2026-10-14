<?php

namespace App\Form;

use App\Dto\AjoutLignes;
use App\Dto\LigneSaisie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutLignesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('lignes', CollectionType::class, [
            'entry_type' => LigneSaisieType::class,
            'allow_add' => true,       // le JavaScript peut ajouter des lignes
            'allow_delete' => true,
            // une ligne entièrement vide est simplement ignorée
            'delete_empty' => fn (?LigneSaisie $l) => $l === null || ($l->produit === null && $l->quantite === null),
            'label' => false,
            'attr' => ['class' => 'collection-lignes'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AjoutLignes::class]);
    }
}
