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

use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductInStoreController extends AbstractController
{    
    public function product_list(Request $request): Response  
    {
       /* requirements:
        * amount - 0 (not in store), 1 - all products in store (default), 5 - products >5 in store
        * page - from 1, default 1
        * elements - max 1000, default 2
        */
            
    //validating parameters:
        try {            
            $amount = $request->query->get('amount') ?? 1;
            
            $page = ($request->query->get('page')) ?? 1;
            if (!$this->if_string_is_natural_number($page)) throw new Exception ("Invalid value of PAGE");
            
            $elements = $request->query->get('elements') ?? 2;
            if ( !$this->if_string_is_natural_number($elements) or $elements > 1000) throw new Exception ("Invalid value of ELEMENTS");
        }
        catch (Exception $e) {return new Response ($e->getMessage(), Response::HTTP_BAD_REQUEST);}
        
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
        try {
            $productsPage = new Paginator($DQLquery);
            $totalElements = count($productsPage); 
        }
        catch (\Exception $e) {return new Response ("DB is not available", Response::HTTP_SERVICE_UNAVAILABLE);}
        
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
                            -> getQuery();
        
        try {$result = $queryBuilder -> execute(); }               
        catch (\Exception $e) {return new Response ("DB is not available", Response::HTTP_SERVICE_UNAVAILABLE);}

        if ($queryBuilder) return new Response ("Success", Response::HTTP_OK);
        else return new Response ("Item NOT FOUND", Response::HTTP_NOT_FOUND);
    }

    public function product_add (Request $request, ValidatorInterface $validator): Response
    {
        /* required json data from Request:
         * {    
         *      "name": "Product X", //required
         *      "amount": 1,   // 0 for default
         * }
         */

        $json = file_get_contents('php://input');

    //validating parameters:
        try 
        {
            $data = ($this->valid_json($json)) ? json_decode($json, true) : throw new Exception('Invalid json');

            $name = $data['name'] ?? throw new Exception('Invalid key for NAME');
            if (!is_string($data['name']) or strlen($data['name']) > 5) throw new Exception('Invalid value for NAME');

            $amount = $data['amount'] ?? 0;
            if ( !is_int($amount) or $amount < 0) throw new Exception ("Invalid value of AMOUNT");
         
        }
        catch (\Exception $e) {return new Response ($e->getMessage(), Response::HTTP_BAD_REQUEST); }
        

    //validating and adding product to DB:
        $product = new ProductInStore;
        $product->setName($name);
        $product->setAmount($amount);
        $em = $this->getDoctrine()->getManager();
        $em -> persist($product);
        
        try {$em -> flush();} 
        catch (\Exception $e) {return new Response ("DB is not available", Response::HTTP_SERVICE_UNAVAILABLE);}

        return new Response ("Success", Response::HTTP_OK);
    }

    public function product_edit (Request $request, $id): Response
    {
        /* required json data from Request:
         * {    
         *      "name": "Product X",  
         *      "amount": 1,  
         * }
         */

        $json = file_get_contents('php://input');

    //validating parameters:
        try 
        {
            $data = ($this->valid_json($json)) ? json_decode($json, true) : throw new Exception('Invalid json');

            if (sset($data['name'])) $name = $data['name'];
            if (!is_string($data['name']) or strlen($data['name']) > 5) throw new Exception('Invalid value for NAME');
           
            if (isset($data['amount'])) $amount = $data['amount'];
            if ( !is_int($amount) or $amount < 0) throw new Exception ("Invalid value of AMOUNT");
        }
        catch (Exception $e) {
            return new Response ($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    //updating product in DB:
        

        return new Response ("Success", Response::HTTP_OK);
    }
    
    private function if_string_is_natural_number ($string) {
        return (is_numeric($string) and ($string - floor($string) == 0) and $string > 0 );
    }

    private function valid_json($string)  { 
        json_decode($string); return json_last_error() === 0;
    }
}
