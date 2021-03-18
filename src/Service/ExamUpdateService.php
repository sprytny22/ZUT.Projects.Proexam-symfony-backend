<?php

namespace App\Service;


use App\Entity\Answer;
use App\Entity\Result;
use App\Repository\ResultRepository;
use App\Request\UpdateRequest;
use Doctrine\ORM\EntityManagerInterface;

class ExamUpdateService
{
    /** @var ResultRepository $resultRepository */
    private $resultRepository;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(ResultRepository $resultRepository)
    {
        $this->resultRepository = $resultRepository;
    }

//    public function updateFromRequest(Result $result, UpdateRequest $request)
//    {
//        $questionUuid = $request->questionUuid;
//        $currentAnswers = $result->getAnswers()->toArray();
//
//        foreach($answers as $answer) {
//            $uuid = $answer->uuid;
//
//            /** @var Answer $currentAnswer */
//            foreach($currentAnswers as $currentAnswer) {
//                $currentAnswer->getQuestion()->getUuid();
//            }
//        }
//    }
}