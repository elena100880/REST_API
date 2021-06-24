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

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;

class ProductInStoreController extends AbstractFOSRestController
{    
    public function product_list(Request $request)
    {
       /* required data from Request:
        * amount - 0 (not in store), 1 - all products in store (default), 5 - products >5 in store
        * page - from 1, default 1
        * elements - max 1000, default 2
        */
            
    //validating parameters:
        try {            
            $amount = $request->query->get('amount') ?? 1;
            
            $page = ($request->query->get('page')) ?? 1;
            $this->if_string_is_natural_number($page);
            
            $elements = $request->query->get('elements') ?? 2;
            $this->is_elements_valid($elements);
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
            //$productsPage = $DQLquery ->getResult();
            $productsPage = new Paginator($DQLquery);
            $totalElements = count($productsPage); 
        }
        catch (\Exception $e) {
            $message = $e->getMessage();
            return new Response ("Developer info: $message<br><br> DB is not available", Response::HTTP_SERVICE_UNAVAILABLE);
        }
        $returnResponse = ["data" => $productsPage, "total" => $totalElements];
        
        $view = $this->view($returnResponse, 200);
        return $view;
    }

    /**
     * @View()
     */
    public function product_delete (Request $request, $id)
    {        
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em -> createQueryBuilder()
                            -> delete ('App\Entity\ProductInStore', 'p')
                            -> setParameter ('id', $id)
                            -> where ('p.id = :id')
                            -> getQuery();
        
        try {$result = $queryBuilder -> execute(); }               
        catch (\Exception $e) {
            $message = $e->getMessage();
            return new Response ("Developer info: $message.<br><br>
                                DB is not available", Response::HTTP_SERVICE_UNAVAILABLE);
        }
        
        if ($result) $view = $this->view('Delete Success', 200);
        else $view = $this->view('Item NOT FOUND', 404);
        return $view;
    }

    public function product_add (Request $request)
    {
        /* required json data from Request body:
         * {    
         *      "name": "Product X", //
         *      "amount": 1,   // 0 for default
         * }
         */

        $json = file_get_contents('php://input');

    //validating parameters:
        try 
        {
            $this->valid_json($json);
            $data = json_decode($json, true);

            $name = trim($data['name']) ?? null;
            $this->is_name_valid ($name);
            
            $amount = $data['amount'] ?? 0;
            $this->is_amount_valid ($amount);
        }
        catch (\Exception $e) {return new Response ($e->getMessage(), Response::HTTP_BAD_REQUEST); }
        

    //validating and adding product to DB:
        $product = new ProductInStore;
        $product->setName($name);
        $product->setAmount($amount);
        $em = $this->getDoctrine()->getManager();
        $em -> persist($product);
        
        try {$em -> flush();} /** @todo Another way of catching Error here??? */
        catch (\Exception $e) {
            $message = $e->getMessage();
            return new Response ("Developer info: $message<br><br> DB is not available", Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $view = $this->view('Add Success', 200);
        return $view;
    }

    public function product_edit (Request $request, $id)
    {
        /* required json data from Request body:
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

            if (isset($data['name'])) $name = trim($data['name']);
            $this->is_name_valid ($name);
           
            if (isset($data['amount'])) $amount = $data['amount'];
            $this->is_amount_valid ($amount);
        }
        catch (Exception $e) {
            return new Response ($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

    //updating product in DB:
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em -> createQueryBuilder()
                            -> update ('App\Entity\ProductInStore', 'p')

                            -> set('p.name', ':name')
                            -> setParameter ('name', $name)

                            -> set('p.amount', ':amount')
                            -> setParameter ('amount', $amount)

                            -> where ('p.id = :id')
                            -> setParameter ('id', $id)
                            -> getQuery();
                            
        try {$result = $queryBuilder -> execute();}     /** @todo Another way of catching Error here??? */          
        catch (\Exception $e) {
            $message = $e->getMessage();
            return new Response ("Developer info: $message<br><br> DB is not available", Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if ($result) $view = $this->view('Update Success', 200);
        else $view = $this->view('Item NOT FOUND', 404);        
        return $view;
    }
    
    private function valid_json($string)  { 
        json_decode($string);
        if (json_last_error() !== 0) throw new Exception('Invalid json');
        return true;
    }
    
    private function if_string_is_natural_number($string) {
        if (!is_numeric($string) or ($string - floor($string) != 0) or $string <= 0 ) throw new Exception ("Invalid value for PAGE");
        return true;
    }

    private function is_elements_valid ($elements) {
        try {
            $this->if_string_is_natural_number($elements);
        }
        catch (Exception) {
            throw new Exception ("Invalid value for ELEMENTS");
        }
        if ($elements > 1000 ) throw new Exception ("Invalid value for ELEMENTS");
        return true;
    }

    private function is_name_valid ($name) {
        if ($name === null) throw new Exception('Invalid key for NAME');
        if (!is_string($name) or strlen($name) > 50 or strlen($name) < 2 or (trim($name) == "") ) throw new Exception('Invalid value for NAME');
        return true;
    }

    private function is_amount_valid ($amount) {
    if ( !is_int($amount) or $amount < 0) throw new Exception ("Invalid value of AMOUNT");
    return true;
    }  
}
