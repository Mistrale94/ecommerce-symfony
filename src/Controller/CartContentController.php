<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartContent;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


class CartContentController extends AbstractController
{

    #[Route("/cart-content/add/{productId}", name:"cart_content_add", methods:["GET", "POST"])]
    public function addAction(int $productId, EntityManagerInterface $em): Response
    {

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        } else if ($product->getStock() <= 0) {
            throw $this->createNotFoundException('Produit en rupture de stock.');
        } else if ($product->getStock() >= 1) {
            $product->setStock($product->getStock() - 1);
        }

        $user = $this->getUser();

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user, 'state' => false]);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setState(false);
            $cart->setDate(new \DateTime());
            $em->persist($cart);
        }

        $cartContent = $em->getRepository(CartContent::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        if (!$cartContent) {
            $cartContent = new CartContent();
            $cartContent->setCart($cart);
            $cartContent->setProduct($product);
            $cartContent->setQuantity(1);
            $cartContent->setDate(new \DateTime());
            $em->persist($cartContent);
        } else {
            $cartContent->setQuantity($cartContent->getQuantity() + 1);
        }

        $em->flush();

        return $this->redirectToRoute('cart_show');
    }

    #[Route("/cart-content/remove/{cartContentId}", name:"cart_content_remove", methods:["GET", "POST"])]
    public function removeAction(int $cartContentId, EntityManagerInterface $em): Response
    {
        $cartContent = $em->getRepository(CartContent::class)->find($cartContentId);
        if (!$cartContent) {
            throw $this->createNotFoundException('Contenu du panier introuvable.');
        } else if ($cartContent->getQuantity() > 1) {
            $cartContent->getProduct()->setStock($cartContent->getProduct()->getStock() + 1);
            $cartContent->setQuantity($cartContent->getQuantity() - 1);
            $em->persist($cartContent);
            $em->flush();
        } else if ($cartContent->getQuantity() == 1) {
            $cartContent->getProduct()->setStock($cartContent->getProduct()->getStock() + 1);
            $em->remove($cartContent);
            $em->flush();
        }

        return $this->redirectToRoute('cart_show');
    }

    
}