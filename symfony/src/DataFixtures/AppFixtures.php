<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;

class AppFixtures extends Fixture
{

    public function __construct(
        private KernelInterface $kernel
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $path = $this->kernel->getProjectDir() . '/src/DataFixtures/produits.json';
        $products = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        foreach ($products as $data) {
            $product = new Product();
            $product->setName($data['nom']);
            $product->setShade($data['teinte']);
            $product->setPriceHt((string)$data['prix_ht']);
            $product->setStock($data['stock']);
            $product->setAlertThreshold($data['seuil_alerte']);
            $manager->persist($product);
        }
        $manager->flush();
    }
}
