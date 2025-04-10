<?php

namespace rollun\permission\UserProvider;

use rollun\datastore\DataStore\Interfaces\DataStoresInterface;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Query;

class GetUserByName implements GetUserInterface
{
    private $users;

    const FIELD_NAME = 'name';

    public function __construct(
        DataStoresInterface $users
    ) {
        $this->users = $users;
    }

    public function getUser(string $credential): ?array
    {
        $query = new Query();
        $query->setQuery(new EqNode(self::FIELD_NAME, $credential));
        return $this->users->query($query);
    }
}