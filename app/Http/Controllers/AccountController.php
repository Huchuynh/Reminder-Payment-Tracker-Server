<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class AccountController extends Controller
{
    use ApiResponseTrait;
    
    public function me(Request $request)
    {
        return $this->responseSuccess($request->user());
    }
}
