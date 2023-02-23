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
            $maintainers = [];

            foreach ($users as $user) {
                if ($user->getRoles()[0] === 'ROLE_USER') {
                    $customers[] = $user;
                }
                if ($user->getRoles()[0] === 'ROLE_MAINTAINER') {
                    $maintainers[] = $user;
                }
            }

            return $this->render('home-admin.html.twig', [
                'maintainers' => $maintainers,
                'customers' => $customers
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
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')")
     */
    #[Route('/category/{category}', name: 'home-category')]
    public function selectCategory(string $category, MachineRepository $machineRepository)
    {
        $machines = $machineRepository->findAll();
        $customerMachines = [];
        foreach ($machines as $machine) {
            if ($machine->getCustomer()->getId() === $this->getUser()->getId() && $machine->getCategory() === $category) {
                $customerMachines[] = $machine;
            }
        }
        $cat = $customerMachines[0]->getCategory();
        return $this->render('home-user.html.twig', [
            'machines' => $customerMachines,
            'cat' => $cat,
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