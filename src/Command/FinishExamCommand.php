<?php


namespace App\Command;

use App\Entity\Exam;
use App\Entity\Result;
use App\Repository\ExamRepository;
use App\Repository\ResultRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FinishExamCommand extends Command
{
    protected static $defaultName = 'proexam:end-exam';

    /** @var ResultRepository */
    private $resultRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ExamRepository */
    private $examRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ResultRepository $resultRepository, EntityManagerInterface $entityManager, ExamRepository $examRepository, LoggerInterface $logger)
    {
        $this->resultRepository = $resultRepository;
        $this->entityManager = $entityManager;
        $this->examRepository = $examRepository;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('exam', InputArgument::REQUIRED, 'Which exam end');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $examId = $input->getArgument('exam');
        $exam = $this->examRepository->find($examId);

        if ($exam === NULL){
            $this->logger->critical("Cannot start exam, ID not found!");
            return Command::FAILURE;
        }

        $exam->setStatus(Exam::STATUS_FINISHED);

        $this->entityManager->persist($exam);
        $this->entityManager->flush();


        $results = $this->resultRepository->findBy(['exam'=> $exam]);

        foreach ($results as $result) {
            $result->setStatus(Result::STATUS_CLOSE);
            $this->entityManager->persist($result);
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}