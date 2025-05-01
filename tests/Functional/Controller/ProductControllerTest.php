<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Category;
use App\Entity\Media;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SizeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProductControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $productRepository;
    private $sizeRepository;
    private $testUser;
    private $userRepository;
    private $category;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->productRepository = static::getContainer()->get(ProductRepository::class);
        $this->sizeRepository = static::getContainer()->get(SizeRepository::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);

        // Create a test category
        $this->category = new Category();
        $this->category->setName('Test Category');
        $this->entityManager->persist($this->category);

        // Create a test user with a unique email
        $testEmail = 'test' . uniqid() . '@example.com';
        $this->testUser = new User();
        $this->testUser->setEmail($testEmail);
        $this->testUser->setUsername('testuser');
        
        $userPasswordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $userPasswordHasher->hashPassword($this->testUser, 'password123');
        $this->testUser->setPassword($hashedPassword);
        
        $this->entityManager->persist($this->testUser);
        $this->entityManager->flush();

        // Log in the user and start the session
        $this->client->loginUser($this->testUser);
        $this->client->request('GET', '/');
        $this->client->followRedirects();
    }

    private function createTestProduct(): Product
    {
        $product = new Product();
        $product->setName('Test Product');
        $product->setDescription('Test Description');
        $product->setPrice(99.99);
        $product->setIsAvailable(1);
        $product->setCategory($this->category);
        return $product;
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/product');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Product index');
    }

    public function testNewProduct(): void
    {
        $crawler = $this->client->request('GET', '/product/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Create new Product');

        // Create a temporary file for testing upload
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');
        $mainImage = new UploadedFile($tempFile, 'test.jpg', 'image/jpeg', null, true);

        $form = $crawler->filter('form[name="product"]')->form([
            'product[name]' => 'Test Product',
            'product[description]' => 'Test Description',
            'product[price]' => '99.99',
            'product[isAvailable]' => '1',
            'product[category]' => $this->category->getId(),
        ]);
        $form['product[mainImage]']->upload($mainImage);

        $this->client->submit($form);

        $this->assertResponseRedirects('/product');
        $this->assertNotNull($this->productRepository->findOneBy(['name' => 'Test Product']));
    }

    public function testShowProduct(): void
    {
        // Create a test product
        $product = $this->createTestProduct();
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->client->request('GET', '/product/' . $product->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Product');
    }

    public function testEditProduct(): void
    {
        // Create a test product
        $product = $this->createTestProduct();
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/product/' . $product->getId() . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Edit Product');

        // Create a temporary file for testing upload
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');
        $mainImage = new UploadedFile($tempFile, 'test.jpg', 'image/jpeg', null, true);

        $form = $crawler->filter('form[name="product"]')->form([
            'product[name]' => 'Updated Product',
            'product[description]' => 'Updated Description',
            'product[price]' => '149.99',
            'product[isAvailable]' => '1',
            'product[category]' => $this->category->getId(),
        ]);
        $form['product[mainImage]']->upload($mainImage);

        $this->client->submit($form);

        $this->assertResponseRedirects('/product');
        $updatedProduct = $this->productRepository->find($product->getId());
        $this->assertEquals('Updated Product', $updatedProduct->getName());
    }

    public function testDeleteProduct(): void
    {
        // Create a test product
        $product = $this->createTestProduct();
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $productId = $product->getId();
        
        $this->client->request('GET', '/product');
        $token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('delete' . $productId)->getValue();
        
        $this->client->request('POST', '/product/' . $productId, [
            '_token' => $token,
        ]);

        $this->assertResponseRedirects('/product');
        $this->assertNull($this->productRepository->find($productId));
    }

    public function testEditProductImages(): void
    {
        // Create a test product
        $product = $this->createTestProduct();
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/product/' . $product->getId() . '/edit/images');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Edit Product Images');

        // Create temporary files for testing upload
        $tempFile1 = tempnam(sys_get_temp_dir(), 'test1');
        $tempFile2 = tempnam(sys_get_temp_dir(), 'test2');
        file_put_contents($tempFile1, 'test content 1');
        file_put_contents($tempFile2, 'test content 2');
        $image1 = new UploadedFile($tempFile1, 'test1.jpg', 'image/jpeg', null, true);
        $image2 = new UploadedFile($tempFile2, 'test2.jpg', 'image/jpeg', null, true);

        $form = $crawler->filter('form[name="product"]')->form();
        $form['product[detailsImages][]']->upload([$image1, $image2]);

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $updatedProduct = $this->productRepository->find($product->getId());
        $this->assertGreaterThan(0, count($updatedProduct->getDetailsImages()));
    }

    public function testDeleteProductImage(): void
    {
        // Create a test product with an image
        $product = $this->createTestProduct();
        
        $media = new Media('test.jpg', false);
        $media->setProduct($product);
        $product->addMedium($media);
        
        $this->entityManager->persist($product);
        $this->entityManager->persist($media);
        $this->entityManager->flush();

        $this->client->request('GET', '/product');
        $token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('delete' . $media->getId())->getValue();
        
        $this->client->request('POST', '/product/' . $product->getId() . '/delete/' . $media->getId(), [
            '_token' => $token,
        ]);

        $this->assertResponseIsSuccessful();
        $updatedProduct = $this->productRepository->find($product->getId());
        $this->assertEquals(0, count($updatedProduct->getDetailsImages()));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up test data
        if ($this->testUser) {
            $user = $this->userRepository->find($this->testUser->getId());
            if ($user) {
                $this->entityManager->remove($user);
                $this->entityManager->flush();
            }
        }

        // Clean up test category
        if ($this->category && $this->category->getId()) {
            $category = $this->entityManager->getRepository(Category::class)->find($this->category->getId());
            if ($category) {
                // Remove any products associated with this category first
                foreach ($category->getProducts() as $product) {
                    $this->entityManager->remove($product);
                }
                $this->entityManager->remove($category);
                $this->entityManager->flush();
            }
        }
    }
}
