<?php

namespace App\Controller;

use App\Entity\Stock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stock')]
#[IsGranted('ROLE_USER')]
class StockController extends AbstractController
{
    #[Route('/', name: 'app_stock_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $stocks = $entityManager->getRepository(Stock::class)->findBy([], ['warehouse' => 'ASC', 'product' => 'ASC']);

        return $this->render('inventory/stock_index.html.twig', [
            'stocks' => $stocks,
        ]);
    }
}
