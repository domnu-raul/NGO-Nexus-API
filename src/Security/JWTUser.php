<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser as BaseJWTUser;

class JWTUser extends BaseJWTUser
{
    private int $id;

    /**
     * @param string $userIdentifier
     * @param int $id
     * @param array $roles
     */
    public function __construct(string $userIdentifier, int $id, array $roles = [])
    {
        $this->id = $id;
        parent::__construct($userIdentifier, $roles);
    }

    /**
     * @inheritdoc
     */
    public static function createFromPayload($username, array $payload): JWTUser
    {
        return new static($username,5, (array) ($payload['roles'] ?? []));
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}