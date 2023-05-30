<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit',6);
        $food_id = $request->input('name');
        $status = $request->input('types');

        if($id)
        {
            $transaction = Transaction::whit('food','user')->find($id);

            if($transaction)
            {
                return ResponseFormatter::success(
                    $transaction,
                    'Data transaksi berhasil diambil'
                );
            }
            else
            {
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
            }
        }

        $transaction = Transaction::with(['food','user'])
        ->where('user_id',Auth::user()->id);

        if($food_id)
        {
            $transaction->where('food_id',$food_id);
        }

        if($status)
        {
            $transaction->where('status',$status);
        }
        

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list transaksi berhail diambil'
        );
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $transaction->update($request->all());

        return ResponseFormatter::success($transaction, 'Transaksi baerhasil diperbarui');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'food_id' => 'required|exists:food_id',
            'user_id' => 'required|exists:user_id',
            'total' => 'required',
            'status' => 'required',
        ]);

        $transaction = Transaction::create([
            'food_id' => $request->food_id,
            'user_id' => $request->user_id,
            'quantity' => $request->quantity,
            'total' => $request->total,
            'status' => $request->status,
            'payment_url' =>'',
        ]);

        //Konfigurasi Midtrans
        Config::$serverKey = config('service.midtrans.serverKey');
        config::$isProduction = config('service.midtrans.isProduction');
        config::$isSanitized = config('service.midtrans.isSanitized');
        config::$is3ds = config('service.midtrans.is3ds');

        //Panggil transaksi yang tadi dipanggil
        $transaction = Transaction::with(['food','user'])->find($transaction->id);

        //Membuat transaktion midtrans
        $midtrans = [
            'transaction_detail' => [
                'order_id' => $transaction->id,
                'gross_amount' => (int) $transaction->total,
            ],
            'customer_detail' => [
                'first_name' => $transaction->user->name,
                'email' => $transaction->user->email,
            ],
            'enabled_payment' => ['gopay','bank_tranfer'],
            'vtweb' =>[]
        ];

        //memanggil midtrans
        try{
            //ambil halaman payment midtrans
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
            $transaction->payment_url = $paymentUrl;
            $transaction->save();

            //mengembalikan data ke API
            return ResponseFormatter::success($transaction, 'Tansaksi sukses');

        }
        catch(Exception $e) {
            return ResponseFormatter::error($e->getMessage(),'Transaksi gagal');
        }
        
    }


}
