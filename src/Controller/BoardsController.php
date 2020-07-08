<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Board;
use App\Entity\BoardAccess;
use App\Entity\User;

use App\Entity\Diff;


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
     * @Route("/boards/{id}/fetch", name="boards_fetch", requirements={"id"="\d+"})
     */
    public function fetchBoard($id, Request $request) {
        $board = $this->getDoctrine()->getRepository(Board::class)->find($id);

        $boardContent = $board->getContent();

        return $this->json(['content' => $boardContent, 'last_modification' => "08/07/2020 21:47"]);
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
     * @Route("/boards/{id}/allow/{email}", name="boards_email_allow", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function allowUserBoard(Request $request, $id, $email)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $boardAccess = new BoardAccess();
        $boardAccess->setEmail($this->getDoctrine()->getRepository(User::class)->findOneBy(array("email" => $email)));
        $boardAccess->setBoard($this->getDoctrine()->getRepository(Board::class)->find($id));

        $entityManager->persist($boardAccess);
        $entityManager->flush();

        return $this->json(['email' => $boardAccess->getEmail()->getEmail()]);

        return $this->render('boards/create.html.twig', [
            'controller_name' => 'BoardsController',
        ]);
    }

    /**
     * @Route("/boards/{id}/update/{userid}/{text}", name="boards_write", requirements={"id"="\d+", "userid"="\d+"}, methods={"POST"})
     */
    public function writeToBoard(Request $request, $id, $userid, $text)
    {
        $board = $this->getDoctrine()->getRepository(Board::class)->find($id);
        $entityManager = $this->getDoctrine()->getManager();

        $diff = Diff::compare($board->getContent(), $text, true);
        $added = array();
        $removed = array();

        foreach ($diff as $key=>$value) {
            if($value[1] == Diff::DELETED) {
                array_push($removed, array($value[0], $key));
            } else if($value[1] == Diff::INSERTED) {
                array_push($added, array($value[0], $key));
            }
        }

        $board->setContent($text);
        
        $entityManager->flush();

        return $this->json(['added' => $added, 'removed' => $removed]);
    }

    /**
     * @Route("/boards/{id}", name="boards_display", requirements={"id"="\d+"})
     */
    public function showBoard(Request $request, UserInterface $user, $id)
    {
        $board = $this->getDoctrine()->getRepository(Board::class)->find($id);
        
        $allowAccess = false;
        $isBoardOwner = false;

        if($board->getPrivate() === true) {
            $boardAccesses = $board->getBoardAccesses();

            foreach ($boardAccesses as $value) {
                if(($value->getBoard()->getId() == $id) && ($value->getEmail()->getId() == $user->getId())) {
                    $allowAccess = true;
                    break;
                }
            }

        } 
        else {
            $allowAccess = true;
        }


        if($board->getOwner()->getId() == $user->getId()) {

            //Owner has access
            $allowAccess = true;
            $isBoardOwner = true;
        }

        return $this->render('boards/board.html.twig', [
            'board_id' => $board->getId(),
            'board_name' => $board->getName(),
            'board_content' => $board->getContent(),
            'board_notAllowed' => !$allowAccess,
            'board_isOwner' => $isBoardOwner,
            'user_id' => $user->getId()
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
