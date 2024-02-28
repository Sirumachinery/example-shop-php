<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class ProductsController extends AbstractController
{
    #[Route('/shop', 'shop_products')]
    public function __invoke(ProductRepository $productRepository) : Response
    {
        return $this->render(
            'shop/products.html.twig',
            [
                'products' => $productRepository->findAll(),
            ]
        );
    }
}
