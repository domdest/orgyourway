<?php

namespace App\Controller\Admin\User;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class CheckInController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(
        '/admin/user/{user}/check_in',
        name: 'app_admin_user_checkin',
        methods: ['GET']
    )]
    public function checkInUser(User $user): RedirectResponse
    {
        $filteredTickets = $user->getTickets()->filter(function ($element) {
            return $element->getEvent()->getStartedAt() <= new DateTime('now') &&
                $element->getEvent()->getEndedAt() >= new DateTime('now');
        })->toArray();

        if (empty($filteredTickets)) {
            //TODO: build a fail response for user
            die('no valid tickets!');
        }

        foreach ($filteredTickets as $ticket) {
            $ticket->setCheckedIn(true);
            $this->entityManager->persist($ticket);
        }

        $this->entityManager->flush();

        return new RedirectResponse('/admin/app/ticket/' . $filteredTickets[0]->getId() . '/show');
    }
}