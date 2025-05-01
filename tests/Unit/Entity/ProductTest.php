<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;
use App\Entity\Product;

/**
 * Ce fichier contient des tests unitaires pour l'entité Task.
 * Les tests permettent de vérifier que l'objet Task se comporte comme attendu
 * sans dépendre d'une base de données ou d'autres services.
 */
class ProductTest extends TestCase
{
    /**
     * Vérifie qu'on peut créer une tâche avec des données valides
     * et que les propriétés sont bien définies.
     * On teste aussi ici le lien entre une tâche et une liste de tâches.
     */
    public function testProductCreationWithValidData()
    {
      // Création d'un nouveau product
      $product = new Product();
      
      // Création d'une nouvelle catégorie test
      $category = new Category();
      $category->setName("Test category");
      
      // On remplit les infos du produit
      $product->setPrice(35);
      $product->setName("Test Product");
      $product->setDescription("crazy description");
      $product->setIsAvailable(0);
      $product->setCategory($category);

      // Vérification des valeurs
      $this->assertEquals(35, $product->getPrice());
      $this->assertEquals("Test Product", $product->getName());
      $this->assertEquals("crazy description", $product->getDescription());
      $this->assertEquals(0, $product->getIsAvailable());
      $this->assertSame($category, $product->getCategory());
    }

}