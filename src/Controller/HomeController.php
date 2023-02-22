<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') or is_granted('ROLE_MAINTAINER')")
     */
    #[Route('/', name: 'home')]
    public function index()
    {
        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->render('home-admin.html.twig');
        } else if ($this->container->get('security.authorization_checker')->isGranted('ROLE_MAINTAINER')) {
            return $this->render('home-maintainer.html.twig');
        } else {
            return $this->render('home-user.html.twig');
       }
    }
}