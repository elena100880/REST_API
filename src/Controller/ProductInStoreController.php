<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Controller\JsonResponse;

use App\Entity\ProductInStore;
use App\Repository\ProductInStoreRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Tools\Pagination\Paginator;


class ProductInStoreController extends AbstractController
{
    public function products_list(): Response
    {
        
        
        
        
        
        $returnResponse = ["data" => 1, "total" => 5];
        return $this->json($returnResponse);  
    }
}
