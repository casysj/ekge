<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\Post;
use Application\Entity\Attachment;
use Application\Repository\AttachmentRepository;
use Doctrine\ORM\EntityManager;
use DateTime;

/**
 * 파일 업로드 서비스
 */
class FileUploadService
{
    private EntityManager $entityManager;
    private AttachmentRepository $attachmentRepository;
    private string $uploadPath;
    private array $allowedMimeTypes;
    private int $maxFileSize;

    public function __construct(
        EntityManager $entityManager,
        string $uploadPath = 'data/uploads',
        int $maxFileSize = 10485760 // 10MB
    ) {
        $this->entityManager = $entityManager;
        $this->attachmentRepository = $entityManager->getRepository(Attachment::class);
        $this->uploadPath = rtrim($uploadPath, '/');
        $this->maxFileSize = $maxFileSize;

        // 허용되는 MIME 타입
        $this->allowedMimeTypes = [
            // 이미지
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
            // 문서
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            // 압축
            'application/zip', 'application/x-zip-compressed',
            'application/x-rar-compressed',
            // 텍스트
            'text/plain',
            // 오디오
            'audio/mpeg', 'audio/mp3',
            // 비디오
            'video/mp4', 'video/mpeg',
        ];
    }

    /**
     * 파일 업로드
     *
     * @param array $fileData $_FILES에서 받은 파일 데이터
     * @param Post $post 게시글
     * @param int $displayOrder 표시 순서
     * @return Attachment
     * @throws \Exception
     */
    public function uploadFile(array $fileData, Post $post, int $displayOrder = 0): Attachment
    {
        // 파일 검증
        $this->validateFile($fileData);

        // 저장 경로 생성
        $yearMonth = date('Y/m');
        $uploadDir = $this->uploadPath . '/' . $yearMonth;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 안전한 파일명 생성
        $originalName = $fileData['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $savedName = $this->generateSafeFileName($extension);
        $filePath = $uploadDir . '/' . $savedName;

        // 파일 이동
        if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
            throw new \Exception('파일 업로드 실패');
        }

        // MIME 타입 및 파일 타입 결정
        $mimeType = mime_content_type($filePath);
        $fileType = $this->getFileType($mimeType);

        // Attachment 엔티티 생성
        $attachment = new Attachment();
        $attachment->setPost($post)
                   ->setOriginalName($originalName)
                   ->setSavedName($savedName)
                   ->setFilePath($yearMonth . '/' . $savedName)
                   ->setFileSize($fileData['size'])
                   ->setMimeType($mimeType)
                   ->setFileType($fileType)
                   ->setDisplayOrder($displayOrder);

        // 이미지인 경우 크기 정보 저장
        if ($fileType === 'image') {
            $imageInfo = getimagesize($filePath);
            if ($imageInfo) {
                $attachment->setImageWidth($imageInfo[0])
                          ->setImageHeight($imageInfo[1]);

                // 썸네일 생성 (선택사항)
                // $this->createThumbnail($filePath, $uploadDir . '/thumb_' . $savedName);
            }
        }

        $this->entityManager->persist($attachment);
        $this->entityManager->flush();

        return $attachment;
    }

    /**
     * 다중 파일 업로드
     *
     * @param array $filesData $_FILES에서 받은 다중 파일 데이터
     * @param Post $post 게시글
     * @return Attachment[]
     */
    public function uploadMultipleFiles(array $filesData, Post $post): array
    {
        $attachments = [];
        $displayOrder = 0;

        foreach ($filesData as $fileData) {
            if ($fileData['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            try {
                $attachments[] = $this->uploadFile($fileData, $post, $displayOrder++);
            } catch (\Exception $e) {
                // 로그 기록 또는 에러 수집
                continue;
            }
        }

        return $attachments;
    }

    /**
     * 파일 삭제
     */
    public function deleteFile(Attachment $attachment): bool
    {
        $filePath = $this->uploadPath . '/' . $attachment->getFilePath();

        // 실제 파일 삭제
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // DB에서 삭제
        $this->entityManager->remove($attachment);
        $this->entityManager->flush();

        return true;
    }

    /**
     * 파일 검증
     *
     * @throws \Exception
     */
    private function validateFile(array $fileData): void
    {
        // 업로드 에러 확인
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('파일 업로드 오류: ' . $this->getUploadErrorMessage($fileData['error']));
        }

        // 파일 크기 확인
        if ($fileData['size'] > $this->maxFileSize) {
            $maxSizeMB = $this->maxFileSize / 1024 / 1024;
            throw new \Exception("파일 크기가 제한을 초과했습니다. (최대: {$maxSizeMB}MB)");
        }

        // MIME 타입 확인
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileData['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            throw new \Exception('허용되지 않는 파일 형식입니다.');
        }

        // 파일명 길이 확인
        if (strlen($fileData['name']) > 255) {
            throw new \Exception('파일명이 너무 깁니다.');
        }
    }

    /**
     * 안전한 파일명 생성
     */
    private function generateSafeFileName(string $extension): string
    {
        return uniqid('file_', true) . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }

    /**
     * MIME 타입으로 파일 타입 결정
     */
    private function getFileType(string $mimeType): string
    {
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        } elseif (strpos($mimeType, 'audio/') === 0) {
            return 'audio';
        } elseif (strpos($mimeType, 'video/') === 0) {
            return 'video';
        } elseif (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
        ])) {
            return 'document';
        } else {
            return 'other';
        }
    }

    /**
     * 업로드 에러 메시지
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => '파일이 php.ini의 upload_max_filesize를 초과했습니다.',
            UPLOAD_ERR_FORM_SIZE => '파일이 HTML 폼의 MAX_FILE_SIZE를 초과했습니다.',
            UPLOAD_ERR_PARTIAL => '파일이 부분적으로만 업로드되었습니다.',
            UPLOAD_ERR_NO_FILE => '파일이 업로드되지 않았습니다.',
            UPLOAD_ERR_NO_TMP_DIR => '임시 폴더가 없습니다.',
            UPLOAD_ERR_CANT_WRITE => '디스크에 파일을 쓸 수 없습니다.',
            UPLOAD_ERR_EXTENSION => 'PHP 확장에 의해 파일 업로드가 중지되었습니다.',
        ];

        return $errors[$errorCode] ?? '알 수 없는 오류';
    }

    /**
     * 이미지 썸네일 생성 (선택사항)
     */
    private function createThumbnail(string $sourcePath, string $destPath, int $maxWidth = 300, int $maxHeight = 300): bool
    {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        [$width, $height, $type] = $imageInfo;

        // 비율 계산
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int) ($width * $ratio);
        $newHeight = (int) ($height * $ratio);

        // 원본 이미지 로드
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        // 썸네일 생성
        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // 저장
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $destPath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumb, $destPath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumb, $destPath);
                break;
        }

        imagedestroy($source);
        imagedestroy($thumb);

        return true;
    }

    /**
     * 파일 다운로드 (다운로드 수 증가)
     */
    public function downloadFile(Attachment $attachment): string
    {
        $this->attachmentRepository->incrementDownloadCount($attachment);
        return $this->uploadPath . '/' . $attachment->getFilePath();
    }
}
