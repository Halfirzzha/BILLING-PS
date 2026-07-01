<?php

namespace App\Enums;

enum RoleName: string
{
    case Developer = 'developer';
    case SuperAdmin = 'super_admin';
    case Operator = 'operator';
    case Member = 'member';
}
