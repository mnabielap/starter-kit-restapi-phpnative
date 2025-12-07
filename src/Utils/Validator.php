<?php

namespace App\Utils;

class Validator
{
    /**
     * Validates data against rules.
     * Rules format: ['email' => 'required|email', 'password' => 'required|min:8']
     * @return array|bool Returns validated data on success, throws ApiError on failure.
     */
    public static function validate($data, $rules)
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $value = isset($data[$field]) ? $data[$field] : null;

            foreach ($ruleList as $rule) {
                // Parse rule parameters (e.g., min:8)
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $paramStr] = explode(':', $rule);
                    $rule = $ruleName;
                    $params = explode(',', $paramStr);
                }

                if ($rule === 'required' && (is_null($value) || $value === '')) {
                    $errors[] = "$field is required";
                    break; 
                }

                if ($value !== null && $value !== '') {
                    if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "$field must be a valid email";
                    }
                    if ($rule === 'min' && strlen($value) < $params[0]) {
                        $errors[] = "$field must be at least {$params[0]} characters";
                    }
                    if ($rule === 'alpha_num' && !ctype_alnum($value)) {
                        $errors[] = "$field must contain only letters and numbers";
                    }
                    if ($rule === 'in' && !in_array($value, $params)) {
                         $errors[] = "$field must be one of: " . implode(', ', $params);
                    }
                }
            }
            // Keep only defined fields in result
            if (array_key_exists($field, $data)) {
                $validated[$field] = $value;
            }
        }

        if (!empty($errors)) {
            throw new ApiError(400, implode(', ', $errors));
        }

        return $validated;
    }
}