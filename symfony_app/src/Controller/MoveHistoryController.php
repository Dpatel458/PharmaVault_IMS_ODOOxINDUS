<?php

namespace App\Controller;

use App\Entity\StockMove;
use App\Entity\Warehouse;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/inventory')]
#[IsGranted('ROLE_USER')]
class MoveHistoryController extends AbstractController
{
    #[Route('/move-history', name: 'app_move_history', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        // Default list of moves
        $moves = $em->getRepository(StockMove::class)->findBy([], ['date' => 'DESC']);
        $warehouses = $em->getRepository(Warehouse::class)->findAll();
        $products = $em->getRepository(Product::class)->findAll();

        return $this->render('inventory/move_history/index.html.twig', [
            'moves' => $moves,
            'warehouses' => $warehouses,
            'products' => $products,
        ]);
    }

    #[Route('/move-history/api/filter', name: 'api_move_history_filter', methods: ['GET'])]
    public function filterMoves(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $type = $request->query->get('type');
        $warehouseId = $request->query->get('warehouse');
        $productId = $request->query->get('product');
        $search = $request->query->get('search');

        $qb = $em->createQueryBuilder();
        $qb->select('m', 'i', 'p')
           ->from(StockMove::class, 'm')
           ->leftJoin('m.items', 'i')
           ->leftJoin('i.product', 'p')
           ->orderBy('m.date', 'DESC');

        if ($search) {
            $qb->andWhere('m.reference LIKE :search OR m.contact LIKE :search OR p.name LIKE :search OR p.sku LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($warehouseId) {
            $warehouse = $em->getRepository(Warehouse::class)->find($warehouseId);
            if ($warehouse) {
                $qb->andWhere('m.from_location = :wh OR m.to_location = :wh')
                   ->setParameter('wh', $warehouse->getName());
            }
        }

        if ($productId) {
            $qb->andWhere('i.product = :product')
               ->setParameter('product', $productId);
        }

        if ($type === 'inbound') {
            $qb->andWhere('m.to_location LIKE :whPrefix AND m.from_location NOT LIKE :whPrefix')
               ->setParameter('whPrefix', 'WH/%');
        } elseif ($type === 'outbound') {
            $qb->andWhere('m.from_location LIKE :whPrefix AND m.to_location NOT LIKE :whPrefix')
               ->setParameter('whPrefix', 'WH/%');
        } elseif ($type === 'internal') {
            $qb->andWhere('m.from_location LIKE :whPrefix1 AND m.to_location LIKE :whPrefix2')
               ->setParameter('whPrefix1', 'WH/%')
               ->setParameter('whPrefix2', 'WH/%');
        }

        $moves = $qb->getQuery()->getResult();
        $data = [];

        foreach ($moves as $move) {
            // Unroll products to create multiple rows per move if necessary
            if ($move->getItems()->count() > 0) {
                foreach ($move->getItems() as $item) {
                    $this->addMoveRow($data, $move, $item);
                }
            } else {
                // If no items, add single row with empty product
                $this->addMoveRow($data, $move, null);
            }
        }

        return $this->json($data);
    }

    private function addMoveRow(&$data, StockMove $move, $item)
    {
        $ref = $move->getReference() ?? '';
        $colorClass = '';
        if (str_starts_with($ref, 'WH/IN/')) {
            $colorClass = 'text-success fw-bold'; // Receipt -> Green
        } elseif (str_starts_with($ref, 'WH/OUT/')) {
            $colorClass = 'text-danger fw-bold'; // Delivery -> Red
        } elseif (str_starts_with($ref, 'WH/INT/')) {
            $colorClass = 'text-warning fw-bold text-dark'; // Internal -> Yellow
        }

        $statusColor = match($move->getStatus()) {
            'draft' => 'bg-secondary',
            'waiting' => 'bg-warning text-dark',
            'ready' => 'bg-primary',
            'done' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary'
        };

        $data[] = [
            'id' => $move->getId(),
            'reference' => $move->getReference(),
            'date' => $move->getDate()->format('m/d/Y'),
            'contact' => $move->getContact() ?? '-',
            'product' => $item ? $item->getProduct()->getName() : '-',
            'from' => $move->getFromLocation(),
            'to' => $move->getToLocation(),
            'quantity' => $item ? $item->getQuantity() : 0,
            'status' => ucfirst($move->getStatus()),
            'statusColor' => $statusColor,
            'colorClass' => $colorClass
        ];
    }
}
