<?php

namespace App\Command;

use App\Service\RisqueAbandonService;
use App\Repository\SessionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:analyser-risque', description: 'Analyse le risque d\'abandon')]
class AnalyserRisqueCommand extends Command
{
    public function __construct(
        private RisqueAbandonService $risqueService,
        private SessionRepository    $sessionRepo
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Analyse Risque Abandon');

        $sessions  = $this->sessionRepo->findAll();
        $resultats = $this->risqueService->analyser($sessions);

        $io->success(sprintf(
            'Score global : %d%% — Niveau : %s',
            $resultats['score'],
            $resultats['niveau']
        ));

        foreach ($resultats['recommandations'] as $rec) {
            $io->writeln($rec);
        }

        return Command::SUCCESS;
    }
}
