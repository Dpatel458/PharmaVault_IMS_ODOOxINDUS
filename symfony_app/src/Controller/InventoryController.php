<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Receipt;
use App\Entity\ReceiptItem;
use App\Service\StockMovementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/inventory')]
#[IsGranted('ROLE_USER')]
class InventoryController extends AbstractController
{
    #[Route('/receipts', name: 'app_receipt_index', methods: ['GET'])]
    public function receiptIndex(EntityManagerInterface $entityManager): Response
    {
        $receipts = $entityManager->getRepository(Receipt::class)->findBy([], ['id' => 'DESC']);

        return $this->render('inventory/receipt_index.html.twig', [
            'receipts' => $receipts,
        ]);
    }

    #[Route('/receipts/new', name: 'app_receipt_new', methods: ['POST'])]
    public function newReceipt(Request $request, EntityManagerInterface $entityManager): Response
    {
        $supplier = $request->request->get('supplier', 'Unknown Supplier');
        
        $receipt = new Receipt();
        $receipt->setSupplier($supplier);
        
        $entityManager->persist($receipt);
        $entityManager->flush();

        return $this->redirectToRoute('app_receipt_show', ['id' => $receipt->getId()]);
    }

    #[Route('/receipts/{id}', name: 'app_receipt_show', methods: ['GET'])]
    public function showReceipt(Receipt $receipt, EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('inventory/receipt_show.html.twig', [
            'receipt' => $receipt,
            'products' => $products,
        ]);
    }

    #[Route('/receipts/{id}/add-item', name: 'app_receipt_add_item', methods: ['POST'])]
    public function addReceiptItem(Request $request, Receipt $receipt, EntityManagerInterface $entityManager): Response
    {
        if ($receipt->getStatus() !== 'draft') {
            $this->addFlash('danger', 'Cannot add items to a validated receipt.');
            return $this->redirectToRoute('app_receipt_show', ['id' => $receipt->getId()]);
        }

        $productId = $request->request->get('product_id');
        $quantity = (int) $request->request->get('quantity');

        if ($productId && $quantity > 0) {
            $product = $entityManager->getRepository(Product::class)->find($productId);
            if ($product) {
                $item = new ReceiptItem();
                $item->setProduct($product);
                $item->setQuantity($quantity);
                
                $receipt->addItem($item);
                $entityManager->flush();
                $this->addFlash('success', 'Item added.');
            }
        }

        return $this->redirectToRoute('app_receipt_show', ['id' => $receipt->getId()]);
    }

    #[Route('/receipts/{id}/validate', name: 'app_receipt_validate', methods: ['POST'])]
    public function validateReceipt(Receipt $receipt, StockMovementService $stockService): Response
    {
        if ($receipt->getStatus() !== 'draft') {
            $this->addFlash('danger', 'Receipt is already validated.');
            return $this->redirectToRoute('app_receipt_show', ['id' => $receipt->getId()]);
        }

        if ($receipt->getItems()->isEmpty()) {
            $this->addFlash('danger', 'Cannot validate an empty receipt.');
            return $this->redirectToRoute('app_receipt_show', ['id' => $receipt->getId()]);
        }

        try {
            $stockService->processReceipt($receipt);
            $this->addFlash('success', 'Receipt validated successfully. Stock has been updated.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Validation failed: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_receipt_show', ['id' => $receipt->getId()]);
    }

    #[Route('/deliveries', name: 'app_delivery_index', methods: ['GET'])]
    public function deliveryIndex(EntityManagerInterface $entityManager): Response
    {
        $deliveries = $entityManager->getRepository(\App\Entity\Delivery::class)->findBy([], ['id' => 'DESC']);

        return $this->render('inventory/delivery_index.html.twig', [
            'deliveries' => $deliveries,
        ]);
    }

    #[Route('/deliveries/new', name: 'app_delivery_new', methods: ['POST'])]
    public function newDelivery(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customer = $request->request->get('customer', 'Unknown Customer');
        
        $delivery = new \App\Entity\Delivery();
        $delivery->setCustomer($customer);
        
        $entityManager->persist($delivery);
        $entityManager->flush();

        return $this->redirectToRoute('app_delivery_show', ['id' => $delivery->getId()]);
    }

    #[Route('/deliveries/{id}', name: 'app_delivery_show', methods: ['GET'])]
    public function showDelivery(\App\Entity\Delivery $delivery, EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('inventory/delivery_show.html.twig', [
            'delivery' => $delivery,
            'products' => $products,
        ]);
    }

    #[Route('/deliveries/{id}/add-item', name: 'app_delivery_add_item', methods: ['POST'])]
    public function addDeliveryItem(Request $request, \App\Entity\Delivery $delivery, EntityManagerInterface $entityManager): Response
    {
        if ($delivery->getStatus() !== 'draft') {
            $this->addFlash('danger', 'Cannot add items to a validated delivery.');
            return $this->redirectToRoute('app_delivery_show', ['id' => $delivery->getId()]);
        }

        $productId = $request->request->get('product_id');
        $quantity = (int) $request->request->get('quantity');

        if ($productId && $quantity > 0) {
            $product = $entityManager->getRepository(Product::class)->find($productId);
            if ($product) {
                $item = new \App\Entity\DeliveryItem();
                $item->setProduct($product);
                $item->setQuantity($quantity);
                
                $delivery->addItem($item);
                $entityManager->flush();
                $this->addFlash('success', 'Item added.');
            }
        }

        return $this->redirectToRoute('app_delivery_show', ['id' => $delivery->getId()]);
    }

    #[Route('/deliveries/{id}/validate', name: 'app_delivery_validate', methods: ['POST'])]
    public function validateDelivery(\App\Entity\Delivery $delivery, StockMovementService $stockService): Response
    {
        if ($delivery->getStatus() !== 'draft') {
            $this->addFlash('danger', 'Delivery is already validated.');
            return $this->redirectToRoute('app_delivery_show', ['id' => $delivery->getId()]);
        }

        if ($delivery->getItems()->isEmpty()) {
            $this->addFlash('danger', 'Cannot validate an empty delivery.');
            return $this->redirectToRoute('app_delivery_show', ['id' => $delivery->getId()]);
        }

        try {
            $stockService->processDelivery($delivery);
            $this->addFlash('success', 'Delivery validated successfully. Stock has been deducted.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Validation failed: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_delivery_show', ['id' => $delivery->getId()]);
    }

    #[Route('/transfers', name: 'app_transfer_index', methods: ['GET'])]
    public function transferIndex(EntityManagerInterface $entityManager): Response
    {
        $transfers = $entityManager->getRepository(\App\Entity\Transfer::class)->findBy([], ['id' => 'DESC']);
        $warehouses = $entityManager->getRepository(\App\Entity\Warehouse::class)->findAll();

        return $this->render('inventory/transfer_index.html.twig', [
            'transfers' => $transfers,
            'warehouses' => $warehouses,
        ]);
    }

    #[Route('/transfers/new', name: 'app_transfer_new', methods: ['POST'])]
    public function newTransfer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sourceId = $request->request->get('source_id');
        $destId = $request->request->get('dest_id');

        if ($sourceId === $destId) {
            $this->addFlash('danger', 'Source and Destination cannot be the same.');
            return $this->redirectToRoute('app_transfer_index');
        }

        $source = $entityManager->getRepository(\App\Entity\Warehouse::class)->find($sourceId);
        $dest = $entityManager->getRepository(\App\Entity\Warehouse::class)->find($destId);

        if (!$source || !$dest) {
            $this->addFlash('danger', 'Invalid warehouses selected.');
            return $this->redirectToRoute('app_transfer_index');
        }

        $transfer = new \App\Entity\Transfer();
        $transfer->setSourceLocation($source);
        $transfer->setDestinationLocation($dest);
        
        $entityManager->persist($transfer);
        $entityManager->flush();

        return $this->redirectToRoute('app_transfer_show', ['id' => $transfer->getId()]);
    }

    #[Route('/transfers/{id}', name: 'app_transfer_show', methods: ['GET'])]
    public function showTransfer(\App\Entity\Transfer $transfer, EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('inventory/transfer_show.html.twig', [
            'transfer' => $transfer,
            'products' => $products,
        ]);
    }

    #[Route('/transfers/{id}/add-item', name: 'app_transfer_add_item', methods: ['POST'])]
    public function addTransferItem(Request $request, \App\Entity\Transfer $transfer, EntityManagerInterface $entityManager): Response
    {
        if ($transfer->getStatus() !== 'draft') {
            $this->addFlash('danger', 'Cannot add items to a validated transfer.');
            return $this->redirectToRoute('app_transfer_show', ['id' => $transfer->getId()]);
        }

        $productId = $request->request->get('product_id');
        $quantity = (int) $request->request->get('quantity');

        if ($productId && $quantity > 0) {
            $product = $entityManager->getRepository(Product::class)->find($productId);
            if ($product) {
                $item = new \App\Entity\TransferItem();
                $item->setProduct($product);
                $item->setQuantity($quantity);
                
                $transfer->addItem($item);
                $entityManager->flush();
                $this->addFlash('success', 'Item added.');
            }
        }

        return $this->redirectToRoute('app_transfer_show', ['id' => $transfer->getId()]);
    }

    #[Route('/transfers/{id}/validate', name: 'app_transfer_validate', methods: ['POST'])]
    public function validateTransfer(\App\Entity\Transfer $transfer, StockMovementService $stockService): Response
    {
        if ($transfer->getStatus() !== 'draft') {
            $this->addFlash('danger', 'Transfer is already validated.');
            return $this->redirectToRoute('app_transfer_show', ['id' => $transfer->getId()]);
        }

        if ($transfer->getItems()->isEmpty()) {
            $this->addFlash('danger', 'Cannot validate an empty transfer.');
            return $this->redirectToRoute('app_transfer_show', ['id' => $transfer->getId()]);
        }

        try {
            $stockService->processTransfer($transfer);
            $this->addFlash('success', 'Transfer validated successfully. Stock has been moved.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Validation failed: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_transfer_show', ['id' => $transfer->getId()]);
    }

    #[Route('/adjustments', name: 'app_adjustment_index', methods: ['GET'])]
    public function adjustmentIndex(EntityManagerInterface $entityManager): Response
    {
        $adjustments = $entityManager->getRepository(\App\Entity\StockAdjustment::class)->findBy([], ['id' => 'DESC']);
        return $this->render('inventory/adjustment_index.html.twig', [
            'adjustments' => $adjustments,
        ]);
    }

    #[Route('/adjustments/new', name: 'app_adjustment_new', methods: ['GET', 'POST'])]
    public function newAdjustment(Request $request, EntityManagerInterface $entityManager, StockMovementService $stockService): Response
    {
        if ($request->isMethod('POST')) {
            $warehouseId = $request->request->get('warehouse_id');
            $productId = $request->request->get('product_id');
            $newQuantity = (int) $request->request->get('new_quantity');
            $reason = $request->request->get('reason');

            $warehouse = $entityManager->getRepository(\App\Entity\Warehouse::class)->find($warehouseId);
            $product = $entityManager->getRepository(Product::class)->find($productId);

            if ($warehouse && $product && $newQuantity >= 0) {
                $stock = $entityManager->getRepository(\App\Entity\Stock::class)->findOneBy([
                    'warehouse' => $warehouse,
                    'product' => $product
                ]);
                $oldQuantity = $stock ? $stock->getQuantity() : 0;

                if ($oldQuantity !== $newQuantity) {
                    $adjustment = new \App\Entity\StockAdjustment();
                    $adjustment->setProduct($product);
                    $adjustment->setWarehouse($warehouse);
                    $adjustment->setOldQuantity($oldQuantity);
                    $adjustment->setNewQuantity($newQuantity);

                    try {
                        $stockService->processAdjustment($adjustment);
                        $this->addFlash('success', 'Stock adjustment applied.');
                        return $this->redirectToRoute('app_adjustment_index');
                    } catch (\Exception $e) {
                        $this->addFlash('danger', 'Failed to adjust stock: ' . $e->getMessage());
                    }
                } else {
                    $this->addFlash('warning', 'The new quantity is the same as the current quantity.');
                }
            } else {
                $this->addFlash('danger', 'Invalid input parameters.');
            }
        }

        $warehouses = $entityManager->getRepository(\App\Entity\Warehouse::class)->findAll();
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('inventory/adjustment_new.html.twig', [
            'warehouses' => $warehouses,
            'products' => $products,
        ]);
    }
}
