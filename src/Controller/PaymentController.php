<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    /**
     * @Route("/api/create-payment-intent", name="create_payment_intent", methods={"POST"})
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            // Obtén la clave secreta de Stripe desde los parámetros
            $stripeSecretKey = $this->getParameter('stripe_secret_key');
            Stripe::setApiKey($stripeSecretKey);

            $data = json_decode($request->getContent(), true);
            if (!isset($data['amount'])) {
                return new JsonResponse(['error' => 'Missing amount'], 400);
            }

            $amount = $data['amount'];

            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'eur',
            ]);

            return new JsonResponse(['clientSecret' => $paymentIntent->client_secret]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}

