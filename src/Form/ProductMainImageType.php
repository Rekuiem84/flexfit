<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ProductMainImageType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
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
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Product::class,
    ]);
  }
}
