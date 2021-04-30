<?php

namespace App\Controller;

use App\Service\UserLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 *
 * @Route("/account")
 **/
class AccountController extends AbstractController
{ // this could be done way better
    public function buildMenuAction(): Response
    {
        $log = new UserLog($this->getParameter('param_auth'));
        $status = $log->getStatus();

        return $this->render('models/_menu.html.twig', [
            'status' => $status,
        ]);
    }

    /**
     * @Route("/log_in", name="account_log_in")
     */
    public function logInAction(): Response
    {
        $this->addFlash("toto", "please imagine that you're log in now");
        return $this->render('users/users_index.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }

    /**
     * @Route("/log_out", name="account_log_out")
     */
    public function logOutAction(): Response
    {
        $this->addFlash("toto", "please imagine that you're log out now");
        return $this->render('users/users_index.html.twig', [
            'controller_name' => 'AccountController',
        ]);
    }
}
