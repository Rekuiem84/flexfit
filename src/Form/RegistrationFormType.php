<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'placeholder' => 'Nom d’utilisateur',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez un nom',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'attr' => [
                    'pattern' => '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$',
                    'placeholder' => 'exemple@email.com',
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => [
                    'minlength' => 6,
                    'placeholder' => 'Mot de passe',
                ],
                'row_attr' => [
                    'class' => 'input--password',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('telephone', TelType::class, [
                'attr' => [
                    'placeholder' => '06 12 34 56 78',
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Accepter les conditions d\'utilisation',
                'mapped' => false,
                'row_attr' => [
                    'class' => 'agree-terms',
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Merci d\'accepter nos conditions d\'utilisation',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
