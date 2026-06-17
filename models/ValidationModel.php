<?php

function appIsValidUsername(string $username): bool
{
    return strlen($username) >= 5
        && preg_match('/[A-Z]/', $username)
        && preg_match('/[a-z]/', $username);
}

function appIsValidPassword(string $password): bool
{
    return strlen($password) >= 10;
}
