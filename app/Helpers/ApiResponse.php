<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

if (!function_exists('apiResponse')) {
    /**
     * Return a standardized JSON API response
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @param array $extra
     * @return \Illuminate\Http\JsonResponse
     */
    function apiResponse(string $message, $data = null, int $statusCode = 200, array $extra = [])
    {
        if (App::environment('production')) {
            if ($statusCode >= 500) {
                Log::error($message);
                $message = __('error.internal_server_error');
            }

            if ($statusCode >= 404) {
                $message = __('error.data_not_found');
            }

            if ($statusCode == 409) {
                $message = __('error.conflict');
            }
        }

        $response = [
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }

        return response()->json($response, $statusCode);
    }
}
