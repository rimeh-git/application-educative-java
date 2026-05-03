<?php
// src/Command/AnalyserPatientsCommand.php
namespace App\Command;

use App\Service\PredictionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:analyser-patients',
    description: 'Analyse tous les patients et détecte les risques'
)]
class AnalyserPatientsCommand extends Command
{
    public function __construct(private PredictionService $predictionService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🔍 Analyse des patients');

        $resultats = $this->predictionService->analyserTous();

        $normaux = count(array_filter($resultats, fn($r) => $r['prediction'] === 0));
        $risques = count(array_filter($resultats, fn($r) => $r['prediction'] === 1));

        $io->success("Analyse terminée: $normaux normaux, $risques à risque");

        if ($risques > 0) {
            $io->section('🔴 Patients à risque:');
            foreach ($resultats as $r) {
                if ($r['prediction'] === 1) {
                    $io->warning("ID {$r['patient_id']}: {$r['statut']} ({$r['score_risque']}%)");
                }
            }
        }

        return Command::SUCCESS;
    }
}