<?php

namespace App\Service;

use App\Entity\Delivery;
use App\Entity\Product;
use App\Entity\Receipt;
use App\Entity\Stock;
use App\Entity\StockAdjustment;
use App\Entity\StockLedger;
use App\Entity\Transfer;
use App\Entity\Warehouse;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class StockMovementService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function processReceipt(Receipt $receipt): void
    {
        $this->em->beginTransaction();
        try {
            foreach ($receipt->getItems() as $item) {
                // Determine warehouse? A receipt normally targets a specific warehouse. 
                // For simplicity, let's assume all receipts go to a default Warehouse, or we could add a field.
                // Let's get the first warehouse as a fallback.
                $warehouse = $this->em->getRepository(Warehouse::class)->findOneBy([]) ?? throw new Exception("No warehouses exist!");
                
                $this->addStock($item->getProduct(), $warehouse, $item->getQuantity(), 'receipt', $receipt->getId());
            }

            $receipt->setStatus('done');
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    public function processDelivery(Delivery $delivery): void
    {
        $this->em->beginTransaction();
        try {
            foreach ($delivery->getItems() as $item) {
                $warehouse = $this->em->getRepository(Warehouse::class)->findOneBy([]) ?? throw new Exception("No warehouses exist!");
                $this->deductStock($item->getProduct(), $warehouse, $item->getQuantity(), 'delivery', $delivery->getId());
            }

            $delivery->setStatus('done');
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    public function processTransfer(Transfer $transfer): void
    {
        $this->em->beginTransaction();
        try {
            foreach ($transfer->getItems() as $item) {
                $this->deductStock($item->getProduct(), $transfer->getSourceLocation(), $item->getQuantity(), 'transfer_out', $transfer->getId());
                $this->addStock($item->getProduct(), $transfer->getDestinationLocation(), $item->getQuantity(), 'transfer_in', $transfer->getId());
            }

            $transfer->setStatus('done');
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    public function processAdjustment(StockAdjustment $adjustment): void
    {
        $this->em->beginTransaction();
        try {
            $diff = $adjustment->getNewQuantity() - $adjustment->getOldQuantity();
            $adjustment->setDifference($diff);

            if ($diff > 0) {
                $this->addStock($adjustment->getProduct(), $adjustment->getWarehouse(), abs($diff), 'adjustment_add', $adjustment->getId());
            } elseif ($diff < 0) {
                $this->deductStock($adjustment->getProduct(), $adjustment->getWarehouse(), abs($diff), 'adjustment_sub', $adjustment->getId());
            }

            $this->em->persist($adjustment);
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    private function addStock(Product $product, Warehouse $warehouse, int $quantity, string $type, ?int $refId): void
    {
        $stock = $this->em->getRepository(Stock::class)->findOneBy([
            'product' => $product,
            'warehouse' => $warehouse
        ]);

        if (!$stock) {
            $stock = new Stock();
            $stock->setProduct($product);
            $stock->setWarehouse($warehouse);
            $stock->setQuantity(0);
            $this->em->persist($stock);
        }

        $stock->setQuantity($stock->getQuantity() + $quantity);

        $ledger = new StockLedger();
        $ledger->setProduct($product);
        $ledger->setWarehouse($warehouse);
        $ledger->setMovementType($type);
        $ledger->setQuantityChange($quantity);
        $ledger->setReferenceId($refId);

        $this->em->persist($ledger);
    }

    private function deductStock(Product $product, Warehouse $warehouse, int $quantity, string $type, ?int $refId): void
    {
        $stock = $this->em->getRepository(Stock::class)->findOneBy([
            'product' => $product,
            'warehouse' => $warehouse
        ]);

        if (!$stock || $stock->getQuantity() < $quantity) {
            throw new Exception("Not enough stock for product " . $product->getName());
        }

        $stock->setQuantity($stock->getQuantity() - $quantity);

        $ledger = new StockLedger();
        $ledger->setProduct($product);
        $ledger->setWarehouse($warehouse);
        $ledger->setMovementType($type);
        $ledger->setQuantityChange(-$quantity);
        $ledger->setReferenceId($refId);

        $this->em->persist($ledger);
    }
}
