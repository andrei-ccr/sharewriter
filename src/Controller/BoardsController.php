<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BoardsController extends AbstractController
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator) {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/boards", name="boards")
     */
    public function showBoards()
    {
        return $this->render('boards/index.html.twig', [
            'controller_name' => 'BoardsController',
        ]);
    }

    /**
     * @Route("/", name="mainPage")
     */
    public function mainPage() {
        if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new RedirectResponse($this->urlGenerator->generate('boards'));
        } else {
            return $this->render('index.html.twig', []);
        }
    }
}
