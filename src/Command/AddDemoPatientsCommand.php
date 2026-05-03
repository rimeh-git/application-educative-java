<?php

namespace App\Command;

use App\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-demo-patients',
    description: 'Ajouter des patients de démonstration',
)]
class AddDemoPatientsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $patients = [
            ['nom' => 'Dupont', 'prenom' => 'Lucas', 'email' => 'lucas.dupont@example.com'],
            ['nom' => 'Martin', 'prenom' => 'Emma', 'email' => 'emma.martin@example.com'],
            ['nom' => 'Bernard', 'prenom' => 'Noah', 'email' => 'noah.bernard@example.com'],
            ['nom' => 'Dubois', 'prenom' => 'Léa', 'email' => 'lea.dubois@example.com'],
            ['nom' => 'Thomas', 'prenom' => 'Louis', 'email' => 'louis.thomas@example.com'],
            ['nom' => 'Robert', 'prenom' => 'Chloé', 'email' => 'chloe.robert@example.com'],
            ['nom' => 'Petit', 'prenom' => 'Gabriel', 'email' => 'gabriel.petit@example.com'],
            ['nom' => 'Richard', 'prenom' => 'Alice', 'email' => 'alice.richard@example.com'],
        ];

        $count = 0;
        foreach ($patients as $data) {
            // Vérifier si le patient existe déjà
            $existing = $this->em->getRepository(Patient::class)
                ->findOneBy(['email' => $data['email']]);

            if ($existing) {
                $io->warning("Patient {$data['prenom']} {$data['nom']} existe déjà");
                continue;
            }

            $patient = new Patient();
            $patient->setNom($data['nom']);
            $patient->setPrenom($data['prenom']);
            $patient->setEmail($data['email']);

            $this->em->persist($patient);
            $count++;

            $io->success("✅ Patient ajouté: {$data['prenom']} {$data['nom']}");
        }

        $this->em->flush();

        $io->success("🎉 {$count} patient(s) ajouté(s) avec succès!");

        return Command::SUCCESS;
    }
}
