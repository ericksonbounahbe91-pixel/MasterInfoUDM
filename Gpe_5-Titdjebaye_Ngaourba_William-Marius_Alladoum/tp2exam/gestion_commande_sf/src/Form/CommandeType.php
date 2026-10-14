<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Commande;
use App\Repository\ClientRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'label' => 'Client',
                'placeholder' => 'Choisir un client',
                'query_builder' => fn (ClientRepository $r) => $r->createQueryBuilder('c')->orderBy('c.nom')->addOrderBy('c.prenom'),
                'choice_label' => fn (Client $c) => $c->getNomComplet(),
            ])
            ->add('dateCommande', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Commande::class]);
    }
}
