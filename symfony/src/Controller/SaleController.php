<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class SaleController extends AbstractController
{
    #[Route('/api/sales', name: 'api_sales_create', methods: ['POST'])]
    public function create(
        Request $request,
        ProductRepository $products,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $data = $request->toArray();
        } catch (\JsonException) {
            return $this->json([
                'error' => 'Invalid JSON request.',
            ], 400);
        }

        $productId = $data['productId'] ?? null;
        $quantity = $data['quantity'] ?? null;

        if (
            !is_int($productId) || $productId <= 0 ||
            !is_int($quantity) || $quantity <= 0
        ) {
            return $this->json([
                'error' => 'productId and quantity must be positive integers.',
            ], 400);
        }

        $product = $products->find($productId);

        if (!$product) {
            return $this->json([
                'error' => 'Product not found.',
            ], 404);
        }

        if ($product->getStock() < $quantity) {
            return $this->json([
                'error' => 'Insufficient stock.',
                'availableStock' => $product->getStock(),
                'requestedQuantity' => $quantity,
            ], 409);
        }

        $product->setStock(
            $product->getStock() - $quantity
        );

        $entityManager->flush();

        return $this->json([
            'message' => 'Sale recorded successfully.',
            'productId' => $product->id,
            'remainingStock' => $product->getStock(),
        ]);
    }
}
