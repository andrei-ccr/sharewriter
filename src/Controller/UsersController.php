<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    /**
     * @Route("/users/new", name="users_create")
     */
    public function createUser()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $user = new Users();
        $user->setEmail('testemail@domain.com');
        $user->setPwd('1999');

        $entityManager->persist($user);

        $entityManager->flush();

        return $this->render('users/index.html.twig', [
            'controller_name' => 'UsersController',
            'user' => $user->getEmail(),
        ]);
    }


    /**
    * @Route("/users/login", name="users_login")
    */
    public function login(Request $request)
    {

        if($request->request->get('email') && $request->request->get('pwd') ) {
            //Login here
            return new Response($request->request->get('email'));
        } 
        else {
            return $this->render('users/login.html.twig', []);
        }
        
        /*
        else {
            $entityManager = $this->getDoctrine()->getManager();

            $user = new Users();
            $user->setEmail('testemail@domain.com');
            $user->setPwd('1999');

            $entityManager->persist($user);

            $entityManager->flush();
            return new Response('Added a new user ');
        } 

        return new Response($request->query->get('test'));*/
        
    }

    /**
     * @Route("/users/{id}", name="users_show" , requirements={"id"="\d+"})
     */
    public function show($id)
    {
        $user = $this->getDoctrine()->getRepository(Users::class)->find($id);

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
