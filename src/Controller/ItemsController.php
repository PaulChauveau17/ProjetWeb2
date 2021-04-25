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
        /*throw $this->createNotFoundException('Permission denied: You have to be logged.');*/

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
        /* raise an exception if toto is already created */
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
        /* raise an exception if admin is already created */
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
                $userLogin = $userToAdd->getLogin();
                $em->persist($userToAdd);
                $em->flush();
                $this->addFlash('info', "$userLogin as been added");
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
     * @Route("/choose", name="users_choose")
     */
    public function chooseAction(Request $request): Response
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
        // dump($data);

        // if we already have a posted form, we treat this one.
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $userID = $data["user"]->getID();
                $userLogin =  $data["user"]->getLogin();
                $actionChosen = $data["action"];
                $this->addFlash('info', "$userLogin has been chosen");

                switch ($actionChosen){
                    case "edit": return $this->redirectToRoute("users_edit", ["id" => $userID]);
                    case "remove": return $this->redirectToRoute("users_remove", ["id" => $userID]);
                    case "show": return $this->render('users/users_list.html.twig', [
                        'controller_name' => 'UsersController',
                        'users' => array($data["user"])
                    ]); /* pas super propre.. */
                    default: throw $this->createNotFoundException("$actionChosen/$userLogin");
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
     * @Route("/remove/{id?}", name="users_remove", requirements = {"id" = "[1-9]\d*"})
     */
    public function removeAction($id): Response
    {
        /*throw $this->createNotFoundException('Permission denied: You have to be logged.');*/

        // Default is null for id
        if ($id == null) {throw $this->createNotFoundException('Please choose a user id.');}
        else{
            $em = $this->getDoctrine()->getManager();
            $userRepository = $em->getRepository('App\Entity\Users');
            $userToRemove =  $userRepository->find($id);
            $em->remove($userToRemove);
            $em->flush();
            /*throw $this->createNotFoundException("nothing to do with user $id");*/
            $this->addFlash('info', "User $id has been removed.");
        }
        return $this->redirectToRoute("users_index");
    }

    /**
     * @Route("/edit/{id?}", name="users_edit", requirements = {"id" = "[1-9]\d*"})
     */
    public function editAction($id, Request $request): Response
    {
        /*throw $this->createNotFoundException('Permission denied: You have to be logged.');*/

        // Default is null for id
        if ($id == null) {throw $this->createNotFoundException('Please choose a user id.');}
        else{
            $em = $this->getDoctrine()->getManager();
            $userRepository = $em->getRepository('App\Entity\Users');
            $userToEdit =  $userRepository->find($id);

            $form = $this->createForm(AddUserType::class, $userToEdit);
            $form->add('send', SubmitType::class, ['label' => 'Edit']);
            // We create the form, add a submit button.
            // dump($request);
            $form->handleRequest($request);
            //dump($form);
            // if we already have a posted form, we treat this one.
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    // edit the guy
                    $em->persist($userToEdit);
                    $em->flush();
                    $this->addFlash('info', "User $id has been edited");
                }
                else {$this->addFlash('info', 'User hasn\'t been edited');}
                return $this->redirectToRoute("users_list");
            }
            else {
                $myform = $form->createView();
                return $this->render('users/users_form.html.twig', [
                    'controller_name' => 'usersController',
                    'myform' => $myform
                ]);
            }
        }
    }

}
