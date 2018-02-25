<?php

namespace Laravel\Passport\Bridge;

use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Connection;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    use FormatsScopesForStorage;

    /**
     * The database connection.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $database;

    /**
     * The session.
     *
     * @var Session
     */
    protected $session;

    /**
     * Create a new repository instance.
     *
     * @param  \Illuminate\Database\Connection $database
     * @param  Session $session
     */
    public function __construct(Connection $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        return new AuthCode;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $
        $this->database->table('oauth_auth_codes')->insert([
            'id' => $authCodeEntity->getIdentifier(),
            'user_id' => $authCodeEntity->getUserIdentifier(),
            'client_id' => $authCodeEntity->getClient()->getIdentifier(),
            'scopes' => $this->formatScopesForStorage($authCodeEntity->getScopes()),
            'revoked' => false,
            'expires_at' => $authCodeEntity->getExpiryDateTime(),
        ]);

        $this->database->table('oauth_sessions')->insert([
            'session_id' => $this->session->getId(),
            'code_id' => $authCodeEntity->getIdentifier(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        $this->database->table('oauth_auth_codes')
                    ->where('id', $codeId)->update(['revoked' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        return $this->database->table('oauth_auth_codes')
                    ->where('id', $codeId)->where('revoked', 1)->exists();
    }
}
