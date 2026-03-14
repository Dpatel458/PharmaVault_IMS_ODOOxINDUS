<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Total products
        $totalProducts = $entityManager->getRepository(\App\Entity\Product::class)->count([]);

        // Low stock items (Quantity < 10)
        $lowStockQuery = $entityManager->createQuery(
            'SELECT COUNT(s.id) FROM App\Entity\Stock s WHERE s.quantity > 0 AND s.quantity < 10'
        );
        $lowStockItems = $lowStockQuery->getSingleScalarResult();

        // Out of stock items (Quantity = 0)
        $outOfStockQuery = $entityManager->createQuery(
            'SELECT COUNT(s.id) FROM App\Entity\Stock s WHERE s.quantity = 0'
        );
        $outOfStockItems = $outOfStockQuery->getSingleScalarResult();

        // Pending Receipts (Draft)
        $pendingReceipts = $entityManager->getRepository(\App\Entity\Receipt::class)->count(['status' => 'draft']);

        // Pending Deliveries (Draft)
        $pendingDeliveries = $entityManager->getRepository(\App\Entity\Delivery::class)->count(['status' => 'draft']);

        // Scheduled Transfers (Draft)
        $scheduledTransfers = $entityManager->getRepository(\App\Entity\Transfer::class)->count(['status' => 'draft']);

        return $this->render('dashboard/index.html.twig', [
            'metrics' => [
                'totalProducts' => $totalProducts,
                'lowStockItems' => $lowStockItems,
                'outOfStockItems' => $outOfStockItems,
                'pendingReceipts' => $pendingReceipts,
                'pendingDeliveries' => $pendingDeliveries,
                'scheduledTransfers' => $scheduledTransfers,
            ],
        ]);
    }
}
