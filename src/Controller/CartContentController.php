<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartContentController extends AbstractController
{
    #[Route('/cart/content', name: 'app_cart_content')]
    public function index(): Response
    {
        return $this->render('cart_content/index.html.twig', [
            'controller_name' => 'CartContentController',
        ]);
    }
}
