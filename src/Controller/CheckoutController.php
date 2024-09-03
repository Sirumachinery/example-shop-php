<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductRepository;
use Siru\API;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsController]
class CheckoutController extends AbstractController
{
    #[Route('/shop/checkout', 'shop_checkout', methods: ['POST'])]
    public function __invoke(API $siruApi, ProductRepository $productRepository, Request $request) : Response
    {
        $productId = (string) $request->request->get('productId');
        $product = $productRepository->findOneById($productId);
        if (null === $product) {
            throw $this->createNotFoundException();
        }

        $returnUrl = $this->generateUrl('shop_return', ['productId' => $product->id], UrlGeneratorInterface::ABSOLUTE_URL);
        try {
            $paymentApi = $siruApi->getPaymentApi();
            foreach ($product->apiVariables as $key => $value) {
                $paymentApi->set($key, $value);
            }
            $transaction = $paymentApi
                ->set('redirectAfterSuccess', $returnUrl)
                ->set('redirectAfterFailure', $returnUrl)
                ->set('redirectAfterCancel', $returnUrl)
#                ->set('notifyAfterSuccess', $notifyUrl)
#                ->set('notifyAfterFailure', $notifyUrl)
#                ->set('notifyAfterCancel', $notifyUrl)
                ->set('customerNumber', $request->request->get('customerNumber'))
                ->set('customerFirstName', 'John')
                ->set('customerLastName', 'Doe')
#                ->set('title', $translator->trans($product['title']))
#                ->set('purchaseReference', $purchaseReference)
#                ->set('serviceGroup', 2)
#                ->set('taxClass', 2)
                ->set('customerLocale', $request->getLocale())
                ->createPayment();

        } catch(\Siru\Exception\InvalidResponseException $e) {
            $this->addFlash('danger', 'Unable to contact Siru Payment API.');
            return $this->redirectToRoute('shop_cart', ['productId' => $product->id]);

        } catch(\Siru\Exception\ApiException $e) {
            $errors = implode(', ', $e->getErrorStack());
            $this->addFlash('danger', 'API reported following errors: ' . $errors);
            return $this->redirectToRoute('shop_cart', ['productId' => $product->id]);
        }
        return $this->redirect($transaction['redirect']);
    }
}
