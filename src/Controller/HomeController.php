<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\TicketType;
use App\Repository\MachineRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER') or is_granted('ROLE_MAINTAINER')")
     */
    #[Route('/', name: 'home')]
    public function index(MachineRepository $machineRepository, UserRepository $userRepository)
    {
        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $users = $userRepository->findAll();
            $customers = [];

            foreach ($users as $user) {
                if ($user->getRoles()[0] === 'ROLE_USER') {
                    $customers[] = $user;
                }
            }

            return $this->render('home-admin.html.twig', [
                'users' => $customers
            ]);
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

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route('/selection/{type}', name: 'select-users')]
    public function selectUsers(string $type, UserRepository $userRepository)
    {
        $users = $userRepository->findAll();
        $specifics = [];

        foreach ($users as $user) {
            if ($user->getRoles()[0] === 'ROLE_USER' && $type === 'customer') {
                $specifics[] = $user;
            }
            if ($user->getRoles()[0] === 'ROLE_MAINTAINER' && $type === 'maintainer') {
                $specifics[] = $user;
            }
        }

        return $this->render('home-admin.html.twig', [
            'users' => $specifics
        ]);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route('/selection/{type}/{idCustomer}', name: 'customer-view')]
    public function getUserParc(string $type, UserRepository $userRepository, MachineRepository $machineRepository, TicketRepository $ticketRepository, int $idCustomer)
    {
        if ($type === 'ROLE_USER') {
            $users = $userRepository->findAll();
            $specifics = [];

            foreach ($users as $user) {
                if ($user->getRoles()[0] === 'ROLE_USER') {
                    $specifics[] = $user;
                }
            }

            $machines = $machineRepository->findAll();
            $machinesUser = [];

            foreach ($machines as $machine) {
                if ($machine->getCustomer()->getId() === $idCustomer) {
                    $machinesUser[] = $machine;
                }
            }

            return $this->render('admin-customer-parc.html.twig', [
                'users' => $specifics,
                'machines' => $machinesUser
            ]);
        } else {
            $users = $userRepository->findAll();
            $specifics = [];

            foreach ($users as $user) {
                if ($user->getRoles()[0] === 'ROLE_MAINTAINER') {
                    $specifics[] = $user;
                }
            }

            $tickets = $ticketRepository->findAll();
            $toDo = [];

            foreach ($tickets as $ticket) {
                if ($ticket->getState() === 'À faire') {
                    $toDo[] = $ticket;
                }
            }

            return $this->render('admin-ticket-maintainer.html.twig', [
                'users' => $specifics,
                'tickets' => $toDo
            ]);
        }
    }

    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')")
     */
    #[
        Route('/category/{category}', name: 'home-category')]
    public function selectCategory(string $category, MachineRepository $machineRepository)
    {
        $machines = $machineRepository->findAll();
        $customerMachines = [];
        foreach ($machines as $machine) {
            if ($machine->getCustomer()->getId() === $this->getUser()->getId() && $machine->getCategory() === $category) {
                $customerMachines[] = $machine;
            }
        }
        return $this->render('home-user.html.twig', [
            'machines' => $customerMachines
        ]);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')")
     */
    #[Route('/machine-detail/{id}', name: 'machine-detail')]
    public function viewMachineDetail(Machine $machine, TicketRepository $ticketRepository)
    {
        $tickets = $ticketRepository->findAll();
        $currentTickets = [];
        foreach ($tickets as $ticket) {
            if ($ticket->getMachine()->getId() === $machine->getId() && $ticket->getCustomer() === $this->getUser()) {
                $currentTickets[] = $ticket;
            }
        }

        return $this->render('machine-detail.html.twig', [
            'machine' => $machine,
            'tickets' => $currentTickets,
        ]);
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     */
    #[Route('/create-ticket/{idMachine}', name: 'create-ticket')]
    public function createTicket(int $idMachine, Request $request)
    {
        $ticket = new Ticket();
        $ticket->setCustomer($this->getUser());
        $ticket->setMachine($this->em->getRepository(Machine::class)->find($idMachine));
        $machineCode = $this->em->getRepository(Machine::class)->find($idMachine)->getCode();
        $ticket->setDate(new \DateTime());
        $ticket->setState('À faire');
        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($ticket);
            $this->em->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('create-ticket.html.twig', [
            'form' => $form->createView(),
            'machine' => $machineCode
        ]);
    }
}