<?php

declare(strict_types=1);

namespace Application\Repository;

use Application\Entity\User;
use Doctrine\ORM\EntityRepository;
use DateTime;

/**
 * 사용자 Repository
 */
class UserRepository extends EntityRepository
{
    /**
     * 사용자명으로 찾기
     */
    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    /**
     * 이메일로 찾기
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * 활성 사용자만 조회
     * @return User[]
     */
    public function findActiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 역할별 사용자 조회
     * @return User[]
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.role = :role')
            ->andWhere('u.isActive = :active')
            ->setParameter('role', $role)
            ->setParameter('active', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 관리자 목록 조회
     * @return User[]
     */
    public function findAdmins(): array
    {
        return $this->findByRole('admin');
    }

    /**
     * 로그인 시간 업데이트
     */
    public function updateLastLogin(User $user): void
    {
        $user->setLastLoginAt(new DateTime());
        $this->getEntityManager()->flush();
    }

    /**
     * 사용자 인증 (username + password)
     *
     * @param string $username 사용자명
     * @param string $password 평문 비밀번호
     * @return User|null 인증 성공시 User 객체, 실패시 null
     */
    public function authenticate(string $username, string $password): ?User
    {
        $user = $this->findByUsername($username);

        if (!$user || !$user->isActive()) {
            return null;
        }

        // 비밀번호 검증
        if (password_verify($password, $user->getPassword())) {
            $this->updateLastLogin($user);
            return $user;
        }

        return null;
    }

    /**
     * 비밀번호 변경
     */
    public function changePassword(User $user, string $newPassword): void
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->setPassword($hashedPassword);
        $this->getEntityManager()->flush();
    }

    /**
     * 사용자 생성
     */
    public function createUser(
        string $username,
        string $password,
        string $email,
        string $displayName,
        string $role = 'editor'
    ): User {
        $user = new User();
        $user->setUsername($username)
             ->setPassword(password_hash($password, PASSWORD_DEFAULT))
             ->setEmail($email)
             ->setDisplayName($displayName)
             ->setRole($role)
             ->setIsActive(true);

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }
}
