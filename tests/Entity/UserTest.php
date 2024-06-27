<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetId()
    {
        $user = new User();
        $this->assertNull($user->getId());
    }

    public function testGetSetEmail()
    {
        $user = new User();
        $email = 'user@example.com';
        $user->setEmail($email);
        $this->assertSame($email, $user->getEmail());
    }

    public function testGetUserIdentifier()
    {
        $user = new User();
        $email = 'user@example.com';
        $user->setEmail($email);
        $this->assertSame($email, $user->getUserIdentifier());
    }

    public function testGetSetRoles()
    {
        $user = new User();
        $roles = ['ROLE_ADMIN'];
        $user->setRoles($roles);
        $this->assertSame(array_unique(array_merge($roles, ['ROLE_USER'])), $user->getRoles());
    }

    public function testGetSetPassword()
    {
        $user = new User();
        $password = 'hashed_password';
        $user->setPassword($password);
        $this->assertSame($password, $user->getPassword());
    }

    public function testEraseCredentials()
    {
        $user = new User();
        // There is no sensitive data to erase, so this is just a call to ensure no exceptions
        $user->eraseCredentials();
        $this->assertTrue(true);
    }

    public function testGetSetCreatedAt()
    {
        $user = new User();
        $createdAt = new \DateTime();
        $user->setCreatedAt($createdAt);
        $this->assertSame($createdAt, $user->getCreatedAt());
    }

    public function testGetSetUpdatedAt()
    {
        $user = new User();
        $updatedAt = new \DateTime();
        $user->setUpdatedAt($updatedAt);
        $this->assertSame($updatedAt, $user->getUpdatedAt());
    }

    public function testGetSetUsername()
    {
        $user = new User();
        $username = 'username';
        $user->setUsername($username);
        $this->assertSame($username, $user->getUsername());
    }
}
