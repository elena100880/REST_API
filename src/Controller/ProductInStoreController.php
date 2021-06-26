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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductInStoreController extends AbstractFOSRestController
{    
    public function products_get(Request $request)
    {
        /* required data from Request:
        * amount - 0 (not in store), 1 - all products in store (default), 5 - products >5 in store
        * page - from 1, default 1
        * elements - max 1000, default 2;
        *
        *  or just id in URL (for getting one product by id)
        */

        try {
            if ($id != 0) {
                $product = $this->getDoctrine()->getRepository(ProductInStore::class)->find($id);
                if (empty($product)) return $this->view (["code" => 404, "message" =>"Product NOT FOUND"], 404);

                return $this->view($product, 200);
            }
    //validating data from Request:
            $amount = $request->query->get('amount') ?? 1;
                
            $page = ($request->query->get('page')) ?? 1;
            $this->if_string_is_natural_number($page);
                
            $elements = $request->query->get('elements') ?? 2;
            $this->is_elements_valid($elements);
                  
    //quering products:    
            $dql = "SELECT p FROM App\Entity\ProductInStore p";
            if ($amount == 1) $dql = $dql;
            elseif ($amount == 0) $dql = $dql.' WHERE p.amount = 0';
            elseif ($amount == 5) $dql = $dql.' WHERE p.amount > 5';
            else return $this->view (["code" => 400, "message" =>"Invalid amount"], 400);
        
        
            $em = $this->getDoctrine()->getManager();
            $DQLquery = $em->createQuery($dql)
                                    ->setFirstResult($page * $elements  - $elements)
                                    ->setMaxResults($elements);
            $productsPage = new Paginator($DQLquery); //$productsPage = $DQLquery ->getResult();
            $totalElements = count($productsPage);
            $returnResponse = ["data" => $productsPage, "total" => $totalElements];

            return $this->view($returnResponse, 200);
        }
        catch (Exception $e) {
            return $this->view(["code" => 400, "message" => $e->getMessage()], 400);
        }
        catch (\Throwable $e) {
            $message = $e->getMessage(); //dev info
            return $this->view(["code" => 500, "message" => "Service is not available. Try again later."], 500);
        }
    }

    public function product_delete (ProductInStore $product) // = null) 
    { 
        try{
           // if (empty($product)) return $this->view (["code" => 404, "message" =>"Product NOT FOUND"], 400);

            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
            return $this->view('Delete Success', 200);
        }
        catch (\Throwable $e) {
            $message = $e->getMessage(); //dev info
            return $this->view(["code" => 500, "message" => "Service is not available. Try again later."], 500);
        }
    }
   
    public function product_add ()
    {
        /* required json data from Request body:
         * {     "name": "Product X", //
         *      "amount": 1,   // 0 for default
         * }
         */

        try {    
            $json = file_get_contents('php://input');
        
    //validating data from Request:
            $this->valid_json($json);
            $data = json_decode($json, true);

            $name = trim($data['name']) ?? null;
            $this->is_name_valid ($data['name']);
                
            $amount = $data['amount'] ?? 0; 
            $this->is_amount_valid ($amount);
                  
    //adding product to DB:
            $product = new ProductInStore;
            $product->setName($name);
            $product->setAmount($amount);
            $em = $this->getDoctrine()->getManager();
            $em -> persist($product);
            $em -> flush();

            return $this->view('Add Success', 200);
        }
        catch (Exception $e) {
            $g = $e->getCode();
            return $this->view(["code" => 400, "message" => $e->getMessage()], 400);
        }
        catch (\Throwable $e) {
            $message = $e->getMessage(); //dev info
            return $this->view(["code" => 500, "message" => "Service is not available. Try again later."], 500);
        }
    }

    public function product_edit (ProductInStore $product = null) 
    {
        /* required json data from Request body:
         * {    "name": "Product X",  
         *      "amount": 1,  
         * }
         */
        
        try { 
            if (empty($product)) return $this->view (["code" => 404, "message" =>"Product NOT FOUND"], 400);  
            $json = file_get_contents('php://input');
        
    //validating data from Request:
            $this->valid_json($json);
            $data = json_decode($json, true);
            
            if (isset($data['name'])) {
                $name = trim($data['name']);
                $this->is_name_valid ($data['name']);
                $product->setName($name);
            }
            
            if (isset($data['amount'])) {
                $amount = $data['amount'];
                $this->is_amount_valid ($amount);
                $product->setAmount($amount);
            }
      
    //updating product in DB:
            $em = $this->getDoctrine()->getManager();
            $em -> persist($product);
            $em->flush();

            return $this->view('Update Success', 200);
        }
        catch (Exception $e) {
            $g = $e->getCode();
            return $this->view(["code" => 400, "message" => $e->getMessage()], 400);
        }
        catch (\Throwable $e) {
            $message = $e->getMessage(); //dev info
            return $this->view(["code" => 500, "message" => "Service is not available. Try again later."], 500);
        }
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
        if (!is_numeric($elements) or ($elements - floor($elements) != 0) or $elements <= 0 or $elements > 1000 ) throw new Exception ("Invalid value for ELEMENTS");
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
