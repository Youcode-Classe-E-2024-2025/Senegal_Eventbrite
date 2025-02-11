<?php

namespace Core;

class Validator {
    private $data;
    private $rules;
    private $errors = [];

    public function __construct(array $data, array $rules) {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    private function validate() {
        foreach ($this->rules as $field => $rules) {
            foreach ($rules as $rule) {
                $this->applyRule($field, $rule);
            }
        }
    }

    private function applyRule($field, $rule) {
        $value = $this->data[$field] ?? '';

        if ($rule === 'required') {
            $this->validateRequired($field, $value);
        } elseif ($rule === 'email') {
            $this->validateEmail($field, $value);
        } elseif (preg_match('/min:(\d+)/', $rule, $matches)) {
            $this->validateMinLength($field, $value, (int)$matches[1]);
        } elseif (preg_match('/max:(\d+)/', $rule, $matches)) {
            $this->validateMaxLength($field, $value, (int)$matches[1]);
        } elseif (preg_match('/unique:([\w_]+),([\w_]+)/', $rule, $matches)) {
            $this->validateUnique($field, $value, $matches[1], $matches[2]);
        } elseif ($rule === 'confirmed') {
            $this->validateConfirmed($field);
        }
    }

    private function validateRequired($field, $value) {
        if (empty(trim($value))) {
            $this->errors[$field][] = "The $field field is required.";
        }
    }

    private function validateEmail($field, $value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "The $field must be a valid email address.";
        }
    }

    private function validateMinLength($field, $value, $min) {
        if (strlen($value) < $min) {
            $this->errors[$field][] = "The $field must be at least $min characters.";
        }
    }

    private function validateMaxLength($field, $value, $max) {
        if (strlen($value) > $max) {
            $this->errors[$field][] = "The $field must not exceed $max characters.";
        }
    }

    private function validateUnique($field, $value, $table, $column) {
        // Assuming a Database class exists
        $db = new Database(); 
        $result = $db->query("SELECT COUNT(*) as count FROM $table WHERE $column = ?", [$value]);

        if ($result[0]['count'] > 0) {
            $this->errors[$field][] = "The $field is already taken.";
        }
    }

    private function validateConfirmed($field) {
        $confirmationField = $field . '_confirmation';
        if (!isset($this->data[$confirmationField]) || $this->data[$confirmationField] !== $this->data[$field]) {
            $this->errors[$field][] = "The $field confirmation does not match.";
        }
    }

    public function fails() {
        return !empty($this->errors);
    }

    public function errors() {
        return $this->errors;
    }
}
