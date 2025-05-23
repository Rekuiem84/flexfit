<?php

namespace App\Controller;

use App\Form\ContactFormType;
use App\Repository\CategoryRepository;
use App\Repository\OrderHistoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SizeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository, OrderHistoryRepository $orderHistoryRepository): Response
    {
        $allProducts = $productRepository->findBy(['isAvailable' => true]);
        shuffle($allProducts);
        $randomProducts = array_slice($allProducts, 0, 3);

        $popularProducts = $orderHistoryRepository->getMostPopularProducts();

        return $this->render('home/index.html.twig', [
            'products' => $randomProducts,
            'categories' => $categoryRepository->getAll(),
            'popularProducts' => $popularProducts
        ]);
    }
    #[Route('/collections', name: 'app_collections')]
    public function collections(): Response
    {
        return $this->render('home/collections.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/collections/{id}', name: 'app_collections_show', methods: ['GET'])]
    public function collections_show(): Response
    {
        return $this->render('home/collection_show.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/products', name: 'app_home_products')]
    public function product(ProductRepository $productRepository, CategoryRepository $categoryRepository, Request $request): Response
    {
        $categoryId = $request->query->get('category');

        $products = $categoryId
            ? $productRepository->findBy(['category' => $categoryId, 'isAvailable' => true])
            : $productRepository->findBy(['isAvailable' => true]);
        // randomize l'ordre des produiits
        shuffle($products);

        return $this->render('home/products.html.twig', [
            'products' => $products,
            'categories' => $categoryRepository->getAll(),
        ]);
    }
    #[Route('/products/{id}', name: 'app_product_shop', methods: ['GET'])]
    public function product_shop(ProductRepository $productRepository, SizeRepository $sizeRepository, $id): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $categoryId = $product->getCategory()->getId();
        // Récupère tous les produits de la même catégorie
        $products = $productRepository->findBy([
            'category' => $categoryId,
            'isAvailable' => true
        ]);

        // Filtre pour exclure le produit actuel
        $filteredProducts = array_filter($products, function ($p) use ($id) {
            return $p->getId() !== (int)$id;
        });
        // Mélange l'ordre des produits
        shuffle($filteredProducts);
        // Garde seulement 3 produits
        $similarProducts = array_slice($filteredProducts, 0, 3);

        // Récupère les tailles disponibles
        $sizes = $sizeRepository->getAll();

        // Affiche la page produit avec les produits similaires
        return $this->render('home/shop.html.twig', [
            'product' => $product,
            'similarProducts' => $similarProducts,
            'sizes' => $sizes,
        ]);
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET'])]
    public function contact(Request $request): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        return $this->render('home/contact.html.twig', ['form' => $form,]);
    }
    #[Route('/cgv', name: 'app_cgv', methods: ['GET'])]
    public function cgv(): Response
    {
        return $this->render('home/cgv.html.twig');
    }
    #[Route('/mentions_legales', name: 'app_mentions_legales', methods: ['GET'])]
    public function mentions_legales(): Response
    {
        return $this->render('home/mentions_legales.html.twig');
    }
    #[Route('/politique-confidentialite', name: 'app_confidentialite', methods: ['GET'])]
    public function confidentialite(): Response
    {
        return $this->render('home/confidentialite.html.twig');
    }
    #[Route('/politique-retours', name: 'app_politique_retours', methods: ['GET'])]
    public function politique_retours(): Response
    {
        return $this->render('home/politique_retours.html.twig');
    }
    #[Route('/cookies', name: 'app_cookies', methods: ['GET'])]
    public function cookies(): Response
    {
        return $this->render('home/cookies.html.twig');
    }
}
