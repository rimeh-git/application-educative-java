<?php

namespace App\Command;

use App\Repository\SessionRepository;
use App\Service\GoogleMeetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:generate-meet-links', description: 'Genere les liens Google Meet pour les sessions sans lien')]
class GenerateMeetLinksCommand extends Command
{
    public function __construct(
        private SessionRepository    $sessionRepo,
        private GoogleMeetService    $meetService,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sessions = $this->sessionRepo->findAll();
        $count = 0;

        foreach ($sessions as $session) {
            if (!$session->getMeetLink()) {
                $session->setMeetLink($this->meetService->generateMeetLink());
                $count++;
            }
        }

        $this->em->flush();
        $output->writeln("<info>$count liens Meet generes.</info>");

        return Command::SUCCESS;
    }
}
