<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Location;
use App\Entity\Product;
use App\Entity\Warehouse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $em): Response
    {
        $query = trim(string: (string) $request->query->get('q', ''));
        
        $results = [
            'products' => [],
            'categories' => [],
            'warehouses' => [],
            'locations' => [],
        ];

        if ($query !== '') {
            // Search Products (by name or SKU)
            $results['products'] = $em->getRepository(Product::class)
                ->createQueryBuilder('p')
                ->where('p.name LIKE :query')
                ->orWhere('p.sku LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
                
            // Search Categories
            $results['categories'] = $em->getRepository(Category::class)
                ->createQueryBuilder('c')
                ->where('c.name LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
                
            // Search Warehouses
            $results['warehouses'] = $em->getRepository(Warehouse::class)
                ->createQueryBuilder('w')
                ->where('w.name LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
                
            // Search Locations
            $results['locations'] = $em->getRepository(Location::class)
                ->createQueryBuilder('l')
                ->where('l.name LIKE :query')
                ->orWhere('l.shortCode LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
        }

        return $this->render('search/index.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
