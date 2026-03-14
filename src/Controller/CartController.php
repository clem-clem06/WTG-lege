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

        // On récupère les valeurs du formulaire
        $quantiteChoisie = (int) $request->request->get('quantite', 1);

        // Si tu as remplacé les boutons radio par un champ "nombre de mois", on le récupère ici :
        $dureeChoisie = (int) $request->request->get('duree', 1);

        // ==========================================
        // POINT 1 : SÉCURITÉ
        // ==========================================


        $existingItem = null;
        foreach ($cart->getCartItems() as $item) {
            // LE SECRET : On vérifie l'offre ET la durée pour ne pas les mélanger !
            if ($item->getOffre() === $offre && $item->getDureeMois() === $dureeChoisie) {
                $existingItem = $item;
                break;
            }
        }

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + $quantiteChoisie);
        } else {
            // Si le client prend plusieurs années (ex: 12, 24, 36), on peut lui appliquer la promo "2 mois offerts par an"
            if ($dureeChoisie >= 12 && $dureeChoisie % 12 === 0) {
                $annees = $dureeChoisie / 12;
                $prixCalcule = $offre->getPrixMensuel() * 10 * $annees; // 10 mois payés au lieu de 12 par an
            } else {
                $prixCalcule = $offre->getPrixMensuel() * $dureeChoisie; // Prix classique
            }

            $cartItem = new CartItem();
            $cartItem->setOffre($offre);
            $cartItem->setQuantity($quantiteChoisie);
            $cartItem->setDureeMois($dureeChoisie);
            $cartItem->setPrice($prixCalcule);

            $cart->addCartItem($cartItem);
            $em->persist($cartItem);
        }

        $em->flush();

        $this->addFlash('success', 'Offre ajoutée à votre panier pour ' . $dureeChoisie . ' mois !');
        return $this->redirectToRoute('app_offer_show', ['id' => $offre->getId()]);
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
