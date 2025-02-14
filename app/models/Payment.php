<?php

namespace Model;

use Core\Model;

class Payment extends Model{
    public function createPayment($data) {
        return $this->insert('payments', $data);
    }
}