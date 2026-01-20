<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\FileUploadService;
use Doctrine\ORM\EntityManager;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Http\Response;

/**
 * 파일 서빙 컨트롤러
 */
class FileController extends AbstractActionController
{
    private FileUploadService $fileUploadService;
    private EntityManager $entityManager;
    private string $uploadPath;

    public function __construct(
        FileUploadService $fileUploadService,
        EntityManager $entityManager,
        string $uploadPath = 'data/uploads'
    ) {
        $this->fileUploadService = $fileUploadService;
        $this->entityManager = $entityManager;
        $this->uploadPath = rtrim($uploadPath, '/');
    }

    /**
     * 파일 다운로드/서빙
     * GET /api/files/:id
     */
    public function serveAction()
    {
        $id = (int) $this->params()->fromRoute('id');
        $attachmentRepo = $this->entityManager->getRepository(\Application\Entity\Attachment::class);
        $attachment = $attachmentRepo->find($id);

        if (!$attachment) {
            $response = $this->getResponse();
            $response->setStatusCode(404);
            $response->setContent('파일을 찾을 수 없습니다.');
            return $response;
        }

        // 파일 경로 구성
        $filePath = $this->uploadPath . '/' . $attachment->getFilePath();

        if (!file_exists($filePath)) {
            $response = $this->getResponse();
            $response->setStatusCode(404);
            $response->setContent('파일이 존재하지 않습니다.');
            return $response;
        }

        // 다운로드 카운트 증가
        $attachmentRepo->incrementDownloadCount($attachment);

        // 파일 서빙
        $response = $this->getResponse();
        $headers = $response->getHeaders();

        // MIME 타입 설정
        $mimeType = $attachment->getMimeType() ?: 'application/octet-stream';
        $headers->addHeaderLine('Content-Type', $mimeType);

        // 이미지인 경우 인라인 표시, 그 외는 다운로드
        if (strpos($mimeType, 'image/') === 0) {
            $headers->addHeaderLine('Content-Disposition', 'inline; filename="' . $attachment->getOriginalName() . '"');
        } else {
            $headers->addHeaderLine('Content-Disposition', 'attachment; filename="' . $attachment->getOriginalName() . '"');
        }

        $headers->addHeaderLine('Content-Length', (string) filesize($filePath));
        $headers->addHeaderLine('Cache-Control', 'public, max-age=31536000'); // 1년 캐시

        $response->setContent(file_get_contents($filePath));

        return $response;
    }

    /**
     * 파일 경로로 서빙 (레거시 지원용)
     * GET /api/files/path/*
     */
    public function serveByPathAction()
    {
        $path = $this->params()->fromRoute('path');

        if (!$path) {
            $response = $this->getResponse();
            $response->setStatusCode(400);
            $response->setContent('파일 경로가 필요합니다.');
            return $response;
        }

        // 경로 조작 방지
        $path = str_replace(['..', '\\'], ['', '/'], $path);
        $filePath = $this->uploadPath . '/' . $path;

        if (!file_exists($filePath) || !is_file($filePath)) {
            $response = $this->getResponse();
            $response->setStatusCode(404);
            $response->setContent('파일을 찾을 수 없습니다.');
            return $response;
        }

        // MIME 타입 감지
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        // 파일 서빙
        $response = $this->getResponse();
        $headers = $response->getHeaders();

        $headers->addHeaderLine('Content-Type', $mimeType);

        // 이미지인 경우 인라인 표시
        if (strpos($mimeType, 'image/') === 0) {
            $headers->addHeaderLine('Content-Disposition', 'inline; filename="' . basename($filePath) . '"');
        } else {
            $headers->addHeaderLine('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"');
        }

        $headers->addHeaderLine('Content-Length', (string) filesize($filePath));
        $headers->addHeaderLine('Cache-Control', 'public, max-age=31536000');

        $response->setContent(file_get_contents($filePath));

        return $response;
    }
}
