<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Offre;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartRepository $cartRepository): Response
    {
        $user = $this->getUser();
        // On cherche le panier de l'utilisateur
        $cart = $cartRepository->findOneBy(['user' => $user]);

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Offre $offre, Request $request, CartRepository $cartRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $em->persist($cart);
        }

        // 1. Récupération de la durée depuis le formulaire (par défaut 1 mois si on n'a rien)
        $dureeChoisie = (int) $request->request->get('duree', 1);

        // 2. Calcul du prix total pour cette ligne
        if ($dureeChoisie === 12) {
            // Promo : 12 mois pour le prix de 10
            $prixCalcule = $offre->getPrixMensuel() * 10;
        } else {
            // Sinon (1 mois), c'est le prix normal x1
            $prixCalcule = $offre->getPrixMensuel() * 1;
        }

        // 3. Ajout au panier
        $cartItem = new CartItem();
        $cartItem->setOffre($offre);
        $cartItem->setQuantity(1);
        $cartItem->setDureeMois($dureeChoisie); // On sauvegarde la durée
        $cartItem->setPrice($prixCalcule); // Le prix total de la ligne

        $cart->addCartItem($cartItem);
        $em->persist($cartItem);
        $em->flush();

        $this->addFlash('success', 'L\'offre a bien été ajoutée pour ' . $dureeChoisie . ' mois !');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function remove(CartItem $cartItem, EntityManagerInterface $em): Response
    {
        // Sécurité : On vérifie que la ligne appartient bien au panier de l'utilisateur connecté !
        if ($cartItem->getCart()->getUser() === $this->getUser()) {
            $em->remove($cartItem);
            $em->flush();
            $this->addFlash('success', 'Offre retirée du panier.');
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/decrease/{id}', name: 'app_cart_decrease')]
    public function decrease(CartItem $cartItem, EntityManagerInterface $em): Response
    {
        // On vérifie que le CartItem appartient bien au panier de l'utilisateur
        if ($cartItem->getCart()->getUser() === $this->getUser()) {
            if ($cartItem->getQuantity() > 1) {
                // Si > 1, on retire 1
                $cartItem->setQuantity($cartItem->getQuantity() - 1);
            } else {
                // Si = 1, on supprime la ligne complètement
                $em->remove($cartItem);
                $this->addFlash('success', 'Offre retirée du panier.');
            }
            $em->flush();
        }

        return $this->redirectToRoute('app_cart');
    }
}
