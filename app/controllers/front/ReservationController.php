<?php
namespace Controller_front;

use Core\Controller;
use Model\Reservation;

class ReservationController extends Controller
{
    protected $reservationModel;

    public function __construct() {
        parent::__construct();
        $this->reservationModel = new Reservation();
    }

    public function cancel($id) {
        if (!is_numeric($id)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid reservation ID"]);
            exit;
        }
        error_log("Attempting to cancel reservation: " . $id);
        $result = $this->reservationModel->cancelReservation($id);
    
        if ($result) {
            http_response_code(200);
            echo json_encode(["message" => "Reservation cancelled successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to cancel reservation"]);
        }
    }
    

    public function showQrCode($id) {
        $qrCode = $this->reservationModel->getQrCodeByReservationId($id);
        if (!$qrCode) {
            $this->redirect('/userDash');
        }

        $this->view('reservations/qrcode', ['qrCode' => $qrCode]);
    }
}
