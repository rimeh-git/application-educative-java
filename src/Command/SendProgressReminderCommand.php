<?php

namespace App\Command;

use App\Repository\SessionRepository;
use App\Service\TwilioService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-progress-reminders',
    description: 'Envoie des rappels SMS aux patients sans journal de progression',
)]
class SendProgressReminderCommand extends Command
{
    public function __construct(
        private SessionRepository $sessionRepository,
        private TwilioService     $twilioService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('days',    null, InputOption::VALUE_OPTIONAL, 'Jours apres la session', 1)
            ->addOption('dry-run', null, InputOption::VALUE_NONE,     'Mode test sans envoi SMS')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $days   = (int) $input->getOption('days');
        $dryRun = (bool) $input->getOption('dry-run');

        $io->title('Envoi des rappels de progression');

        $sessions = $this->sessionRepository->findSessionsWithoutProgressTracking($days);

        if (empty($sessions)) {
            $io->success('Aucune session ne necessite de rappel');
            return Command::SUCCESS;
        }

        $io->info(sprintf('%d session(s) trouvee(s)', count($sessions)));

        $successCount = 0;
        $errorCount   = 0;

        foreach ($sessions as $session) {
            $telephone = $session->getTelephoneResponsable();
            if (!$telephone) {
                $io->warning(sprintf('Pas de telephone pour la session #%d', $session->getId()));
                continue;
            }

            $dateHeure = $session->getDateHeure();
            if ($dateHeure === null) {
                $io->warning(sprintf('Pas de date pour la session #%d', $session->getId()));
                continue;
            }
            $dateStr = $dateHeure->format('d/m/Y');

            $message = sprintf(
                'RAPPEL - Session #%d terminee le %s. Completez le journal de progression.',
                (int) $session->getId(),
                $dateStr
            );

            if ($dryRun) {
                $io->writeln(sprintf('[DRY-RUN] SMS vers %s: %s', $telephone, $message));
                $successCount++;
            } else {
                try {
                    $result = $this->twilioService->sendSms(
                        $telephone,
                        'RAPPEL - Journal de progression',
                        $dateHeure,
                        $session->getDuree()
                    );

                    if ($result['success']) {
                        $io->writeln(sprintf('SMS envoye a %s pour session #%d', $telephone, $session->getId()));
                        $successCount++;
                    } else {
                        $io->error(sprintf('Echec SMS vers %s: %s', $telephone, (string)($result['error'] ?? 'Erreur inconnue')));
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    $io->error(sprintf('Exception session #%d: %s', $session->getId(), $e->getMessage()));
                    $errorCount++;
                }
            }
        }

        $io->success(sprintf('%d succes, %d erreurs%s', $successCount, $errorCount, $dryRun ? ' (mode test)' : ''));

        return Command::SUCCESS;
    }
}
