<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductType;
use App\Form\UserProductType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;


#[Route('/app')]
#[IsGranted('ROLE_USER')]
final class AppliController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route(name: 'appli_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('appli/index.html.twig', [
            'title' => 'Dashboard',
            'user' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'user'
            ]
        ]);
    }

    #[Route(path: "/informations", name: 'appli_informations', methods: ['GET', 'POST'])]
    public function informations(Request $request): Response
    {
        $session = $request->getSession();
        $currentStep = $request->query->get('step', $session->get('currentStep', 1));
        $totalSteps = 3;

        // S'assurer que currentStep est un entier
        $currentStep = (int)$currentStep;
        // Valider la plage de currentStep
        if ($currentStep < 1) $currentStep = 1;
        if ($currentStep > $totalSteps) $currentStep = $totalSteps;


        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            switch ($currentStep) {
                case 1:
                    $session->set('gender', $data['gender']);
                    $session->set('currentStep', 2);
                    return $this->redirectToRoute('appli_informations', ['step' => 2]);
                case 2:
                    $session->set('height', $data['height']);
                    $session->set('currentStep', 3);
                    return $this->redirectToRoute('appli_informations', ['step' => 3]);
                case 3:
                    $session->set('weight', $data['weight']);

                    // If this is the final submission, save the data
                    if (isset($data['final_submission'])) {
                        $user = $this->getUser();
                        if ($user instanceof User) {

                            // Conversion explicite des types pour éviter les erreurs
                            $gender = $session->get('gender');
                            $height = (int)$session->get('height');
                            $weight = (float)$session->get('weight');

                            $user->setGender($gender);
                            $user->setHeight($height);
                            $user->setWeight($weight);

                            $this->entityManager->persist($user);
                            $this->entityManager->flush();

                            // Clear the session data
                            $session->remove('currentStep');
                            $session->remove('gender');
                            $session->remove('height');
                            $session->remove('weight');

                            if ($request->isXmlHttpRequest()) {
                                return $this->json([
                                    'success' => true,
                                    'redirect' => $this->generateUrl('appli_dashboard')
                                ]);
                            }
                            return $this->redirectToRoute('appli_dashboard');
                        }
                    }
                    break;
            }

            $session->set('currentStep', $currentStep);

            if ($request->isXmlHttpRequest()) {
                $html = $this->renderView('appli/informations.html.twig', [
                    'title' => 'Informations',
                    'currentStep' => $currentStep,
                    'totalSteps' => $totalSteps,
                    'gender' => $session->get('gender'),
                    'height' => $session->get('height'),
                    'weight' => $session->get('weight')
                ]);

                return $this->json([
                    'success' => true,
                    'html' => $html,
                    'currentStep' => $currentStep,
                    'totalSteps' => $totalSteps
                ]);
            }
        }

        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            $html = $this->renderView('appli/informations.html.twig', [
                'title' => 'Informations',
                'currentStep' => $currentStep,
                'totalSteps' => $totalSteps,
                'gender' => $session->get('gender'),
                'height' => $session->get('height'),
                'weight' => $session->get('weight')
            ]);

            return $this->json([
                'success' => true,
                'html' => $html,
                'currentStep' => $currentStep,
                'totalSteps' => $totalSteps
            ]);
        }

        $response = $this->render('appli/informations.html.twig', [
            'title' => 'Informations',
            'currentStep' => $currentStep,
            'totalSteps' => $totalSteps,
            'gender' => $session->get('gender'),
            'height' => $session->get('height'),
            'weight' => $session->get('weight')
        ]);

        return $response;
    }

    #[Route(path: "/dashboard", name: 'appli_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, rediriger vers login
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Si l'utilisateur est connecté mais que son compte n'est pas "ok", rediriger vers informations
        if ($user->getAccountState() !== 'ok') {
            return $this->redirectToRoute('appli_informations');
        }

        return $this->render('appli/dashboard.html.twig', [
            'title' => 'Tableau de bord',
            'stats' => [
                'workouts' => 12,
                'calories' => 2500,
                'streak' => 5
            ]
        ]);
    }

    #[Route(path: "/produits", name: 'appli_produits', methods: ['GET'])]
    public function produits(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        // Récupérer les produits de l'utilisateur grâce à la relation ManyToMany
        $products = $user->getProducts();

        return $this->render('appli/produits.html.twig', [
            'title' => 'Mes produits',
            'products' => $products
        ]);
    }

    #[Route(path: "/produits/new", name: 'appli_produits_new')]
    public function produitsNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        $form = $this->createForm(UserProductType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->get('product')->getData();
            $user->addProduct($product);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été ajouté à votre collection');
            return $this->redirectToRoute('appli_produits');
        }

        return $this->render('appli/produits_new.html.twig', [
            'title' => 'Nouveau produit',
            'form' => $form->createView()
        ]);
    }

    #[Route(path: "/produits/delete/{id}", name: 'appli_produits_delete')]
    public function produitsDelete(Product $product, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        // Retirer le produit de la collection de l'utilisateur
        if ($user->getProducts()->contains($product)) {
            $user->removeProduct($product);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été retiré de votre collection');
        } else {
            $this->addFlash('error', 'Ce produit ne fait pas partie de votre collection');
        }

        return $this->redirectToRoute('appli_produits');
    }

    #[Route(path: "/entrainement", name: 'appli_entrainement', methods: ['GET'])]
    public function entrainement(): Response
    {
        return $this->render('appli/entrainement.html.twig', [
            'title' => 'Entraînements',
            'workouts' => [
                [
                    'id' => 1,
                    'name' => 'Full Body',
                    'duration' => '45 min',
                    'difficulty' => 'Moyen'
                ],
                [
                    'id' => 2,
                    'name' => 'Upper Body',
                    'duration' => '30 min',
                    'difficulty' => 'Facile'
                ]
            ]
        ]);
    }

    #[Route(path: "/entrainement/start", name: 'appli_entrainement_start', methods: ['GET'])]
    public function entrainementStart(): Response
    {
        return $this->render('appli/entrainement_start.html.twig', [
            'title' => 'Commencer un entraînement',
            'workout' => [
                'id' => 1,
                'name' => 'Full Body',
                'exercises' => [
                    ['name' => 'Squats', 'sets' => 3, 'reps' => 12],
                    ['name' => 'Push-ups', 'sets' => 3, 'reps' => 10]
                ]
            ]
        ]);
    }

    #[Route(path: "/entrainement/edit", name: 'appli_entrainement_edit', methods: ['GET'])]
    public function entrainementEdit(): Response
    {
        return $this->render('appli/entrainement_edit.html.twig', [
            'title' => 'Modifier l\'entraînement',
            'workout' => [
                'id' => 1,
                'name' => 'Full Body',
                'exercises' => [
                    ['name' => 'Squats', 'sets' => 3, 'reps' => 12],
                    ['name' => 'Push-ups', 'sets' => 3, 'reps' => 10]
                ]
            ]
        ]);
    }

    #[Route(path: "/entrainement/decouvrir", name: 'appli_entrainement_decouvrir', methods: ['GET'])]
    public function entrainementDecouvrir(): Response
    {
        return $this->render('appli/entrainement_decouvrir.html.twig', [
            'title' => 'Découvrir',
            'workouts' => [
                [
                    'id' => 1,
                    'name' => 'Full Body',
                    'duration' => '45 min',
                    'difficulty' => 'Moyen',
                    'description' => 'Entraînement complet pour tout le corps'
                ],
                [
                    'id' => 2,
                    'name' => 'Upper Body',
                    'duration' => '30 min',
                    'difficulty' => 'Facile',
                    'description' => 'Focus sur le haut du corps'
                ]
            ]
        ]);
    }

    #[Route(path: "/entrainement/{id}", name: 'appli_entrainement_show', methods: ['GET'])]
    public function entrainementShow(int $id): Response
    {
        return $this->render('appli/entrainement.html.twig', [
            'title' => 'Détails de l\'entraînement',
            'workout' => [
                'id' => $id,
                'name' => 'Full Body',
                'duration' => '45 min',
                'difficulty' => 'Moyen',
                'exercises' => [
                    ['name' => 'Squats', 'sets' => 3, 'reps' => 12],
                    ['name' => 'Push-ups', 'sets' => 3, 'reps' => 10]
                ]
            ]
        ]);
    }

    #[Route(path: "/activite", name: 'appli_activite', methods: ['GET'])]
    public function activite(): Response
    {
        return $this->render('appli/activite.html.twig', [
            'title' => 'Activité',
            'activities' => [
                [
                    'id' => 1,
                    'type' => 'workout',
                    'name' => 'Full Body',
                    'date' => '2024-03-20',
                    'duration' => '45 min',
                    'calories' => 350
                ],
                [
                    'id' => 2,
                    'type' => 'meal',
                    'name' => 'Petit déjeuner',
                    'date' => '2024-03-20',
                    'calories' => 450
                ]
            ]
        ]);
    }

    #[Route(path: "/profil", name: 'appli_profil', methods: ['GET'])]
    public function profil(): Response
    {
        return $this->render('appli/profil.html.twig', [
            'title' => 'Profil',
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 30,
                'weight' => 75,
                'height' => 180,
                'goal' => 'Perte de poids',
                'progress' => 65
            ]
        ]);
    }

    #[Route(path: "/profil/{id}/edit", name: 'appli_profil_edit', methods: ['GET'])]
    public function profilEdit(int $id): Response
    {
        return $this->render('appli/profil_edit.html.twig', [
            'title' => 'Modifier le profil',
            'user' => [
                'id' => $id,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 30,
                'weight' => 75,
                'height' => 180,
                'goal' => 'Perte de poids'
            ]
        ]);
    }
}
