<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Gather some basic stats for the admin dashboard
        $totalUsers = $entityManager->getRepository(\App\Entity\User::class)->count([]);
        $totalProducts = $entityManager->getRepository(\App\Entity\Product::class)->count([]);
        $totalWarehouses = $entityManager->getRepository(\App\Entity\Warehouse::class)->count([]);
        
        // Count active admins vs regular users
        // This is a simplified way to just get all users and filter them;
        // Doctrine doesn't easily let you query JSON arrays natively in DQL without custom functions
        $allUsers = $entityManager->getRepository(\App\Entity\User::class)->findAll();
        $adminCount = 0;
        foreach ($allUsers as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $adminCount++;
            }
        }
        $regularUserCount = $totalUsers - $adminCount;


        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalProducts' => $totalProducts,
                'totalWarehouses' => $totalWarehouses,
                'adminCount' => $adminCount,
                'regularUserCount' => $regularUserCount,
            ]
        ]);
    }
}
