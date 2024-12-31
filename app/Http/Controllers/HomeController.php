<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Car;
use App\Models\bayar;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Str;


class HomeController extends Controller
{
    public function index(){
        
        $cars = Car::latest()->get();
        return view('frontend.homepage', compact('cars'));
    }

    public function contact(){
        return view('frontend.contact');
    }

    public function contactStore(Request $request){
       $data = $request->validate([
            'nama' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'pesan' => 'required'  
       ]);

       Message::create($data);

       return redirect()->back()->with([
        'message' => 'Pesan anda berhasil dikirim',
        'alert-type' => 'Sukses'
       ]);
    }

    public function detail(Car $car){

        return view('frontend.detail', compact('car'));
    }

    public function bayar(Car $car){

        return view('frontend.bayar', compact('car'));
    }

    //FUNCTION DOWNLOAD INVOICE
    public function downloadInvoice($orders_id)
    {
       // Ambil data pembayaran berdasarkan ID
       $bayars = Bayar::findOrFail($orders_id);
        
       // Generate PDF dari view invoice
       $pdf = PDF::loadView('invoice', compact('bayars'));
       
       // Download PDF dengan nama invoice-<id>.pdf
       return $pdf->download('invoice-' . $bayars->orders_id . '.pdf');
    }

    //FUNCTION MIDTRANS
    public function bayarStore(Request $request, $slug)
    {
        // Ambil data mobil berdasarkan slug
        $car = Car::where('slug', $slug)->first();
    
        // Validasi input dari request
        $validatedData = $request->validate([
            'mobil' => 'required', // Validasi untuk mobil
            'harga' => 'required',
            'nama' => 'required',
            'nomor' => 'required',
            'hari' => 'required',
            'status' => 'Unpaid', // Status default
        ]);
    
       // Hitung total harga berdasarkan jumlah hari
       $harga = $validatedData['harga'] * $validatedData['hari'];

       // Tambahkan total_harga ke dalam data yang akan disimpan
       $validatedData['harga_total'] = $harga;

        // Generate ID unik
       $validatedData['orders_id'] = uniqid();
       
        // Simpan data pembayaran ke database
        $bayars = Bayar::create($validatedData);
    
        // Konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.serverKey');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    
        // Buat parameter transaksi
        $params = [
            'transaction_details' => [
                'order_id' => $bayars->orders_id, // Pastikan sesuai dengan format Midtrans
                'gross_amount' =>$bayars->harga_total, // Pastikan sesuai dengan format Midtrans
            ],
            'item_details' => [
                [
                    'id' => $bayars->orders_id, // ID Orders
                    'name' => $bayars->mobil, // Gunakan nama mobil dari data yang disimpan di bayars
                    'quantity' => $bayars->hari, // Jumlah hari yang disewa
                    'price' => $bayars->harga, // Harga per hari
                ]
            ],
            'customer_details' => [
                'first_name' => $bayars->nama, // Nama pemesan
                'phone' => $bayars->nomor, // Nomor telepon pemesan
            ],
        ];
    
        // Dapatkan Snap Token dari Midtrans
        $snapToken = \Midtrans\Snap::getSnapToken($params);
        // Kembalikan ke view dan mengirim variable snapToken serta data pembayaran
        return view('frontend.sewa', compact('snapToken', 'bayars'));
    }
    
    // callback midtrans 
    public function callback(Request $request){
        $serverKey = config('midtrans.serverKey');
        $hashed = hash("sha512", $request->order_id.$request->status_code.$request->gross_amount.$serverKey);
        if($hashed == $request->signature_key){
            if($request->transaction_status == 'capture' || $request->transaction_status == 'settlement'){
                $bayars = Bayar::find($request->order_id);
                $bayars->update(['status' => 'Paid']);
            }
        }
    }

    public function invoice($orders_id){
        $bayars = Bayar::find($orders_id);
        return view('frontend.invoice', compact('bayars'));
     }

}