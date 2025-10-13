<?php

namespace App\Traits;

trait ResponseTrait
{
    public function success200($data = null, $message = 'Success')
    {
        return response()->json([
            'data' => $data,
            'status' => 200,
            'message' => $message,
            'success' => true,
        ], 200);
    }

    public function success201($data = null, $message = 'Created')
    {
        return response()->json([
            'data' => $data,
            'status' => 201,
            'message' => $message,
            'success' => true,
        ], 201);
    }

    public function success202($data = null)
    {
        return response()->json([
            'data' => $data,
            'status' => 202,
            'message' => 'The request has been accepted for processing',
        ], 202);
    }

    public function error400($errors = null, $message = 'Bad Request')
    {
        return response()->json([
            'errors' => $errors,
            'status' => 400,
            'message' => $message,
        ], 400);
    }

    public function error401($errors = 'Unauthorized', $message = 'Unauthorized')
    {
        return response()->json([
            'status' => 401,
            'errors' => $errors,
            'message' => $message,
            'success' => false,
        ], 401);
    }

    public function error403($errors = 'Forbidden')
    {
        return response()->json([
            'errors' => $errors,
            'status' => 403,
            'message' => 'Forbidden',
            'success' => false,
        ], 403);
    }

    public function error404($message = 'Not Found')
    {
        return response()->json([
            'status' => 404,
            'message' => $message,
            'errors' => 'Not Found',
        ], 404);
    }

    public function error422($errors, $message = 'Unprocessable Content')
    {
        return response()->json([
            'message' => $message,
            'status' => 422,
            'errors' => $errors,

        ], 422);
    }

    public function error500($errors = null, $message = 'Internal Server Error')
    {
        return response()->json([
            'errors' => $errors,
            'status' => 500,
            'message' => $message,
            'success' => false,
        ], 500);
    }
}

