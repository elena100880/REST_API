<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\Exception;
use App\Controller\JsonResponse;

use App\Entity\ProductInStore;
use App\Repository\ProductInStoreRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Tools\Pagination\Paginator;


class ProductInStoreController extends AbstractController
{
    public function products_list(Request $request): Response  
    /* amount - 0 (not in store), 1 - all products in store (default), 5 - products >5 in store
     * page - from 1, default 1
     * elements - max 20, default 2
     */
    {
        
    //validating parameters:
        try {            
            $amount = $request->query->get('amount') ?? 1;
            
            $page = $request->query->get('page') ?? 1;
            if ( !is_int($page) or $page == 0) throw new Exception ("Invalid value of PAGE");

            $elements = $request->query->get('elements') ?? 2;
            if ( !is_int($elements) or $elements == 0) throw new Exception ("Invalid value of ELEMENTS");
        }
        catch (Exception $e) {
            return new Response ($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        
    //quering products:    
        $dql = "SELECT p FROM App\Entity\ProductInStore p";
        if ($amount == 1);
        elseif ($amount == 0) $dql = $dql.' WHERE p.amount = 0';
        elseif ($amount == 5) $dql = $dql.' WHERE p.amount > 5';
        else return new Response ("Invalid amount", Response::HTTP_BAD_REQUEST);
        
        $em = $this->getDoctrine()->getManager();
        $DQLquery = $em->createQuery($dql)
                                    ->setFirstResult($page * $elements  - $elements)
                                    ->setMaxResults($elements);
        $productsPage = new Paginator($DQLquery);  
        $totalElements = count($productsPage); 

        $returnResponse = ["data" => $productsPage, "total" => $totalElements];
        return $this->json($returnResponse);  
    }
}
