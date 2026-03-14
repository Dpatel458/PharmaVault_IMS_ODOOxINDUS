<?php

namespace App\Controller;

use App\Entity\Warehouse;
use App\Form\WarehouseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/warehouse')]
#[IsGranted('ROLE_USER')]
class WarehouseController extends AbstractController
{
    #[Route('/', name: 'app_warehouse_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $warehouses = $entityManager
            ->getRepository(Warehouse::class)
            ->findAll();

        return $this->render('warehouse/index.html.twig', [
            'warehouses' => $warehouses,
        ]);
    }

    #[Route('/new', name: 'app_warehouse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $warehouse = new Warehouse();
        $form = $this->createForm(WarehouseType::class, $warehouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($warehouse);
            $entityManager->flush();

            return $this->redirectToRoute('app_warehouse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('warehouse/new.html.twig', [
            'warehouse' => $warehouse,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_warehouse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Warehouse $warehouse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WarehouseType::class, $warehouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_warehouse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('warehouse/edit.html.twig', [
            'warehouse' => $warehouse,
            'form' => $form->createView(),
        ]);
    }
}
