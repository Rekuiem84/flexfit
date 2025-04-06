<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;

class ProductDetailsImagesType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('detailsImages', FileType::class, [
        'label' => 'Ajouter des images',
        'multiple' => true,
        'mapped' => false,
        'required' => false,
        'constraints' => [
          new All([
            'constraints' => [
              new Image([
                'maxSize' => '10240k',
                'mimeTypes' => [
                  'image/jpeg',
                  'image/png',
                  'image/webp',
                ],
                'mimeTypesMessage' => 'Merci de télécharger une image au format JPG, PNG ou WEBP'
              ])
            ]
          ])
        ]
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Product::class,
    ]);
  }
}
