<?php

if (!function_exists('process')) {
    function process($success, $WrongCode = 422)
    {
        return response()->json(['success' => $success], $success ? 200 : $WrongCode);
    }
}