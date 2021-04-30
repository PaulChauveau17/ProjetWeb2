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
use App\Service\UserLog;

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
        // prod: The server returned a "500 Internal Server Error".
        // dev: works fine
        // I don't understand why...

        $log = new UserLog($this->getParameter('param_auth'));
        if ($log->getStatus() != "admin") {
            throw $this->createNotFoundException('Permission denied: You have to be logged as admin.');
        }

        $em = $this->getDoctrine()->getManager();
        $usersRepository = $em->getRepository('App\Entity\Users');
        $users = $usersRepository->findAll();
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
        $log = new UserLog($this->getParameter('param_auth'));
        if ($log->getStatus() == "not_logged") {
            throw $this->createNotFoundException('Permission denied: You have to be logged.');
        }

        $login = "toto";
        $em = $this->getDoctrine()->getManager();
        $usersRepository = $em->getRepository('App\Entity\Users');
        $otherToto = $usersRepository->findBy(array("login" => $login));

        if ($otherToto != null){
            throw $this->createNotFoundException("$login is already in DB");
        }else{
            $user = new Users(); // l'utilisateur est encore indépendant de Doctrine
            $user->setLogin($login)
                ->setEncPwd('otot')
                ->setName('Twopak')
                ->setSurname('Tom')
                ->setBirthDate(null)
                ->setIsAdmin(false);

            $em->persist($user); // Doctrine devient responsable de l'user
            $em->flush(); // injection physique dans la BD
            $this->addFlash('info', "$login has been added");

            return $this->redirectToRoute("users_index");
        }
    }

    /**
     * @Route("/add/admin", name="users_add_admin")
     */
    public function addAdminAction(): Response
    {
        $log = new UserLog($this->getParameter('param_auth'));
        if ($log->getStatus() == "not_logged") {
            throw $this->createNotFoundException('Permission denied: You have to be logged.');
        }

        $login = "admin";
        $em = $this->getDoctrine()->getManager();
        $usersRepository = $em->getRepository('App\Entity\Users');
        $otherAdmin = $usersRepository->findBy(array("login" => $login));

        if ($otherAdmin != null){
            throw $this->createNotFoundException("$login is already in DB");
        }else {
            $admin = new Users(); // l'utilisateur est encore indépendant de Doctrine
            $admin->setLogin($login)
                ->setEncPwd('nimda')
                ->setName('Pujadas')
                ->setSurname('David')
                ->setBirthDate(null)
                ->setIsAdmin(true);
            //dump($admin);

            $em = $this->getDoctrine()->getManager();
            $em->persist($admin); // Doctrine devient responsable de l'user
            $em->flush(); // injection physique dans la BD
            $this->addFlash('info', "$login has been added");

            // on redirige vers une autre action
            return $this->redirectToRoute("users_index");
        }
    }

    /**
     * @Route("/add/form", name="users_add_form")
     */
    public function addWithFormAction(Request $request): Response
    {
        // you can access to this even if you're not logged in order to create an account

        $form = $this->createForm(AddUserType::class);
        $form->add('send', SubmitType::class, ['label' => 'Add']);
        // We create the form, add a submit button.
        $userToAdd = $form->handleRequest($request)->getData(); // doit être fait avant la conditionnelle

        // if we already have a posted form, we treat this one.
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $userLogin = $userToAdd->getLogin();
                $em = $this->getDoctrine()->getManager();
                // Doctrine will send an error if the login is already used
                $em->persist($userToAdd);
                $em->flush();
                $this->addFlash('info', "$userLogin as been added");
            }else {
                $this->addFlash('info', 'User hasn\'t been added');
                throw $this->createNotFoundException('Invalid form.');
            }
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
        $log = new UserLog($this->getParameter('param_auth'));
        if ($log->getStatus() == "not_logged") {
            throw $this->createNotFoundException('Permission denied: You have to be logged.');
        }

        $em = $this->getDoctrine()->getManager();
        $usersRepository = $em->getRepository('App\Entity\Users');
        $users = $usersRepository->findAll();

        if ($users == null){
            throw $this->createNotFoundException("please add some users in order to choose");
        }else{
            $form = $this->createForm(ChooseUserType::class, $users);
            $form->add('send', SubmitType::class, ['label' => 'Choose']);
            // We create the form, add a submit button.

            $data = $form->handleRequest($request)->getData();
            // dump($data);
            // if we already have a posted form, we treat this one.
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $actionChosen = $data["action"];
                    $userID = $data["user"]->getID();
                    $userLogin = $data["user"]->getLogin();
                    $this->addFlash('info', "$userLogin has been chosen");

                    switch ($actionChosen) {
                        case "edit":
                            return $this->redirectToRoute("users_edit", ["id" => $userID]);
                        case "remove":
                            return $this->redirectToRoute("users_remove", ["id" => $userID]);
                        default:
                            throw $this->createNotFoundException("$actionChosen/$userID");
                    }
                } else{
                    $this->addFlash('info', 'User hasn\'t been chosen');
                    throw $this->createNotFoundException('Invalid form.');
                }
            } else{
                $myform = $form->createView();
                return $this->render('users/users_form.html.twig', [
                    'controller_name' => 'usersController',
                    'myform' => $myform
                ]);
            }
        }
    }

    /**
     * @Route("/remove/{id?}", name="users_remove", requirements = {"id" = "[1-9]\d*"})
     */
    public function removeAction($id): Response
    {
        $log = new UserLog($this->getParameter('param_auth'));
        if ($log->getStatus() != "admin") {
            throw $this->createNotFoundException('Permission denied: You have to be logged as admin.');
        }

        // Default is null for id
        if ($id == null) {throw $this->createNotFoundException('Please choose a user id.');}
        else{
            $em = $this->getDoctrine()->getManager();
            $usersRepository = $em->getRepository('App\Entity\Users');
            $userToRemove =  $usersRepository->find($id);
            if ($userToRemove != null) {
                // TODO: check if the user has carts
                $cartsRepository = $em->getRepository('App\Entity\Carts');
                $carts = $cartsRepository->findAll();
                foreach ($carts as $cart){
                    if ($cart->getUser() == $userToRemove){
                        $userLogin = $userToRemove->getLogin();
                        throw $this->createNotFoundException("Not allowed: $userLogin has at least a cart.");
                    }
                }
                // else:
                $em->remove($userToRemove);
                $em->flush();
                $this->addFlash('info', "User $id has been removed.");
            }
            else{throw $this->createNotFoundException('This id is not the pk of a user in this DB.');}
        }
        return $this->redirectToRoute("users_index");
    }

    /**
     * @Route("/edit/{id?}", name="users_edit", requirements = {"id" = "[1-9]\d*"})
     */
    public function editAction($id, Request $request): Response
    {
        // With a real auth system we could raise an exception only if the user don't edit its own profile
        $log = new UserLog($this->getParameter('param_auth'));
        if ($log->getStatus() != "admin") {
            throw $this->createNotFoundException('Permission denied: You have to be logged as admin.');
        }

        // Default is null for id
        if ($id == null) {throw $this->createNotFoundException('Please choose a user id.');}
        else{
            $em = $this->getDoctrine()->getManager();
            $usersRepository = $em->getRepository('App\Entity\Users');
            $userToEdit =  $usersRepository->find($id);

            if ($userToEdit == null) {throw $this->createNotFoundException('This id is not the pk of a user in this DB.');}
            // login is unique so an exception is gonna be raised by Doctrine if it already exist
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
                    // assuming we can edit an user which has carts
                    $em->persist($userToEdit);
                    $em->flush();
                    $this->addFlash('info', "User $id has been edited");
                }
                else {
                    $this->addFlash('info', 'User hasn\'t been edited');
                    throw $this->createNotFoundException('Invalid form.');
                }
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
