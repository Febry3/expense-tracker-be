<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

class Utils extends Controller
{
    public static function responseHelper($statusCode, $status, $message, $error = "", $data = null, $token = "")
    {
        if ($token != "") {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'token' => $token
            ], $statusCode);
        }

        if ($data == null) {
            if ($error == "") {
                return response()->json([
                    'status' => $status,
                    'message' => $message,
                ], $statusCode);
            } else {
                return response()->json([
                    'status' => $status,
                    'message' => $message,
                    'error' => $error
                ], $statusCode);
            }
        } else {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $data
            ], $statusCode);
        }
    }
}
