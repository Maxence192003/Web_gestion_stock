<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
            ])
            ->add('typeProduit', ChoiceType::class, [
                'label' => 'Type de produit',
                'choices' => [
                    'Boisson' => Produit::TYPE_BOISSON,
                    'Nourriture' => Produit::TYPE_NOURRITURE,
                    'Autre' => Produit::TYPE_AUTRE,
                ],
            ])
            ->add('prixAchat', MoneyType::class, [
                'label' => 'Prix d\'achat',
                'currency' => 'EUR',
                'divisor' => 1,
            ])
            ->add('prixVente', MoneyType::class, [
                'label' => 'Prix de vente',
                'currency' => 'EUR',
                'divisor' => 1,
            ])
            ->add('quantiteStock', IntegerType::class, [
                'label' => 'Quantite en stock',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}