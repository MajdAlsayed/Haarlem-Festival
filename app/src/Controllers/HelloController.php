<?php

namespace App\Controllers;

class HelloController
{
    public function greet(string $name): string
    {
        return "Hello $name";
    }
}
