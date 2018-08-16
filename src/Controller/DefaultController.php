<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Psr\Log\LoggerInterface;
use Siru\API;
use Siru\Signature;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class DefaultController extends Controller
{

    /**
     * @var API
     */
    private $api;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(API $api, ProductRepository $repository, LoggerInterface $log)
    {
        $this->api = $api;
        $this->productRepository = $repository;
        $this->logger = $log;
    }

    /**
     * @return Response
     *
     * @Route("/shop/{_locale}", defaults={"_locale": "en"}, methods={"GET"}, name="demoshop_index")
     */
    public function index() : Response
    {
        $products = $this->productRepository->findAll();

        return $this->render(
            'shop/index.html.twig',
            [
                'products' => $products
            ]
        );
    }

    /**
     * @param int $productId
     * @return Response
     * @throws \Exception
     *
     * @Route("/shop/{_locale}/checkout/{productId}", defaults={"_locale": "en"}, methods={"GET"}, name="demoshop_purchase")
     */
    public function checkout(int $productId) : Response
    {
        $product = $this->productRepository->findById($productId);
        if($product === null) {
            throw $this->createNotFoundException();
        }

        return $this->render(
            'shop/checkout.html.twig',
            [
                'product'  => $product,
                'productId' => $productId
            ]
        );
    }

    /**
     * @param Request             $request
     * @param int                 $productId
     * @param TranslatorInterface $translator
     * @return RedirectResponse
     *
     * @Route("/shop/{_locale}/checkout/{productId}/confirm", defaults={"_locale": "en"}, methods={"POST"}, name="demoshop_post")
     */
    public function post(Request $request, int $productId, TranslatorInterface $translator) : RedirectResponse
    {
        $product = $this->productRepository->findById($productId);
        if($product === null) {
            throw $this->createNotFoundException();
        }

        $returnUrl = $this->generateUrl('demoshop_return', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $notifyUrl = $this->generateUrl('demoshop_notify', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $purchaseReference = 'demoshop-' . date('Ymdhis');

        try {
            $transaction = $this->api->getPaymentApi()
                ->set('variant', $request->request->get('variant'))
                ->set('basePrice', '5.00')
                ->set('redirectAfterSuccess', $returnUrl)
                ->set('redirectAfterFailure', $returnUrl)
                ->set('redirectAfterCancel', $returnUrl)
                ->set('notifyAfterSuccess', $notifyUrl)
                ->set('notifyAfterFailure', $notifyUrl)
                ->set('notifyAfterCancel', $notifyUrl)
                ->set('customerNumber', $request->request->get('customerNumber'))
                ->set('title', $translator->trans($product['title']))
                ->set('basePrice', $product['price'])
                ->set('purchaseCountry', $product['country'])
                ->set('purchaseReference', $purchaseReference)
                ->set('serviceGroup', 2)
                ->set('taxClass', 2)
                ->set('customerLocale', $request->getLocale())
                ->createPayment();

        } catch(\Siru\Exception\InvalidResponseException $e) {
            $this->addFlash('danger', 'Unable to contact Siru Payment API.');
            return $this->redirectToRoute('demoshop_purchase', ['productId' => $productId]);

        } catch(\Siru\Exception\ApiException $e) {
            $errors = implode(', ', $e->getErrorStack());
            $this->addFlash('danger', 'API reported following errors: ' . $errors);
            return $this->redirectToRoute('demoshop_purchase', ['productId' => $productId]);
        }

        $this->logger->info('Created new transaction at Siru Payment Gateway. Redirecting user to payment screen.',
            ['purchaseReference' => $purchaseReference, 'uuid' => $transaction['uuid']]
        );

        return $this->redirect($transaction['redirect']);
    }

    /**
     * @param  Request $request
     * @param  Signature $signature
     * @return Response
     * @todo   Log notification
     *
     * @Route("/shop/{_locale}/notify", defaults={"_locale": "en"}, methods={"POST"}, name="demoshop_notify")
     */
    public function notify(Request $request, Signature $signature)
    {
        $params = json_decode($request->getContent(), true);

        if($signature->isNotificationAuthentic($params) === false) {
            $this->logger->warning('Received a non-authentic notification to callback url.', $params);
            throw new HttpException(500, 'Game over man game over');
        }

        $this->logger->info('Received notification to callback url regarding transaction.',
            ['purchaseReference' => $params['siru_purchaseReference'], 'uuid' => $params['siru_uuid']]
        );

        return new Response('Receipt: ' . $params['siru_event']);
    }

    /**
     * @param Request $request
     * @param Signature $signature
     * @return RedirectResponse
     *
     * @Route("/shop/{_locale}/return", defaults={"_locale": "en"}, methods={"GET"}, name="demoshop_return")
     */
    public function verify(Request $request, Signature $signature)
    {
        $params = $request->query->all();

        if($signature->isNotificationAuthentic($params) === false) {
            throw new HttpException(500, 'Game over man game over');
        }


        switch ($params['siru_event']) {

            case 'cancel':
                return $this->redirectToRoute('demoshop_result_canceled');

            case 'success':
                return $this->redirectToRoute('demoshop_result_success');

            case 'failure':
                return $this->redirectToRoute('demoshop_result_failed');

            default:
                throw new HttpException(500, 'Game over man game over');
        }

    }

    /**
     * @return Response
     *
     * @Route("/shop/{_locale}/failed", defaults={"_locale": "en"}, methods={"GET"}, name="demoshop_result_failed")
     */
    public function failed() : Response
    {
        return $this->render('shop/fail.html.twig');
    }

    /**
     * @return Response
     *
     * @Route("/shop/{_locale}/success", defaults={"_locale": "en"}, methods={"GET"}, name="demoshop_result_success")
     */
    public function success() : Response
    {
        return $this->render('shop/ok.html.twig');
    }

    /**
     * @return Response
     *
     * @Route("/shop/{_locale}/canceled", defaults={"_locale": "en"}, methods={"GET"}, name="demoshop_result_canceled")
     */
    public function canceled() : Response
    {
        return $this->render('shop/cancel.html.twig');
    }

}
