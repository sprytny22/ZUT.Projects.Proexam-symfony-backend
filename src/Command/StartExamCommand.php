<?php


namespace App\Command;

use App\Entity\Exam;
use App\Repository\ExamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartExamCommand extends Command
{
    protected static $defaultName = 'proexam:start-exam';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ExamRepository */
    private $examRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, ExamRepository $examRepository, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->examRepository = $examRepository;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('exam', InputArgument::REQUIRED, 'Which exam start');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $examId = $input->getArgument('exam');
        $exam = $this->examRepository->find($examId);

        if ($exam === NULL){
            $this->logger->critical("Cannot start exam, ID not found!");
            return Command::FAILURE;
        }

        $exam->setStatus(Exam::STATUS_PENDING);

        $this->entityManager->persist($exam);
        $this->entityManager->flush();

        return Command::SUCCESS;

    }
}