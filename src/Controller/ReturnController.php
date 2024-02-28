<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class ReturnController extends AbstractController
{
    #[Route('/shop/return/{productId}', 'shop_return')]
    public function __invoke(Request $request, ProductRepository $productRepository, string $productId) : Response
    {
        $params = $request->query->all();
        $product = $productRepository->findOneById($productId);
        return $this->render(
            'shop/return.html.twig',
            [
                'product' => $product,
                'result' => $params['siru_event'],
            ]
        );
    }
}
