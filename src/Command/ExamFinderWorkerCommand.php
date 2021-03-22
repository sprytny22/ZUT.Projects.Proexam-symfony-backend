<?php


namespace App\Command;

use App\Entity\Exam;
use App\Repository\ExamRepository;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExamFinderWorkerCommand extends Command
{
    protected static $defaultName = 'proexam:exam-worker';

    /** @var ExamRepository */
    private $examRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ExamRepository $examRepository, LoggerInterface $logger)
    {
        $this->examRepository = $examRepository;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isRunning = true;

        while($isRunning) {
            $exams = $this->examRepository->findBy(['status' => Exam::STATUS_CONFIRMED]);

            /** @var Exam $exam */
            foreach ($exams as $exam) {
                if (new \DateTime() > $exam->getStartDataTime()) {
                    $this->runStartExam($exam->getId(), $output);
                }
                try {
                    if (new \DateTime() > $exam->getStartDataTime()->add(new DateInterval('PT' . $exam->getTime() . 'M'))) {
                        $this->runEndExam($exam->getId(), $output);
                    }
                } catch (\Exception $e) {
                    return Command::FAILURE;
                }
            }

            sleep(60);
        }

        return Command::SUCCESS;
    }

    private function runStartExam(int $examId, OutputInterface $output): void
    {
        $command = $this->getApplication()->find('proexam:start-exam');
        $input = new ArrayInput(['exam' => $examId]);

        try {
            $returnCode = $command->run($input, $output);
        } catch (\Exception $e) {
            $this->logger->critical('Cant run StartExamCommand, exam cannot be started.'. $e->getMessage());
        }
    }

    private function runEndExam(int $examId, OutputInterface $output): void
    {
        $command = $this->getApplication()->find('proexam:end-exam');
        $input = new ArrayInput(['exam' => $examId]);

        try {
            $returnCode = $command->run($input, $output);
        } catch (\Exception $e) {
            $this->logger->critical('Cant run StartExamCommand, exam cannot be started.');
        }
    }
}