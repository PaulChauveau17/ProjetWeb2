<?php

namespace App\Controller;

use App\Form\AddUserType;
use App\Form\ChooseUserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Users;

/**
 * @package App\Controller
 *
 * @Route("/users")
 **/
class UsersController extends AbstractController
{

    /**
     * @Route("/", name="users_index")
     */
    public function indexAction(): Response
    {
        return $this->render('users/users_index.html.twig', [
            'controller_name' => 'UsersController',
        ]);
    }

    /**
     * @Route("/list", name="users_list")
     */
    public function listAction(): Response
    {
        // TODO: restrict access to this action to admins
        // Entity Manager : em
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository('App\Entity\Users');
        $users = $userRepository->findAll();

        //dump($users);

        return $this->render('users/users_list.html.twig', [
            'controller_name' => 'UsersController',
            'users' => $users,
        ]);
    }

    /**
     * @Route("/add/toto", name="users_add_toto")
     */
    public function addTotoAction(): Response
    {

        $user = new Users(); // l'utilisateur est encore indépendant de Doctrine
        $user->setLogin('toto')
            ->setEncPwd("otot")
            ->setName("Twopak")
            ->setSurname("Tom")
            ->setBirthDate(null)
            ->setIsAdmin(false);// valeur par défaut mais on force
        //dump($user);
        $em = $this->getDoctrine()->getManager();

        $em->persist($user); // Doctrine devient responsable de l'user
        $em->flush(); // injection physique dans la BD

        //dump($user);

        // on redirige vers une autre action
        return $this->redirectToRoute('users_list');
    }

    /**
     * @Route("/add/admin", name="users_add_admin")
     */
    public function addAdminAction(): Response
    {

        $admin = new Users(); // l'utilisateur est encore indépendant de Doctrine
        $admin->setLogin('admin')
            ->setEncPwd("nimda")
            ->setName("Pujadas")
            ->setSurname("David")
            ->setBirthDate(null)
            ->setIsAdmin(true);
        //dump($admin);

        $em = $this->getDoctrine()->getManager();

        $em->persist($admin); // Doctrine devient responsable de l'user

        $em->flush(); // injection physique dans la BD

        // on redirige vers une autre action
        return $this->redirectToRoute('users_list');
    }

    /**
     * @Route("/add/form", name="users_add_form")
     */
    public function addWithFormAction(Request $request): Response
    {
        $form = $this->createForm(AddUserType::class);
        $form->add('send', SubmitType::class, ['label' => 'Add']);
        // We create the form, add a submit button.
        // dump($request);
        $userToAdd = $form->handleRequest($request)->getData();
        // dump($form);
        dump($userToAdd);

        // if we already have a posted form, we treat this one.
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($userToAdd);
                $em->flush();
                $this->addFlash('info', 'An user as been added');
            }
            else {$this->addFlash('info', 'User hasn\'t been added');}
            return $this->redirectToRoute("users_index");
        }
        else {
            $myform = $form->createView();
            return $this->render('users/users_form.html.twig', [
                'controller_name' => 'usersController',
                'myform' => $myform
            ]);
        }
    }

    /**
     * @Route("/choose/form", name="users_choose_form")
     */
    public function chooseWithFormAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $userRepository = $em->getRepository('App\Entity\Users');
        $users = $userRepository->findAll();
        // si il y a au moins un user
        // dump($users);
        $form = $this->createForm(ChooseUserType::class, $users);
        $form->add('send', SubmitType::class, ['label' => 'Choose']);
        // We create the form, add a submit button.

        $data = $form->handleRequest($request)->getData();

        // if we already have a posted form, we treat this one.
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $userSelected = $data["user"];
                $actionChosen = $data["action"];
                $this->addFlash('info', 'An user has been chosen');
                dump($actionChosen);
                dump($userSelected);
                /*TODO: faire en sorte de faire la strat de gilles pour attendre avant d'être redirigé et voir les dump.*/
                switch ($actionChosen){

                    case "edit": return $this->redirectToRoute("users_edit_form");

                    case "remove": return $this->redirectToRoute("users_remove");

                    default: throw $this->createNotFoundException('Invalid action.');
                }
            }
            else {
                $this->addFlash('info', 'User hasn\'t been chosen');
                throw $this->createNotFoundException('Invalid form.');
            }
        }
        $myform = $form->createView();
        return $this->render('users/users_form.html.twig', [
            'controller_name' => 'usersController',
            'myform' => $myform
        ]);
    }

    /**
     * @Route(name="users_remove")
     */
    public function removeAction(Request $request, Users $user): Response
    {
        $this->addFlash('info', 'In removeAction');
        return $this->redirectToRoute("users_index");
    }

    /**
     * @Route(name="users_edit_form")
     */
    public function editWithFormAction(Request $request, Users $user): Response
    {
        /*$em = $this->getDoctrine()->getManager();*/

        /*throw $this->createNotFoundException('Permission denied: You have to be logged.');*/



        /*
        // if we already have a posted form, we treat this one.
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $userToEdit =
                $form = $this->createForm(AddUserType::class, $userToAdd);
                $form->add('send', SubmitType::class, ['label' => 'Add']);
                // We create the form, add a submit button.
                // dump($request);
                $form->handleRequest($request);
                //dump($form);

                $em->persist($userToAdd);
                $em->flush();
                $this->addFlash('info', 'An user as been added');
            }
            else {$this->addFlash('info', 'User hasn\'t been added');}
            return $this->redirectToRoute("users_index");
        }
        else {
            $myform = $form->createView();
            return $this->render('users/users_add.html.twig', [
                'controller_name' => 'usersController',
                'myform' => $myform
            ]);
        }*/

        $this->addFlash('info', 'In editWithFormAction');
        return $this->redirectToRoute("users_index");
    }

}
