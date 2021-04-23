<?php

namespace App\Controller;

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
        $this->addFlash('info', 'an informative flash message');
        $this->addFlash('error', 'an error message');
        $this->addFlash('toto', 'a toto-message');

        return $this->render('models/custom.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
