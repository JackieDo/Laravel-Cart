<?php

if (!function_exists('getCaller')) {
    /**
     * Returns an array containing information about the caller that called specific function.
     *
     * @param null|string $function The function name or syntax of class::function
     * @param int         $addSteps The number of additional steps to backward
     *
     * @return array
     */
    function getCaller($function = null, $addSteps = 0)
    {
        // Get all debug backtrace
        $traces = debug_backtrace();

        // First, we need to remove the caller that called to debug_backtrace().
        // That is the getCaller() function.
        array_shift($traces);

        $totalTraces   = count($traces);
        $stepsBackward = 1 + $addSteps;

        if (is_null($function)) {
            // This stage used to get the caller that called to the function
            // containing getCaller() function. The additional step argument
            // used to add the number of steps backward.
            if ($stepsBackward <= $totalTraces) {
                return $traces[$stepsBackward];
            }

            return [];
        }

        if (is_string($function) && '' != $function) {
            // This stage used to get the caller that called to special function
            // or method.
            $splitParts = explode('::', $function, 2);

            if (2 == count($splitParts)) {
                $class    = $splitParts[0];
                $function = $splitParts[1];
            } else {
                $class    = '';
                $function = $splitParts[0];
            }

            if ('' != $function) {
                // If we are given a function name as a string, go through all
                // the traces and find it's caller.
                $maxFindRange = $totalTraces - $stepsBackward;

                for ($i = 0; $i <= $maxFindRange; ++$i) {
                    $currTrace     = $traces[$i];
                    $traceClass    = array_key_exists('class', $currTrace) ? $currTrace['class'] : null;
                    $traceFunction = array_key_exists('function', $currTrace) ? $currTrace['function'] : '';

                    if ($traceFunction === $function) {
                        $selectedStep  = $i + $stepsBackward;

                        if (empty($class)) {
                            return $traces[$selectedStep];
                        }

                        if ($traceClass === $class) {
                            return $traces[$selectedStep];
                        }
                    }
                }
            }
        }

        // At this stage, no caller has been found.
        return [];
    }
}

if (!function_exists('isAssocArray')) {
    /**
     * Determines if an array is associative.
     *
     * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
     *
     * @param array $array
     *
     * @return bool
     */
    function isAssocArray(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }
}

if (!function_exists('array_intersect_assoc_recursive')) {
    /**
     * Recursive computes the intersection of two arrays with additional index check.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    function array_intersect_assoc_recursive(array $array1, array $array2)
    {
        $output     = [];
        $commonkeys = array_values(array_intersect(array_keys($array1), array_keys($array2)));

        foreach ($commonkeys as $key) {
            if (is_array($array1[$key]) && is_array($array2[$key])) {
                $intersect = array_intersect_assoc_recursive($array1[$key], $array2[$key]);

                if (!empty($intersect)) {
                    $output[$key] = $intersect;
                }
            } else {
                if ($array1[$key] === $array2[$key]) {
                    $output[$key] = $array1[$key];
                }
            }
        }

        return $output;
    }
}

if (!function_exists('array_diff_assoc_recursive')) {
    /**
     * Recursive computes the difference of two arrays with additional index check.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    function array_diff_assoc_recursive(array $array1, array $array2)
    {
        $output     = [];
        $commonkeys = array_values(array_intersect(array_keys($array1), array_keys($array2)));

        foreach ($commonkeys as $key) {
            if (is_array($array1[$key]) && is_array($array2[$key])) {
                $diff = array_diff_assoc_recursive($array1[$key], $array2[$key]);

                if (!empty($diff)) {
                    $output[$key] = $diff;
                }
            } else {
                if ($array1[$key] !== $array2[$key]) {
                    $output[$key] = $array1[$key];
                }
            }
        }

        return $output;
    }
}

if (!function_exists('ksort_recursive')) {
    /**
     * Recursive sort an array by key.
     *
     * @param array $array
     * @param int   $sort_flags
     *
     * @return bool
     */
    function ksort_recursive(array &$array, $sort_flags = SORT_REGULAR)
    {
        $sortArray = ksort($array, $sort_flags);

        if (!$sortArray) {
            return false;
        }

        foreach ($array as $key => $value) {
            if (is_array($array[$key])) {
                $sortValue = ksort_recursive($array[$key], $sort_flags);

                if (!$sortValue) {
                    return false;
                }
            }
        }

        return true;
    }
}
