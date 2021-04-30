<?php

namespace App\Controller;

use App\Service\UserLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 *
 * @Route("/test")
 * */
class TestController extends AbstractController
{
    /**
     * @Route("/", name="test_index")
     */
    public function indexAction(): Response
    {
        $log = new UserLog($this->getParameter('param_auth'));
        // dump($log->getStatus());
        $status = $log->getStatus();

        switch ($status) {
            case "not_logged": $this->addFlash('info', "You're not logged dude."); break;
            case "user": $this->addFlash('info', "Oh! An user, please shop some items."); break;
            case "admin": $this->addFlash('info', "Oh you're alive ?! Grab a coffee, we need to talk of this website.."); break;
            default: throw $this->createNotFoundException("Is this controller updated sometimes ?");
        }
        $this->addFlash('error', 'an error message');
        $this->addFlash('toto', 'a toto-message');

        return $this->render('test/test_index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
