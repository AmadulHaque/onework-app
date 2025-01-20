<?php

if (! function_exists('success')) {
    /**
     * Return a successful JSON response.
     *
     * @param  string  $message
     * @param  array|mixed  $data
     * @param  int  $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    function success($message, $data = [], $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}

if (! function_exists('failure')) {
    /**
     * Return a failed JSON response.
     *
     * @param  string  $message
     * @param  int  $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    function failure($message, $statusCode = 400)
    {
        return response()->json([
            'status' => 'failed',
            'message' => $message,
            'data' => null,
        ], $statusCode);
    }
}

if (! function_exists('validationError')) {

    function validationError($message, $errors = [], $statusCode = 422)
    {
        return response()->json([
            'status' => 'failed',
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}

if (! function_exists('paginateFormat')) {

    function paginateFormat($message, $entries, $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'items' => $entries->items(),
            'last_page' => $entries->lastPage(),
        ], $statusCode);
    }
}