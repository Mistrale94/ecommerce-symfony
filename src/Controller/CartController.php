<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartContent;
use App\Entity\Product;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    private $entityManager;
    private $cartRepository;

    public function __construct(EntityManagerInterface $entityManager, CartRepository $cartRepository)
    {
        $this->entityManager = $entityManager;
        $this->cartRepository = $cartRepository;
    }

    #[Route('/add/{productId}', name: 'cart_add')]
    public function addToCart(int $productId): Response
    {
        $user = $this->getUser();

        $product = $this->entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable');
        }

        $cart = $this->cartRepository->findOneBy(['user' => $user, 'state' => 0]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setDate(new \DateTime());
            $cart->setState(0);

            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        $cartContent = $this->entityManager->getRepository(CartContent::class)->findOneBy(['cart' => $cart, 'product' => $product]);

        if ($cartContent) {
            $cartContent->setQuantity($cartContent->getQuantity() + 1);
        } else {
            $cartContent = new CartContent();
            $cartContent->setCart($cart);
            $cartContent->setProduct($product);
            $cartContent->setQuantity(1);

            $this->entityManager->persist($cartContent);
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/remove/{cartContentId}', name: 'cart_remove')]
    public function removeFromCart(int $cartContentId): Response
    {
        $cartContent = $this->entityManager->getRepository(CartContent::class)->find($cartContentId);

        if (!$cartContent) {
            throw $this->createNotFoundException('Element du panier introuvable');
        }

        $this->entityManager->remove($cartContent);
        $this->entityManager->flush();

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/', name: 'cart_show')]
    public function showCart(): Response
    {
        $user = $this->getUser();

        $cart = $this->cartRepository->findOneBy(['user' => $user, 'state' => 0]);

        $cartContents = [];

        if ($cart) {
            $cartContents = $this->entityManager->getRepository(CartContent::class)->findBy(['cart' => $cart]);
        }

        return $this->render('cart/show.html.twig', [
            'cart' => $cartContents,
        ]);
    }
}