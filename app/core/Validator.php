<?php

namespace Core;

class Validator {

    private $errors = [];

    // Validate if a field is not empty
    public function validateRequired($field, $value) {
        if (empty($value)) {
            $this->errors[$field][] = 'This field is required.';
        }
    }

    // Validate if a field is a valid email
    public function validateEmail($field, $value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = 'Invalid email format.';
        }
    }

    // Validate if the length of a field is within a specified range
    public function validateLength($field, $value, $min, $max) {
        $length = strlen($value);
        if ($length < $min || $length > $max) {
            $this->errors[$field][] = "The length must be between $min and $max characters.";
        }
    }

    // Validate if a field is an integer
    public function validateInteger($field, $value) {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field][] = 'This field must be an integer.';
        }
    }

    // Validate if a field matches a specific pattern (e.g., phone number, etc.)
    public function validatePattern($field, $value, $pattern, $message) {
        if (!preg_match($pattern, $value)) {
            $this->errors[$field][] = $message;
        }
    }

    public function hasErrors() {
        return !empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function clearErrors() {
        $this->errors = [];
    }
}
