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
        $this->reservationModel->cancelReservation($id);
        $this->redirect('/reservations');
    }

    public function showQrCode($id) {
        $qrCode = $this->reservationModel->getQrCodeByReservationId($id);
        if (!$qrCode) {
            $this->redirect('/reservations');
        }

        $this->view('reservations/qrcode', ['qrCode' => $qrCode]);
    }
}
