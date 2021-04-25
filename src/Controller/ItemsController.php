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
        /* increase stock if already created */
        $item = new Items(); // l'item est encore indépendant de Doctrine
        $item->setDescription('Coronavirus disease 2019 (COVID-19)')
            ->setPrice(1.99)
            ->setStock(9000000000);
        $em = $this->getDoctrine()->getManager();

        $em->persist($item); // Doctrine devient responsable de l'item
        $em->flush(); // injection physique dans la BD
        $this->addFlash('info', "COVID has been added");

        // on redirige vers une autre action
        return $this->redirectToRoute('items_list');
    }

    /**
     * @Route("/add/project", name="items_add_project")
     */
    public function addProjectAction(): Response
    {
        /* increase stock if already created */
        $item = new Items(); // l'item est encore indépendant de Doctrine
        $item->setDescription('A website which manage users, items and carts')
            ->setPrice(4.99)
            ->setStock(1);
        $em = $this->getDoctrine()->getManager();

        $em->persist($item); // Doctrine devient responsable de l'item
        $em->flush(); // injection physique dans la BD
        $this->addFlash('info', "This project has been added");

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
        $itemToAdd = $form->handleRequest($request)->getData();
        // dump($form);
        dump($itemToAdd);

        // if we already have a posted form, we treat this one.
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $itemDescription = $itemToAdd->getDescription();
                $em->persist($itemToAdd);
                $em->flush();
                $this->addFlash('info', "$itemDescription as been added");
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
                    case "show": return $this->render('items/items_list.html.twig', [
                        'controller_name' => 'ItemsController',
                        'items' => array($data["item"])
                    ]); /* pas super propre.. */
                    default: throw $this->createNotFoundException("$actionChosen/$itemDescription");
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
            $itemToRemove =  $itemsRepository->find($id);
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
            $itemToEdit =  $itemsRepository->find($id);

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
