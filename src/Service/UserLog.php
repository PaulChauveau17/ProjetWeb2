<?php

namespace App\Service;

use Symfony\Component\Config\Definition\Exception\Exception;

class UserLog
{ // this service purpose is to get the integer defined in the config\services.yaml file and handle it

    private $status;

    public function __construct(int $param_auth){
        switch ($param_auth) {
            case 0: $this->status = "not_logged"; break;
            case 1: $this->status = "user"; break;
            case 2: $this->status = "admin"; break;
            default: throw new Exception("UserLog.php: Invalid integer ($param_auth) in services.yaml");
        }
    }

    public function getStatus(): string {
        return $this->status;
    }
}

