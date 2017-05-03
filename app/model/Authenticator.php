<?php

namespace App\Model;

use Nette;
use Nette\Security;

/**
 * Users authenticator.
 */
class Authenticator extends Nette\Object implements Security\IAuthenticator
{
    /** @var Nette\Database\Context */
    private $database;


    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;
        $row = $this->database->table('users')->where('username', $username)->fetch();

        if (!$row) {
            throw new Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
        } elseif (hash("sha256", $password) !== $row->password && $password !== $row->password) {
            throw new Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        }

        if ($password === $row->password) {
            $this->database->table('users')->where('username', $username)->update(array(
                'password' => hash("sha256", $row->password)
            ));
        }

        $arr = $row->toArray();
        unset($arr['password']);
        return new Security\Identity($row->id, null, $arr);
    }
}
