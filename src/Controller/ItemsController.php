<?php

namespace App\Controller;

use App\Form\ChooseItemType;
use App\Form\AddItemType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Items;

/**
 * @package App\Controller
 *
 * @Route("/items")
 **/
class ItemsController extends AbstractController
{

    /**
     * @Route("/", name="items_index")
     */
    public function indexAction(): Response
    {
        return $this->render('items/items_index.html.twig', [
            'controller_name' => 'ItemsController',
        ]);
    }

    /**
     * @Route("/list", name="items_list")
     */
    public function listAction(): Response
    {

        $em = $this->getDoctrine()->getManager();
        $itemsRepository = $em->getRepository('App\Entity\Items');
        $items = $itemsRepository->findAll();

        //dump($items);

        return $this->render('items/items_list.html.twig', [
            'controller_name' => 'ItemsController',
            'items' => $items,
        ]);
    }



    /**
     * @Route("/add/COVID", name="items_add_COVID")
     */
    public function addCOVIDAction(): Response
    {
        $description = 'coronavirus disease 2019 (COVID-19)';
        $price = 1.99;
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT i FROM App\Entity\Items i WHERE i.description = :description and i.price = :price");
        $query->setParameters(array('description' => $description, 'price' => $price));
        $matchedItems = $query->getResult();

        if($matchedItems == null){
            // l'item n'existe pas au même prix
            $item = new Items(); // l'item est encore indépendant de Doctrine
            $item->setDescription($description)
                ->setPrice($price)
                ->setStock(9000000000);
            $this->addFlash('info', "COVID-19 has been added");
        }
        else {
            // l'item existe déjà au même prix
            if (count($matchedItems) != 1) {
                throw $this->createNotFoundException('There is a problem with items.');
            } else {
                $item = $matchedItems[0];
                $newStock = $item->getStock() + 1;
                $item->setStock($newStock);
                $this->addFlash('info', "COVID-19 stock has increased");
            }
        }

        $em->persist($item); // Doctrine devient responsable de l'item
        $em->flush(); // injection physique dans la BD

        // on redirige vers une autre action
        return $this->redirectToRoute('items_list');
    }

    /**
     * @Route("/add/form", name="items_add_form")
     */
    public function addWithFormAction(Request $request): Response
    {
        $form = $this->createForm(AddItemType::class);
        $form->add('send', SubmitType::class, ['label' => 'Add']);
        // We create the form, add a submit button.
        // dump($request);

        // if we already have a posted form, we treat this one.
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $item = $form->handleRequest($request)->getData();
                // dump($itemToAdd);

                $em = $this->getDoctrine()->getManager();
                $itemDescription = $item->getDescription();
                $itemPrice = $item->getPrice();

                $query = $em->createQuery("SELECT i FROM App\Entity\Items i WHERE i.description = :description and i.price = :price");
                $query->setParameters(array('description' => $itemDescription, 'price' => $itemPrice));
                $matchedItems = $query->getResult();

                if ($matchedItems != null) {
                    // l'item n'existe pas au même prix
                    $em->persist($item);
                    $this->addFlash('info', "$itemDescription ($$itemPrice) has been added");
                } else {
                    // l'item existe déjà au même prix
                    if (count($matchedItems) != 1) {
                        throw $this->createNotFoundException('There is a problem with items.');
                    } else {
                        $oldItem = $matchedItems[0];
                        $newStock = $item->getStock() + $oldItem->getStock();
                        $oldItem->setStock($newStock);
                        $em->persist($oldItem);
                        $this->addFlash('info', "$itemDescription ($$itemPrice) was already in base, so stock has increased");
                    }
                }
                $em->flush();
            }

            else {$this->addFlash('info', 'Item hasn\'t been added');}
            return $this->redirectToRoute("items_index");
        }
        else {
            $myform = $form->createView();
            return $this->render('items/items_form.html.twig', [
                'controller_name' => 'itemsController',
                'myform' => $myform
            ]);
        }
    }

    /**
     * @Route("/choose", name="items_choose")
     */
    public function chooseAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $itemsRepository = $em->getRepository('App\Entity\Items');
        $items = $itemsRepository->findAll();
        // si il y a au moins un item
        $form = $this->createForm(ChooseItemType::class, $items);
        $form->add('send', SubmitType::class, ['label' => 'Choose']);
        // We create the form, add a submit button.

        $data = $form->handleRequest($request)->getData();
        // dump($data);

        // if we already have a posted form, we treat this one.
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $itemID = $data["item"]->getID();
                $itemDescription =  $data["item"]->getDescription();
                $actionChosen = $data["action"];
                $this->addFlash('info', "$itemDescription has been chosen");

                switch ($actionChosen){
                    case "edit": return $this->redirectToRoute("items_edit", ["id" => $itemID]);
                    case "remove": return $this->redirectToRoute("items_remove", ["id" => $itemID]);
                    default: throw $this->createNotFoundException("$actionChosen/$itemID");
                }
            }
            else {
                $this->addFlash('info', 'Item hasn\'t been chosen');
                throw $this->createNotFoundException('Invalid form.');
            }
        }
        $myform = $form->createView();
        return $this->render('items/items_form.html.twig', [
            'controller_name' => 'itemsController',
            'myform' => $myform
        ]);
    }

    /**
     * @Route("/remove/{id?}", name="items_remove", requirements = {"id" = "[1-9]\d*"})
     */
    public function removeAction($id): Response
    {
        /*throw $this->createNotFoundException('Permission denied: You have to be logged.');*/

        // Default is null for id
        if ($id == null) {throw $this->createNotFoundException('Please choose a item id.');}
        else{
            $em = $this->getDoctrine()->getManager();
            $itemsRepository = $em->getRepository('App\Entity\Items');
            $itemToRemove = $itemsRepository->find($id);
            /* si l'item a été trouvé */
            $em->remove($itemToRemove);
            $em->flush();
            /*throw $this->createNotFoundException("nothing to do with item $id");*/
            $this->addFlash('info', "Item $id has been removed.");
        }
        return $this->redirectToRoute("items_index");
    }

    /**
     * @Route("/edit/{id?}", name="items_edit", requirements = {"id" = "[1-9]\d*"})
     */
    public function editAction($id, Request $request): Response
    {
        /*throw $this->createNotFoundException('Permission denied: You have to be logged.');*/

        // Default is null for id
        if ($id == null) {throw $this->createNotFoundException('Please choose a item id.');}
        else{
            $em = $this->getDoctrine()->getManager();
            $itemsRepository = $em->getRepository('App\Entity\Items');
            $itemToEdit = $itemsRepository->find($id);

            $form = $this->createForm(AddItemType::class, $itemToEdit);
            $form->add('send', SubmitType::class, ['label' => 'Edit']);
            // We create the form, add a submit button.
            // dump($request);
            $form->handleRequest($request);
            //dump($form);
            // if we already have a posted form, we treat this one.
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    // edit the item
                    $em->persist($itemToEdit);
                    $em->flush();
                    $this->addFlash('info', "Item $id has been edited");
                    /* we need to check if we don't get a existing item (same description and price)*/
                }
                else {$this->addFlash('info', 'Item hasn\'t been edited');}
                return $this->redirectToRoute("items_list");
            }
            else {
                $myform = $form->createView();
                return $this->render('items/items_form.html.twig', [
                    'controller_name' => 'itemsController',
                    'myform' => $myform
                ]);
            }
        }
    }

}
