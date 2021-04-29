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

        $em = $this->getDoctrine()->getManager();
        $cartsRepository = $em->getRepository('App\Entity\Carts');
        $carts = $cartsRepository->findAll();
        // dump($carts);

        $usersRepository = $em->getRepository('App\Entity\Users');
        $users = $usersRepository->findAll();

        $itemsRepository = $em->getRepository('App\Entity\Items');
        $items = $itemsRepository->findAll();

        return $this->render('carts/carts_list.html.twig', [
            'controller_name' => 'CartsController',
            'carts' => $carts, 'users' => $users, 'items' => $items
        ]);
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
        // dump($data);

        // if we already have a posted form, we treat this one.
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $user =  $data["user"];
                $item =  $data["item"];
                $quantity =  $data["quantity"];

                $query = $em->createQuery("SELECT c FROM App\Entity\Carts c WHERE c.user = :user AND c.item = :item");
                $query->setParameters(array('user' => $user, 'item' => $item));
                $matchedCarts = $query->getResult();
                // dump($matchedCarts);

                if($matchedCarts == null){
                    // l'user n'a pas déjà l'item dans son panier
                    $cart = new Carts();
                    // $cart->setUser($user->getID()); // ne fonctionne pas (prbl setteur dans Users)
                    $cart->setUser($user);
                    // $cart->setItem($item->getID()); // ne fonctionne pas (prbl setteur dans Items
                    $cart->setItem($item);
                    $cart->setQuantity($quantity);
                }
                else{
                    // l'user a déjà l'item dans son panier, alors il n'y a qu'un seul panier qui le contient à priori
                    if (count($matchedCarts) != 1) {throw $this->createNotFoundException('There is a problem with carts.');}
                    else{
                        $cart = $matchedCarts[0];
                        $newQuantity = $cart->getQuantity() + $quantity;
                        $cart->setQuantity($newQuantity);
                    }
                }
                $newStock = $item->getStock() - $quantity;
                $item->setStock($newStock);

                $em->persist($item);
                $em->persist($cart);

                // without toString() function in Users and Items
                // $em->flush(); // Object of class App\Entity\Users could not be converted to string

                // with toString() function in Users and Items
                $em->flush(); // no problem if the database is not in readonly :)

                $itemDescription = $item->getDescription();
                $userLogin =  $user->getLogin();

                $this->addFlash('info', "$itemDescription (x$quantity) has been added to cart of $userLogin");
            }
            else {$this->addFlash('info', 'Cart hasn\'t been added nor modified');}
            return $this->redirectToRoute("carts_shop_form");
        }
        else {
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

        $usersRepository = $em->getRepository('App\Entity\Users');
        $users = $usersRepository->findAll();

        $itemsRepository = $em->getRepository('App\Entity\Items');
        $items = $itemsRepository->findAll();

        $given = array('carts' => $carts, 'users' => $users, 'items' => $items);

        $form = $this->createForm(ChooseCartType::class, $given);
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
        /*throw $this->createNotFoundException('Permission denied: You have to be logged.');*/

        // Default is null for id
        if ($id == null) {throw $this->createNotFoundException('Please choose a cart id.');}
        else{
            $em = $this->getDoctrine()->getManager();
            $cartsRepository = $em->getRepository('App\Entity\Carts');
            $cartToRemove = $cartsRepository->find($id);
            if ($cartToRemove != null) {
                $itemID = $cartToRemove->getItemID();
                $itemsRepository = $em->getRepository('App\Entity\Items');
                $item = $itemsRepository->find($itemID); // i don't check if this is not null

                $newStock = $item->getStock() + $cartToRemove->getQuantity();
                $item->setStock($newStock);
                $em->persist($item);

                $em->remove($cartToRemove);
                $em->flush();
                $this->addFlash('info', "Cart $id has been removed.");
                $this->addFlash('info', "Item $itemID : stock has raised.");
            }
            else {throw $this->createNotFoundException('This id is not in the base.');}
        }
        return $this->redirectToRoute("carts_index");
    }

    /**
     * @Route("/edit/{id?}", name="carts_edit", requirements = {"id" = "[1-9]\d*"})
     */
    public function editAction($id, Request $request): Response
    {
        //throw $this->createNotFoundException('Permission denied: You have to be logged.');

        // le top serait d'appeler la fonction d'ajout puis de suppression

        /* Pas opti et pas fini
        // Default is null for id
        if ($id == null) {throw $this->createNotFoundException('Please choose a cart id.');}
        else{
            $em = $this->getDoctrine()->getManager();

            $cartsRepository = $em->getRepository('App\Entity\Carts');
            $cartToEdit = $cartsRepository->find($id);

            if ($cartToEdit == null) {throw $this->createNotFoundException('This id is not in the base.');}

            $usersRepository = $em->getRepository('App\Entity\Users');
            $users = $usersRepository->findAll();

            $itemsRepository = $em->getRepository('App\Entity\Items');
            $items = $itemsRepository->findAll();

            // vu qu'on a pas passé le panier lors de la création du form on a pas les anciennes valeurs -> dommage
            $form = $this->createForm(ShopCartType::class, array("users" => $users, "items" => $items));
            $form->add('send', SubmitType::class, ['label' => 'Edit']);
            // We create the form, add a submit button.
            // dump($request);
            $data = $form->handleRequest($request)->getData();
            dump($cartToEdit);
            // if we already have a posted form, we treat this one.
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    // edit the cart
                    $query = $em->createQuery("SELECT c FROM App\Entity\Carts c WHERE c.user = :user AND c.item = :item");
                    $query->setParameters(array('user' => $data['user'], 'item' => $data['item']));
                    $matchedCarts = $query->getResult();
                    // dump($matchedCarts);

                    // pas ouf de le mettre avant en cas d'erreur mais on persist après


                    if($matchedCarts == null){
                        // l'user n'a pas déjà l'item dans son panier
                        $cartToEdit->setUser($data['user']);
                        $cartToEdit->setItem($data['item']);
                        $cartToEdit->setQuantity($data['quantity']); // set to 1 whatever
                        $em->persist($cartToEdit);
                    }
                    else {
                        // l'user a déjà l'item dans son panier, alors il n'y a qu'un seul panier qui le contient à priori
                        if (count($matchedCarts) != 1) {
                            throw $this->createNotFoundException('There is a problem with carts.');
                        } else {
                            $cart = $matchedCarts[0];
                            $newQuantity = $cart->getQuantity() + $cartToEdit->getQuantity();
                            $cart->setQuantity($newQuantity);
                            $em->persist($cart);
                        }
                    }

                    $em->flush();
                    $this->addFlash('info', "Cart $id has been edited");
                }
                else {$this->addFlash('info', 'Cart hasn\'t been edited');}
                return $this->redirectToRoute("carts_list");
            }
            else {
                $myform = $form->createView();
                return $this->render('carts/carts_form.html.twig', [
                    'controller_name' => 'cartsController',
                    'myform' => $myform
                ]);
            }
        }*/
    }
}
