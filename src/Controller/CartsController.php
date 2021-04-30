<?php

namespace App\Controller;

use App\Form\ChooseCartType;
use App\Form\ShopCartType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Carts;
// use App\Entity\Items;
// use App\Entity\Users;

/**
 * @package App\Controller
 *
 * @Route("/carts")
 **/
class CartsController extends AbstractController
{

    /**
     * @Route("/", name="carts_index")
     */
    public function indexAction(): Response
    {
        return $this->render('carts/carts_index.html.twig', [
            'controller_name' => 'CartsController',
        ]);
    }

    /**
     * @Route("/list", name="carts_list")
     */
    public function listAction(): Response
    {
        // TODO: restrict access to admins
        /*throw $this->createNotFoundException('Permission denied: You have to be logged.');*/

        $em = $this->getDoctrine()->getManager();
        $cartsRepository = $em->getRepository('App\Entity\Carts');
        $carts = $cartsRepository->findAll();

        return $this->render('carts/carts_list.html.twig', [
            'controller_name' => 'CartsController',
            'carts' => $carts
        ]);

        /* old version:
        $usersRepository = $em->getRepository('App\Entity\Users');
        $users = $usersRepository->findAll();

        $itemsRepository = $em->getRepository('App\Entity\Items');
        $items = $itemsRepository->findAll();

        return $this->render('carts/carts_list.html.twig', [
            'controller_name' => 'CartsController',
            'carts' => $carts, 'users' => $users, 'items' => $items
        ]); */
    }

    /**
     * @Route("/shop", name="carts_shop_form")
     */
    public function shopWithFormAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $usersRepository = $em->getRepository('App\Entity\Users');
        $users = $usersRepository->findAll();

        $itemsRepository = $em->getRepository('App\Entity\Items');
        $items = $itemsRepository->findAll();

        $form = $this->createForm(ShopCartType::class, array("users" => $users, "items" => $items));
        $form->add('send', SubmitType::class, ['label' => 'Shop']);
        // We create the form, add a submit button.
        // dump($request);
        $data = $form->handleRequest($request)->getData();
        // $form->handleRequest($request);
        // dump($data);

        // if we already have a posted form, we treat this one.
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $item = $data['item'];
                $user = $data['user'];
                $quantity = $data["quantity"];
                if ($item->getStock() < $quantity) {throw $this->createNotFoundException('Stock is insufficient.');}
                // else :
                $em = $this->getDoctrine()->getManager();
                $cartsRepository = $em->getRepository('App\Entity\Carts');
                $matchedCarts = $cartsRepository->findBy(array("user" => $user, "item" => $item));
                // dump($matchedCarts);

                if($matchedCarts == null){
                    // l'user n'a pas déjà l'item dans son panier
                    $cart = new Carts();
                    $cart->setUser($user)
                        ->setItem($item)
                        ->setQuantity($quantity);
                } else{
                    // l'user a déjà l'item dans son panier, alors il n'y a qu'un seul panier qui le contient à priori
                    if (count($matchedCarts) != 1) {throw $this->createNotFoundException('There is a problem with carts.');}
                    else{
                        $cart = $matchedCarts[0];
                        $newQuantity = $cart->getQuantity() + $quantity;
                        $cart->setQuantity($newQuantity);
                    }
                }
                // on update le stock de l'item (on a vérifié qu'il y en avait suffisament avant
                $newStock = $item->getStock() - $quantity;
                $item->setStock($newStock);

                $em->persist($item);
                $em->persist($cart);
                $em->flush();

                $description = $item->getDescription();
                $login =  $user->getLogin();
                $this->addFlash('info', "$description (x$quantity) has been added to cart of $login");
                return $this->redirectToRoute("carts_index");
            }
            else {
                $this->addFlash('info', 'Cart hasn\'t been added nor modified');
                throw $this->createNotFoundException('Invalid form.');
            }
        } else {
            $myform = $form->createView();
            return $this->render('carts/carts_form.html.twig', [
                'controller_name' => 'cartsController',
                'myform' => $myform
            ]);
        }
    }

    /**
     * @Route("/choose", name="carts_choose")
     */
    public function chooseAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $cartsRepository = $em->getRepository('App\Entity\Carts');
        $carts = $cartsRepository->findAll();
        if ($carts == null) {throw $this->createNotFoundException("please add some carts in order to choose");}
        // else:
        $form = $this->createForm(ChooseCartType::class, $carts);
        $form->add('send', SubmitType::class, ['label' => 'Choose']);
        // We create the form, add a submit button.

        $data = $form->handleRequest($request)->getData();
        dump($data);

        // if we already have a posted form, we treat this one.
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $cartID = $data["cart"]->getID();
                $actionChosen = $data["action"];
                $this->addFlash('info', "Cart $cartID has been chosen");

                switch ($actionChosen){
                    case "edit": return $this->redirectToRoute("carts_edit", ["id" => $cartID]);
                    case "remove": return $this->redirectToRoute("carts_remove", ["id" => $cartID]);
                    default: throw $this->createNotFoundException("$actionChosen/$cartID");
                }
            }
            else {
                $this->addFlash('info', 'Cart hasn\'t been chosen');
                throw $this->createNotFoundException('Invalid form.');
            }
        }
        $myform = $form->createView();
        return $this->render('carts/carts_form.html.twig', [
            'controller_name' => 'cartsController',
            'myform' => $myform
        ]);
    }

    /**
     * @Route("/remove/{id?}", name="carts_remove", requirements = {"id" = "[1-9]\d*"})
     */
    public function removeAction($id): Response
    {
        // TODO: restrict access to admins
        /*throw $this->createNotFoundException('Permission denied: You have to be logged.');*/

        // Default is null for id
        if ($id == null) {throw $this->createNotFoundException('Please choose a cart id.');}
        else{
            $em = $this->getDoctrine()->getManager();
            $cartsRepository = $em->getRepository('App\Entity\Carts');
            $cartToRemove = $cartsRepository->find($id);

            if ($cartToRemove != null) {
                $item = $cartToRemove->getItem();
                // It should exist if we can't delete a item which is in a cart

                $description = $item->getDescription();
                $newStock = $item->getStock() + $cartToRemove->getQuantity();
                $item->setStock($newStock);
                $em->persist($item);

                $em->remove($cartToRemove);
                $em->flush();
                $this->addFlash('info', "Cart $id has been removed.");
                $this->addFlash('info', "$description : stock has raised.");
            }
            else {throw $this->createNotFoundException('This id is not the pk of a cart in this DB.');}
        }
        return $this->redirectToRoute("carts_index");
    }

    /**
     * @Route("/edit/{id?}", name="carts_edit", requirements = {"id" = "[1-9]\d*"})
     */
    public function editAction($id, Request $request): Response
    {
        if ($id == null) {throw $this->createNotFoundException('Please choose a cart id.');}
        else {
            throw $this->createNotFoundException('Please delete the cart and then create another one.');
            // there was a too long and complicated function here before
        }
    }
}
