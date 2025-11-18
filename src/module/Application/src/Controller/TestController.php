<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Db\Adapter\Adapter;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

class TestController extends AbstractActionController
{
    private Adapter $dbAdapter;

    public function __construct(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    public function indexAction()
    {
        try {
            // DB 연결 테스트
            $connection = $this->dbAdapter->getDriver()->getConnection();
            $connection->connect();
            
            // boards 테이블 조회
            $sql = 'SELECT * FROM boards ORDER BY displayOrder';
            $statement = $this->dbAdapter->query($sql);
            $results = $statement->execute();
            
            $boards = [];
            foreach ($results as $row) {
                $boards[] = $row;
            }
            
            return new JsonModel([
                'status' => 'success',
                'message' => 'Database connection successful!',
                'boards' => $boards
            ]);
            
        } catch (\Exception $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}