<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    #[Route('/admin', name: 'home-admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function indexAdmin()
    {
            return $this->render('home-admin.html.twig');
    }

    #[Route('/maintainer', name: 'home-maintainer')]
    #[IsGranted('ROLE_MAINTAINER')]
    public function indexMaintainer()
    {
        return $this->render('home-maintainer.html.twig');
    }

    #[Route('/', name: 'home-user')]
    #[IsGranted('ROLE_USER')]
    public function indexUser()
    {
        return $this->render('home-user.html.twig');
    }
}