<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Product;
use App\Form\ProductDetailsImagesType;
use App\Form\ProductEditType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\SizeRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/product')]
final class ProductController extends AbstractController
{
  // todo faire un helper
  public function downloadMedia(EntityManagerInterface $entityManager, SluggerInterface $slugger, $media, $isMainImage, $product): void
  {
    $uploadFileName = pathinfo($media->getClientOriginalName(), PATHINFO_FILENAME); // on récupère le nom du fichier
    $safeFileName = $slugger->slug($uploadFileName); // on génère un nom safe pour le fichier
    $newFileName = $safeFileName . '-' . uniqid() . '.' . $media->guessExtension(); // on génère un nom unique pour le fichier

    $media->move(
      $this->getParameter('media_directory'),
      $newFileName
    ); // on déplace le fichier dans le dossier media_directory

    $media = new Media($newFileName, $isMainImage); // on crée une nouvelle instance de l'entité Media
    $media->setProduct($product); // on lie le média au produit
    $entityManager->persist($media);
  }

  public function removePreviousMedia(EntityManagerInterface $entityManager, $product): void
  {
    $currentMainImage = $product->getMainImage(); // on supprime l'image principale actuelle
    if ($currentMainImage) {
      $entityManager->remove($currentMainImage);
      // on supprime l'image du dossier media_directory
      $currentMainImageFileName = $currentMainImage->getUrl();
      $currentMainImagePath = $this->getParameter('media_directory') . '/' . $currentMainImageFileName;
      if (file_exists($currentMainImagePath)) {
        unlink($currentMainImagePath);
      }
    }
  }

  #[Route(name: 'app_product_index', methods: ['GET'])]
  public function index(ProductRepository $productRepository): Response
  {
    return $this->render('product/index.html.twig', [
      'products' => $productRepository->findAll(),
    ]);
  }

  #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
  {
    $product = new Product();
    $form = $this->createForm(ProductType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

      $mainImageFile = $form->get('mainImage')->getData(); // on récupère le fichier image depuis le formulaire
      $detailsImagesFiles = $form->get('detailsImages')->getData(); // on récupère les fichiers images depuis le formulaire
      $entityManager->persist($product);

      if ($mainImageFile) { // on vérifie si un fichier a été envoyé                
        $this->downloadMedia($entityManager, $slugger, media: $mainImageFile, isMainImage: true, product: $product);
      }
      if ($detailsImagesFiles) { // on vérifie si des fichiers ont été envoyés
        foreach ($detailsImagesFiles as $detailsImageFile) {
          $this->downloadMedia($entityManager, $slugger, media: $detailsImageFile, isMainImage: false, product: $product);
        }
      }
      $entityManager->flush();


      return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('product/new.html.twig', [
      'product' => $product,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
  public function show(Product $product, SizeRepository $size): Response
  {
    return $this->render('product/show.html.twig', [
      'product' => $product,
      'sizes' => $size->getAll(),
    ]);
  }

  #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
  {
    $form = $this->createForm(ProductEditType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $mainImageFile = $form->get('mainImage')->getData(); // on récupère le fichier image depuis le formulaire
      $entityManager->persist($product);

      if ($mainImageFile) { // on vérifie si un fichier a été uploadé  
        $currentMainImage = $product->getMainImage(); // on supprime l'image principale actuelle
        if ($currentMainImage) {
          $entityManager->remove($currentMainImage);
          // on supprime l'image du dossier media_directory
          $currentMainImageFileName = $currentMainImage->getUrl();
          $currentMainImagePath = $this->getParameter('media_directory') . '/' . $currentMainImageFileName;
          if (file_exists($currentMainImagePath)) {
            unlink($currentMainImagePath);
          }
        }

        $uploadFileName = pathinfo($mainImageFile->getClientOriginalName(), PATHINFO_FILENAME); // on récupère le nom du fichier
        $safeFileName = $slugger->slug($uploadFileName); // on génère un nom safe pour le fichier
        $newFileName = $safeFileName . '-' . uniqid() . '.' . $mainImageFile->guessExtension(); // on génère un nom unique pour le fichier

        $mainImageFile->move(
          $this->getParameter('media_directory'),
          $newFileName
        ); // on déplace le fichier dans le dossier media_directory

        $media = new Media($newFileName, true); // on crée une nouvelle instance de l'entité Media
        $media->setProduct($product); // on lie le média au produit
        $entityManager->persist($media);
      }

      $entityManager->flush();

      return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }


    return $this->render('product/edit.html.twig', [
      'product' => $product,
      'form' => $form,
    ]);
  }

  #[Route('/{id}/edit/images', name: 'app_product_edit_images', methods: ['GET', 'POST'])]
  public function editImages(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
  {
    $form = $this->createForm(ProductDetailsImagesType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $detailsImagesFiles = $form->get('detailsImages')->getData(); // on récupère le fichier image depuis le formulaire
      $entityManager->persist($product);

      if ($detailsImagesFiles) { // on vérifie si un/des fichier a été uploadé  
        foreach ($detailsImagesFiles as $newDetailsImage) { // on boucle sur les fichiers uploadés
          $uploadFileName = pathinfo($newDetailsImage->getClientOriginalName(), PATHINFO_FILENAME); // on récupère le nom du fichier
          $safeFileName = $slugger->slug($uploadFileName); // on génère un nom safe pour le fichier
          $newFileName = $safeFileName . '-' . uniqid() . '.' . $newDetailsImage->guessExtension(); // on génère un nom unique pour le fichier

          $newDetailsImage->move(
            $this->getParameter('media_directory'),
            $newFileName
          ); // on déplace le fichier dans le dossier media_directory

          $media = new Media($newFileName, false); // on crée une nouvelle instance de l'entité Media
          $media->setProduct($product); // on lie le média au produit
          $entityManager->persist($media);
        }
      }

      $entityManager->flush();

      // todo ajax pour reload la page et afficher les images
      return $this->render('product/edit.html.twig', [
        'product' => $product,
        'form' => $form,
      ]);
    }

    return $this->render('product/edit-images.html.twig', [
      'product' => $product,
      'form' => $form,
    ]);
  }


  #[Route('{productId}/delete/{imageId}', name: 'app_product_delete_image', methods: ['POST'])]
  public function deleteImage(Request $request, EntityManagerInterface $entityManager, $imageId, $productId): Response
  {
    $product = $entityManager->getRepository(Product::class)->find($productId);
    $media = $entityManager->getRepository(Media::class)->find($imageId);
    $form = $this->createForm(ProductEditType::class, $product);

    if ($this->isCsrfTokenValid('delete' . $media->getId(), $request->getPayload()->getString('_token'))) {
      $entityManager->remove($media);
      $entityManager->flush();
    }

    // todo ajax pour reload la page et afficher les images
    return $this->render('product/edit-images.html.twig', [
      'product' => $product,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
  public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
  {
    if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->getPayload()->getString('_token'))) {
      // First, remove all associated media files and their physical files
      foreach ($product->getMedia() as $media) {
        // Delete the physical file
        $mediaPath = $this->getParameter('media_directory') . '/' . $media->getUrl();
        if (file_exists($mediaPath)) {
          unlink($mediaPath);
        }
        // Remove the media entity
        $entityManager->remove($media);
      }
      
      // Now we can safely remove the product
      $entityManager->remove($product);
      $entityManager->flush();
    }

    return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
  }
}
