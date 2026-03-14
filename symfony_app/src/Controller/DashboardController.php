<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // 1. Total products
        $totalProducts = $entityManager->getRepository(\App\Entity\Product::class)->count([]);

        // 2. Total warehouses (Locations) - AA NAVU UMERYU CHE
        $totalWarehouses = $entityManager->getRepository(\App\Entity\Warehouse::class)->count([]);

        // 3. Low stock items (0 < Quantity < 10)
        $lowStockQuery = $entityManager->createQuery(
            'SELECT COUNT(s.id) FROM App\Entity\Stock s WHERE s.quantity > 0 AND s.quantity < 10'
        );
        $lowStockItems = $lowStockQuery->getSingleScalarResult();

        // 4. Out of stock items (Quantity = 0)
        $outOfStockQuery = $entityManager->createQuery(
            'SELECT COUNT(s.id) FROM App\Entity\Stock s WHERE s.quantity = 0'
        );
        $outOfStockItems = $outOfStockQuery->getSingleScalarResult();

        // 5. Pending Receipts (Draft)
        $pendingReceipts = $entityManager->getRepository(\App\Entity\Receipt::class)->count(['status' => 'draft']);

        // 6. Pending Deliveries (Draft)
        $pendingDeliveries = $entityManager->getRepository(\App\Entity\Delivery::class)->count(['status' => 'draft']);

        // 7. Scheduled Transfers (Draft)
        $scheduledTransfers = $entityManager->getRepository(\App\Entity\Transfer::class)->count(['status' => 'draft']);

        // Badho data metrics array ma Twig ne moklo
        return $this->render('dashboard/index.html.twig', [
            'metrics' => [
                'totalProducts' => $totalProducts,
                'totalWarehouses' => $totalWarehouses,
                'lowStockItems' => $lowStockItems,
                'outOfStockItems' => $outOfStockItems,
                'pendingReceipts' => $pendingReceipts,
                'pendingDeliveries' => $pendingDeliveries,
                'scheduledTransfers' => $scheduledTransfers,
            ],
        ]);
    }
}