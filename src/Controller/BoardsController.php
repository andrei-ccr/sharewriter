<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Board;
use Symfony\Component\Security\Core\User\UserInterface;

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
        $boards = $this->getDoctrine()->getRepository(Board::class)->findBy(array("private" => false));

        return $this->render('boards/index.html.twig', [
            'boards' => $boards,
        ]);
    }

    /**
     * @Route("/boards/join", name="boards_join")
     */
    public function joinBoard(Request $request)
    {
        if($request->request->get("bid")) {
            return $this->redirectToRoute("boards_display", array("id"=>$request->request->get("bid")));
        }

        return $this->render('boards/join.html.twig', []);
    }

    /**
     * @Route("/boards/create", name="boards_new")
     */
    public function createBoard(Request $request)
    {
        if($request->request->get("submit")) {
            $entityManager = $this->getDoctrine()->getManager();

            $reqName = $request->request->get("name");
            $reqContent = $request->request->get("content");
            $reqPrivate = $request->request->get("private");

            if(!$reqName) $reqName = "Untitled";
            if(!$reqPrivate) $reqPrivate = false;

            $board = new Board();
            $board->setName($reqName);
            $board->setContent($reqContent);
            $board->setPrivate($reqPrivate);

            $entityManager->persist($board);

            $entityManager->flush();

            $board_id = $board->getId();

            return $this->redirectToRoute('boards_display', array("id"=>$board_id));
        }

        return $this->render('boards/create.html.twig', [
            'controller_name' => 'BoardsController',
        ]);
    }

    /**
     * @Route("/boards/{id}", name="boards_display", requirements={"id"="\d+"})
     */
    public function showBoard(Request $request, UserInterface $user, $id)
    {
        $board = $this->getDoctrine()->getRepository(Board::class)->find($id);
        
        $allowAccess = false;
        if($board->getPrivate() === true) {
            $boardAccesses = $board->getBoardAccesses();

            foreach ($boardAccesses as $value) {
                if(($value->getBoard()->getId() == $id) && ($value->getEmail()->getEmail() == $user->getId())) {
                    $allowAccess = true;
                }
            }
        } 
        else {
            $allowAccess = true;
        }

        return $this->render('boards/board.html.twig', [
            'board_name' => $board->getName(),
            'board_content' => $board->getContent(),
            'board_notAllowed' => !$allowAccess,
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
