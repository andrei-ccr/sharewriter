<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersController extends AbstractController
{
    private $passEncoder;

    public function __construct(UserPasswordEncoderInterface $passEncoder) {
        $this->passEncoder = $passEncoder;
    }

    /**
     * @Route("/users/{id}", name="users_show" , requirements={"id"="\d+"})
     */
    public function show($id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user with this id: '.$id
            );
        }

        return $this->render('users/index.html.twig', [
            'controller_name' => 'UsersController',
            'user' => $user->getEmail(),
        ]);
    }

   
}
