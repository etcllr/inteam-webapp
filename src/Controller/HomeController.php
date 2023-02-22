<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\MachineRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;


class HomeController extends AbstractController
{
    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') or is_granted('ROLE_MAINTAINER')")
     */
    #[Route('/', name: 'home')]
    public function index(MachineRepository $machineRepository)
    {
        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->render('home-admin.html.twig');
        } else if ($this->container->get('security.authorization_checker')->isGranted('ROLE_MAINTAINER')) {
            return $this->render('home-maintainer.html.twig');
        } else {
            $machines = $machineRepository->findAll();
            $customerMachines = [];
            foreach ($machines as $machine) {
                if ($machine->getCustomer()->getId() === $this->getUser()->getId()) {
                    $customerMachines[] = $machine;
                }
            }
            return $this->render('home-user.html.twig', [
                'machines' => $customerMachines,
            ]);
        }
    }
}