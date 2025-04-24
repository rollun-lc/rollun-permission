<?php

namespace rollun\permission\UserProvider;

interface GetUserInterface
{
    public function getUser(string $credential) : ?array;
}