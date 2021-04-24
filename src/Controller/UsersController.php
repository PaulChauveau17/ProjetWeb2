<?php

namespace App\Controller;

//use App\Form\AddUserType;
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
    public function addBuildFormAction(Request $request): void /*Response*/
    {
        /*$em = $this->getDoctrine()->getManager();

        $userToAdd = new Users();
        $form = $this->createForm(AddUserType::class, $userToAdd);
        $form->add('send', SubmitType::class, ['label' => 'Add']);
        // We create the form, add a submit button.
        // dump($request);
        $form->handleRequest($request);

        // if we have an already posted form, we take this one.

        dump($form);
        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($userToAdd);
            $em->flush();
            $this->addFlash('info', 'An user as been added');
            return $this->redirectToRoute("users_add_form");
            // if the form has been submitted and it's valid, we add the entity to database and redirect to the same page (for adding other users)
        } else {
            if ($form->isSubmitted() && !($form->isValid())) {
                $this->addFlash('info', 'User hasn\'t been added');
            }

            $myform = $form->createView();
            return $this->render('users/add_user.html.twig', [
                'controller_name' => 'usersController',
                'action_intern_name' => 'users_add',
                'myform' => $myform
            ]);
        }*/
    }

    /**
     * @Route("/edit/me", name="users_edit_me")
     */
    public function editMeAction(Request $request): void/*Response*/
    {
        /*$doc = $this->getDoctrine();

        $loggedUser = new UserLog(
            $this->getParameter('param_login'),
            $this->getParameter('param_password'),
            $doc);

        if ($loggedUser->isUserLogged()) {
            if (!$loggedUser->isUserAdmin()) { // You access the page only if you are user.
                $em = $this->getDoctrine()->getManager();

                $usersRepository = $em->getRepository('App:Users');
                $myId = $loggedUser->getUser()->getId();
                $userToEdit = $usersRepository->find($myId); // un persist est fait lorsque l'on fait un find($myId) ou findAll()

                $form = $this->createForm(AddUserType::class, $userToEdit);
                $form->add('send', SubmitType::class, ['label' => 'Edit']);
                // We create the form, add a submit button.
                // dump($request);
                $form->handleRequest($request);

                // if we have an already posted form, we take this one.

                dump($form);
                if ($form->isSubmitted() && $form->isValid()) {
                    // $em->persist($userToEdit); // pas besoin
                    $em->flush();
                    $this->addFlash('info', 'User has been edited');
                    return $this->redirectToRoute("users_edit_me");
                    // if the form has been submitted and it's valid, we add the entity to database and redirect to the same page (for adding other users)
                } else {
                    if ($form->isSubmitted() && !($form->isValid())) {
                        $this->addFlash('info', 'User hasn\'t been edited');
                    }
                    $myform = $form->createView();
                    return $this->render('users/add_user.html.twig', [
                        'controller_name' => 'usersController',
                        'action_intern_name' => 'users_edit',
                        'myform' => $myform
                    ]);
                }
            } else {
                throw $this->createNotFoundException('Permission denied: You have to be an user.');
            }
        }
        else {
            throw $this->createNotFoundException('Permission denied: You have to be logged.');
            //If the user do not respect the conditions to access the page...
        }*/
    }

}
