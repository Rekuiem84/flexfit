<?php

namespace App\Form;

use App\Entity\Seance;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class SeanceType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('duration', NumberType::class, [
        'label' => 'Durée de la séance en minutes',
        'html5' => true, // permet de transformer le champ en input de type number plutôt que text
        'constraints' => [
          new Range([
            'min' => 0,
            'minMessage' => 'La durée ne peut pas être négative',
          ])
        ]
      ])
      ->add('percieved_intensity', RangeType::class, [
        'label' => 'Intensité perçue',
        'attr' => [
          'step' => '1',  // Permet des décimales avec 2 chiffres
          'min' => '0',
          'max' => '10',
        ],
        'constraints' => [
          new Range([
            'min' => 0,
            'max' => 10,
          ])
        ]
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Seance::class,
    ]);
  }
}
