<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class CartController extends AbstractController
{
    #[Route('/shop/cart/{productId}', 'shop_cart')]
    public function __invoke(string $productId, ProductRepository $productRepository) : Response
    {
        $product = $productRepository->findOneById($productId);
        if (null === $product) {
            throw $this->createNotFoundException();
        }
        return $this->render(
            'shop/cart.html.twig',
            [
                'product' => $product,
            ]
        );
    }
}
