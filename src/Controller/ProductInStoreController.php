<?php

//declare(strict_types = 1);

namespace App\Controller;

use App\Entity\ProductInStore;
use Doctrine\ORM\Tools\Pagination\Paginator;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductInStoreController extends AbstractFOSRestController
{
    //messages for Response:
    private const _500_MESSAGE = 'Service is not available. Try again later.';
    private const PRODUCT_NOT_FOUND_404 = 'Product NOT FOUND';
    private const PRODUCT_FOUND_200 = 'Product is FOUND';
    private const PRODUCTS_FOUND_200 = 'Search success';

    //messages for Response when invalid parameters:
    private const INVALID_SELECT_400 = 'Invalid select option';
    private const INVALID_JSON_400 = 'Invalid json';
    private const INVALID_PAGE_400 = 'Invalid value for PAGE';
    private const INVALID_ELEMENTS_400 = 'Invalid value for ELEMENTS';
    private const UNDEFINED_NAME_400 = 'Parameter NAME should be defined';
    private const INVALID_NAME_400 = 'Invalid value for NAME';
    private const INVALID_AMOUNT_400 = 'Invalid value of AMOUNT';

    public function products_get(Request $request, ProductInStore $product = null): View
    {
        /* required data from Request:
        * select - 0 (not in store), 1 - all products in store (default), 5 - products >5 in store
        * page - from 1, default 1
        * elements - max 1000, default 20;
        *
        *  or just /id  (for getting one product by id, orher parameters are ignored)
        */
        try {
            $id = $request->attributes->get('id');
            if (0 != $id) {
                if (empty($product)) {
                    return $this->view(['code' => 404, 'message' => ProductInStoreController::PRODUCT_NOT_FOUND_404], 404);
                }

                return $this->view(['code' => 200, 'message' => ProductInStoreController::PRODUCT_FOUND_200, 'data' => $product], 200);
            }

            //validating data from Request:
            $page = ($request->query->get('page')) ?? '1';
            $this->is_page_valid($page);

            $elements = $request->query->get('elements') ?? '20';
            $this->is_elements_valid($elements);

            //quering products:
            $select = $request->query->get('select') ?? 1;
            $dql = "SELECT p FROM App\Entity\ProductInStore p";
            if (1 == $select) {
                $dql = $dql;
            } elseif (0 == $select) {
                $dql = $dql.' WHERE p.amount = 0';
            } elseif (5 == $select) {
                $dql = $dql.' WHERE p.amount > 5';
            } else {
                return $this->view(['code' => 400, 'message' => ProductInStoreController::INVALID_SELECT_400], 400);
            }

            $em = $this->getDoctrine()->getManager();
            $DQLquery = $em->createQuery($dql)
                                    ->setFirstResult($page * $elements - $elements)
                                    ->setMaxResults($elements);
            $productsPage = new Paginator($DQLquery); //$productsPage = $DQLquery ->getResult();
            $totalElements = count($productsPage);

            return $this->view(['code' => 200, 'message' => ProductInStoreController::PRODUCTS_FOUND_200, 'data' => $productsPage, 'total' => $totalElements], 200);
        } catch (\UnexpectedValueException $e) { //catching validation Exceptions
            return $this->view(['code' => 400, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->view(['code' => 500, 'message' => ProductInStoreController:: _500_MESSAGE, 'devInfo' => $e->getMessage()], 500); //devInfo - only for dev mode
        }
    }

    public function product_delete(ProductInStore $product = null): View
    {
        try {
            if (empty($product)) {
                return $this->view(['code' => 404, 'message' => 'Product NOT FOUND'], 404);
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();

            return $this->view(['code' => 200, 'message' => 'Delete Success'], 200);
        } catch (\Throwable $e) {
            return $this->view(['code' => 500, 'message' => ProductInStoreController:: _500_MESSAGE, 'devInfo' => $e->getMessage()], 500); //devInfo - only for dev mode
        }
    }

    public function product_add(): View
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

            $name = $data['name'] ?? null;
            $this->is_name_valid($name);

            $amount = $data['amount'] ?? 0;
            $this->is_amount_valid($amount);

            //adding product to DB:
            $product = new ProductInStore();
            $product->setName($name);
            $product->setAmount($amount);
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return $this->view(['code' => 200, 'message' => 'Add Success'], 200);
        } catch (\UnexpectedValueException $e) {  // catching validation Exceptions
            return $this->view(['code' => 400, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->view(['code' => 500, 'message' => ProductInStoreController:: _500_MESSAGE, 'devInfo' => $e->getMessage()], 500); //devInfo - only for dev mode
        }
    }

    public function product_edit(ProductInStore $product = null): View
    {
        /* required json data from Request body:
         * {    "name": "Product X",
         *      "amount": 1,
         * }
         */

        try {
            if (empty($product)) {
                return $this->view(['code' => 404, 'message' => 'Product NOT FOUND'], 404);
            }
            $json = file_get_contents('php://input');

            //validating data from Request:
            $this->valid_json($json);
            $data = json_decode($json, true);

            if (isset($data['name'])) {
                $name = $data['name'];
                $this->is_name_valid($name);
                $product->setName($name);
            }

            if (isset($data['amount'])) {
                $amount = $data['amount'];
                $this->is_amount_valid($amount);
                $product->setAmount($amount);
            }

            //updating product in DB:
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return $this->view(['code' => 200, 'message' => 'Update Success'], 200);
        } catch (\UnexpectedValueException $e) {
            $g = $e->getCode();

            return $this->view(['code' => 400, 'message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->view(['code' => 500, 'message' => ProductInStoreController::_500_MESSAGE, 'devInfo' => $e->getMessage()], 500); //devInfo - only for dev mode
        }
    }

    private function valid_json($string): void
    {
        json_decode($string);
        if (0 !== json_last_error()) {
            throw new \UnexpectedValueException(ProductInStoreController::INVALID_JSON_400);
        }
    }

    private function is_page_valid($page): void
    {
        if (!is_numeric($page) || ($page - floor($page) > 0)) {
            throw new \UnexpectedValueException(ProductInStoreController::INVALID_PAGE_400);
        }
    }

    private function is_elements_valid($elements): void
    {
        if (!is_numeric($elements) || (0 != $elements - floor($elements)) || $elements <= 0 || $elements > 1000) {
            throw new \UnexpectedValueException(ProductInStoreController::INVALID_ELEMENTS_400);
        }
    }

    private function is_name_valid($name): void
    {
        if (null === $name) {
            throw new \UnexpectedValueException(ProductInStoreController::UNDEFINED_NAME_400);
        }
        if (is_numeric($name) || strlen($name) > 50 or strlen($name) < 2 || ('' == trim($name))) {
            throw new \UnexpectedValueException(ProductInStoreController::INVALID_NAME_400);
        }
    }

    private function is_amount_valid($amount): void
    {
        if (!is_integer($amount) || $amount < 0) {
            throw new \UnexpectedValueException(ProductInStoreController::INVALID_AMOUNT_400);
        }
    }
}
