<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $allProducts = $productRepository->findBy(['isAvailable' => true]);
        shuffle($allProducts);
        $randomProducts = array_slice($allProducts, 0, 3);

        return $this->render('home/index.html.twig', [
            'products' => $randomProducts,
            'categories' => $categoryRepository->getAll(),
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
    public function product_shop(ProductRepository $productRepository, $id): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }
        $categoryId = $product->getCategory()->getId();

        // Récupérer 3 produits de la même catégorie
        $products = $productRepository->findBy(['category' => $categoryId, 'isAvailable' => true]);
        // randomize l'ordre des produiits
        shuffle($products);
        // Limiter à 3 produits
        $products = array_slice($products, 0, 3);
        
        // randomize l'ordre des produiits
        shuffle($products);
        return $this->render('home/shop.html.twig', [
            'product' => $product,
            'similarProducts' => $products,
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
}
