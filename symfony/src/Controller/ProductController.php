<?php

namespace App\Controller;

use App\Exception\InsufficientStockException;
use App\Repository\ProductRepository;
use App\SaleRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository      $repository,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    #[Route('/products', methods: 'GET', format: 'json')]
    public function list(
        #[MapQueryParameter] ?string $shade = null,
        #[MapQueryParameter] bool    $alert = false,
    ): JsonResponse
    {
        return $this->json($this->repository->search($shade, $alert));
    }

    #[Route('/products/{id<\d+>}/sales', methods: ['POST'], format: 'json')]
    public function sell(
        int                              $id,
        #[MapRequestPayload] SaleRequest $sale
    ): JsonResponse
    {
        $product = $this->repository->find($id);

        if (!$product) {
            return $this->json([
                'error' => 'Product not found.',
            ], 404);
        }

        try {
            $product->sell($sale->quantity);
        } catch (InsufficientStockException $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 409);
        }

        $this->em->flush();

        return $this->json([
            'message' => 'Sale recorded successfully.',
            'id' => $product->id,
            'remainingStock' => $product->getStock(),
        ]);
    }

    #[Route('/stock/alerts', methods: 'GET', format: 'json')]
    public function alerts(): JsonResponse
    {
        return $this->json($this->repository->search(inAlert: true));
    }
}
