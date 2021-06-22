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
    public function product_list(Request $request): Response  
    {
        /* amount - 0 (not in store), 1 - all products in store (default), 5 - products >5 in store
        * page - from 1, default 1
        * elements - max 1000, default 2
        */
            
    //validating parameters:
        function string_is_integer_and_more_zero ($string)
        {
            return (is_numeric($string) and ($string - floor($string) == 0) and $string >= 0 );
        }

        try {            
            $amount = $request->query->get('amount') ?? 1;
            
            $page = ($request->query->get('page')) ?? 1;
            $r = string_is_integer_and_not_zero ($page);
            if (!string_is_integer_and_more_zero ($page)) throw new Exception ("Invalid value of PAGE");
            
            $elements = $request->query->get('elements') ?? 2;
            if ( !string_is_integer_and_more_zero ($elements) or $elements > 1000) throw new Exception ("Invalid value of ELEMENTS");
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

    public function product_delete (Request $request, $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em -> createQueryBuilder()
                            -> delete ('App\Entity\ProductInStore', 'p')
                            -> setParameter ('id', $id)
                            -> where ('p.id = :id')
                            -> getQuery()
                            -> execute();
        if ($queryBuilder) return new Response ("Success", Response::HTTP_OK);
        else return new Response ("Item NOT FOUND", Response::HTTP_NOT_FOUND);
    }

    public function product_add (Request $request): Response
    {
        /* required json data from Request:
         * {    
         *      "name": "Product X",  // 10 characters <=length <= 100 characters
         *      "amount": 1,   // 0 for default
         * }
         */

        $json = file_get_contents('php://input');

    //validating parameters:
        function valid_json($string)  { 
            json_decode($string);
            return json_last_error() === 0;
        }
        try 
        {
            $data = (valid_json($json)) ? json_decode($json, true) : throw new Exception('Invalid json');

            if (!isset($data['name'])) throw new Exception('Invalid key for NAME');
            $name = (strlen($data['name']) < 100 ) ? $data['name'] : throw new Exception ("Invalid length of NAME");

            $amount = $data['amount'] ?? 0;
            if ( !is_int($amount) or $amount < 0) throw new Exception ("Invalid value of AMOUNT");
        }
        catch (Exception $e) {
            return new Response ($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    //adding product to DB:
        $product = new ProductInStore;
        $product->setAmount($amount);
        $product->setName($name);

        $em = $this->getDoctrine()->getManager();
        $em -> persist($product);
        $em -> flush();

        return new Response ("Success", Response::HTTP_OK);
        /**
         * @todo Response if Server error
         */
    }
}
