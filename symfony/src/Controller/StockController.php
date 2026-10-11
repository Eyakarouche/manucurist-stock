<?php

namespace App\Controller;

use App\Exception\InsufficientStockException;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StockController extends AbstractController
{
    #[Route('/stock', name: 'stock_index', methods: ['GET'])]
    public function index(
        Request           $request,
        ProductRepository $repository,
    ): Response
    {
        $shade = $request->query->getString('shade');
        $alert = $request->query->getBoolean('alert');

        return $this->render('stock/index.html.twig', [
            'products' => $repository->search(
                $shade ?: null,
                $alert,
            ),
            'shade' => $shade,
            'alert' => $alert,
        ]);
    }

    #[Route(
        '/stock/{id<\d+>}/sell',
        name: 'stock_sell',
        methods: ['POST']
    )]
    public function sell(
        int                    $id,
        Request                $request,
        ProductRepository      $repository,
        EntityManagerInterface $em,
    ): Response
    {
        if (!$this->isCsrfTokenValid(
            'sell_product_' . $id,
            $request->request->getString('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Invalid CSRF token.'
            );
        }

        $product = $repository->find($id);

        if (!$product) {
            $this->addFlash('danger', 'Produit introuvable.');

            return $this->redirectToRoute('stock_index');
        }

        $quantity = $request->request->getInt('quantity');

        if ($quantity <= 0) {
            $this->addFlash(
                'danger',
                'La quantité doit être un entier positif.'
            );

            return $this->redirectToRoute('stock_index');
        }

        try {
            $product->sell($quantity);
            $em->flush();

            $this->addFlash(
                'success',
                sprintf(
                    'Vente enregistrée. Stock restant : %d.',
                    $product->getStock()
                )
            );
        } catch (InsufficientStockException $e) {
            $this->addFlash('danger', $e->getMessage());
        } catch (OptimisticLockException) {
            $this->addFlash('warning', 'Le stock a changé, réessayez.');
        }

        return $this->redirectToRoute('stock_index');
    }
}
