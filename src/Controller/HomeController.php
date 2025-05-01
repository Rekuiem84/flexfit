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
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
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
}
