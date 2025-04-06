<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Range;

class ProductType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('name', TextType::class, [
        'label' => 'Nom du produit',
      ])
      ->add('description', TextareaType::class, [
        'label' => 'Description',
      ])
      ->add('price', NumberType::class, [
        'label' => 'Prix',
        'scale' => 2, // Permet de définir le nombre de chiffres après la virgule
        'html5' => true, // permet de transformer le champ en input de type number plutôt que text
        'attr' => [
          'step' => '0.01',  // Permet des décimales avec 2 chiffres
          'min' => '0'       // Valeur minimale côté client
        ],
        'constraints' => [
          new Range([
            'min' => 0,
            'minMessage' => 'Le prix ne peut pas être négatif',
          ])
        ]
      ])
      ->add('isAvailable', CheckboxType::class, [
        'label' => 'Disponible',
        'required' => false,
        'data' => true
      ])
      ->add('category', EntityType::class, [
        'class' => Category::class,
        'choice_label' => 'name',
      ])
      ->add('mainImage', FileType::class, [
        'label' => 'Image principale',
        'required' => false,
        'mapped' => false, // Ne pas lier le champ à l'entité
        'help' => $options['data']->getMainImage() ? 'Image actuelle : ' . $options['data']->getMainImage()->getUrl() : 'Aucune image',
        'attr' => [
          'data-current-file' => $options['data']->getMainImage() ? $options['data']->getMainImage()->getUrl() : null,
        ],
        'constraints' => [ // Ajout de contraintes sur le champ image
          new Image([
            'maxSize' => '10240k', // Taille max : 1Mo
            'mimeTypes' => [ // Le fichier doit être de type jpg, png, webp
              'image/jpeg',
              'image/png',
              'image/webp',
            ],
            'mimeTypesMessage' => 'Merci de télécharger une image au format JPG, PNG ou WEBP' // Message d'erreur
          ]),
        ]
      ])
      ->add('detailsImages', FileType::class, [
        'label' => 'Images supplémentaires',
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
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Product::class,
    ]);
  }
}
