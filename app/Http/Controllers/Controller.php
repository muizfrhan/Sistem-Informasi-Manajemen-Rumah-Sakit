<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function denyUnless(bool $condition, string $message = 'Anda tidak memiliki hak akses untuk aksi ini.'): void
    {
        abort_unless($condition, 403, $message);
    }
}
