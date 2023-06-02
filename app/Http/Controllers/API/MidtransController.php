<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification as MidtransNotification;


class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        //set konfigurasi midtrans
        Config::$serverKey = config('service.midtrans.serverKey');
        config::$isProduction = config('service.midtrans.isProduction');
        config::$isSanitized = config('service.midtrans.isSanitized');
        config::$is3ds = config('service.midtrans.is3ds');

        //Buat instance midtrans notifikasi
        $notification = new MidtransNotification();
    }
}
