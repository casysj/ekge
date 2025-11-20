<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\User;
use Application\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Laminas\Session\Container;

/**
 * 인증 서비스
 */
class AuthenticationService
{
    private EntityManager $entityManager;
    private UserRepository $userRepository;
    private Container $session;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->session = new Container('auth');
    }

    /**
     * 로그인
     *
     * @param string $username 사용자명
     * @param string $password 평문 비밀번호
     * @return array ['success' => bool, 'message' => string, 'user' => User|null]
     */
    public function login(string $username, string $password): array
    {
        $user = $this->userRepository->authenticate($username, $password);

        if (!$user) {
            return [
                'success' => false,
                'message' => '사용자명 또는 비밀번호가 올바르지 않습니다.',
                'user' => null,
            ];
        }

        // 세션에 사용자 정보 저장
        $this->session->userId = $user->getId();
        $this->session->username = $user->getUsername();
        $this->session->role = $user->getRole();
        $this->session->displayName = $user->getDisplayName();

        return [
            'success' => true,
            'message' => '로그인 성공',
            'user' => $user,
        ];
    }

    /**
     * 로그아웃
     */
    public function logout(): void
    {
        $this->session->getManager()->destroy();
    }

    /**
     * 현재 로그인한 사용자 확인
     */
    public function isAuthenticated(): bool
    {
        return isset($this->session->userId);
    }

    /**
     * 현재 로그인한 사용자 가져오기
     */
    public function getCurrentUser(): ?User
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $this->userRepository->find($this->session->userId);
    }

    /**
     * 현재 사용자 ID
     */
    public function getCurrentUserId(): ?int
    {
        return $this->session->userId ?? null;
    }

    /**
     * 현재 사용자 이름
     */
    public function getCurrentUsername(): ?string
    {
        return $this->session->username ?? null;
    }

    /**
     * 현재 사용자 역할
     */
    public function getCurrentUserRole(): ?string
    {
        return $this->session->role ?? null;
    }

    /**
     * 관리자 권한 확인
     */
    public function isAdmin(): bool
    {
        return $this->getCurrentUserRole() === 'admin';
    }

    /**
     * 에디터 이상 권한 확인
     */
    public function canEdit(): bool
    {
        $role = $this->getCurrentUserRole();
        return $role === 'admin' || $role === 'editor';
    }

    /**
     * 권한 확인
     *
     * @param string $requiredRole 필요한 역할 ('admin' 또는 'editor')
     * @return bool
     */
    public function hasRole(string $requiredRole): bool
    {
        $currentRole = $this->getCurrentUserRole();

        if ($requiredRole === 'admin') {
            return $currentRole === 'admin';
        }

        if ($requiredRole === 'editor') {
            return $currentRole === 'admin' || $currentRole === 'editor';
        }

        return false;
    }

    /**
     * 비밀번호 변경
     *
     * @param User $user 사용자
     * @param string $oldPassword 현재 비밀번호
     * @param string $newPassword 새 비밀번호
     * @return array ['success' => bool, 'message' => string]
     */
    public function changePassword(User $user, string $oldPassword, string $newPassword): array
    {
        // 현재 비밀번호 확인
        if (!password_verify($oldPassword, $user->getPassword())) {
            return [
                'success' => false,
                'message' => '현재 비밀번호가 올바르지 않습니다.',
            ];
        }

        // 새 비밀번호 길이 확인
        if (strlen($newPassword) < 6) {
            return [
                'success' => false,
                'message' => '새 비밀번호는 최소 6자 이상이어야 합니다.',
            ];
        }

        // 비밀번호 변경
        $this->userRepository->changePassword($user, $newPassword);

        return [
            'success' => true,
            'message' => '비밀번호가 변경되었습니다.',
        ];
    }

    /**
     * 사용자 생성 (관리자 전용)
     *
     * @param array $data 사용자 데이터
     * @return array ['success' => bool, 'message' => string, 'user' => User|null]
     */
    public function createUser(array $data): array
    {
        // 사용자명 중복 확인
        if ($this->userRepository->findByUsername($data['username'])) {
            return [
                'success' => false,
                'message' => '이미 존재하는 사용자명입니다.',
                'user' => null,
            ];
        }

        // 이메일 중복 확인
        if ($this->userRepository->findByEmail($data['email'])) {
            return [
                'success' => false,
                'message' => '이미 존재하는 이메일입니다.',
                'user' => null,
            ];
        }

        // 비밀번호 길이 확인
        if (strlen($data['password']) < 6) {
            return [
                'success' => false,
                'message' => '비밀번호는 최소 6자 이상이어야 합니다.',
                'user' => null,
            ];
        }

        try {
            $user = $this->userRepository->createUser(
                $data['username'],
                $data['password'],
                $data['email'],
                $data['displayName'],
                $data['role'] ?? 'editor'
            );

            return [
                'success' => true,
                'message' => '사용자가 생성되었습니다.',
                'user' => $user,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '사용자 생성 중 오류가 발생했습니다: ' . $e->getMessage(),
                'user' => null,
            ];
        }
    }

    /**
     * 사용자 활성화/비활성화
     */
    public function toggleUserStatus(User $user): void
    {
        $user->setIsActive(!$user->isActive());
        $this->entityManager->flush();
    }
}
