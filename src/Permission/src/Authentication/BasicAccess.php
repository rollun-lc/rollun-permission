<?php


namespace rollun\permission\Authentication;

use Psr\Http\Message\ServerRequestInterface;
use Mezzio\Authentication\Basic\BasicAccess as ZendBasicAccess;
use Mezzio\Authentication\UserInterface;


class BasicAccess extends ZendBasicAccess
{
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        $authHeaders = $request->getHeader('Authorization');

        if (1 !== count($authHeaders)) {
            return null;
        }

        $authHeader = array_shift($authHeaders);

        if (!preg_match('/Basic (?P<credentials>.+)/', $authHeader, $match)) {
            return null;
        }

        $decodedCredentials = base64_decode($match['credentials'], true);

        if (false === $decodedCredentials) {
            return null;
        }

        $credentialParts = explode(':', $decodedCredentials, 2);

        if (false === $credentialParts) {
            return null;
        }

        if (2 !== count($credentialParts)) {
            return null;
        }

        [$username, $password] = $credentialParts;

        if (!$password || !$username) {
            return  null;
        }

        return $this->repository->authenticate($username, $password);
    }
}