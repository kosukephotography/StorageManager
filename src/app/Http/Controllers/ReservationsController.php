<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;

class ReservationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reservations = Reservation::all();

        return view('reservations.index', [
            'reservations' => $reservations,
            'storage_id' => '',
            'status' => '',
            'start_date' => '',
            'end_date' => '',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('reservations.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('reservations.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('reservations.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function dashboard()
    {
        return view('reservations.dashboard');
    }

    public function search(Request $request)
    {
        $reservations = Reservation::query()
        ->when($request->storage_id, function ($query, $storageId) {
            return $query->where('storage_id', 'like', '%' . $storageId . '%');
        })
        ->when($request->status, function ($query, $status) {
            return $query->where('status', $status);
        })
        ->when($request->start_date && $request->end_date, function ($query) use ($request) {
            return $query->where('start_date', '<=', $request->end_date)
                ->where('end_date', '>=', $request->start_date);
        })
        ->when($request->start_date && !isset($request->end_date), function ($query) use ($request) {
            return $query->where('end_date', '>=', $request->start_date);
        })
        ->when(!isset($request->start_date) && $request->end_date, function ($query) use ($request) {
            return $query->where('start_date', '<=', $request->end_date);
        })
        ->get();

        return view('reservations.index', [
            'reservations' => $reservations,
            'storage_id' => $request->storage_id,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
    }

    public function csv(Request $request)
    {
        // ヘッダー生成
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=reservations.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // データ取得＆生成
        $reservations = Reservation::query()
        ->when($request->storage_id, function ($query, $storageId) {
            return $query->where('storage_id', 'like', '%' . $storageId . '%');
        })
        ->when($request->status, function ($query, $status) {
            return $query->where('status', $status);
        })
        ->when($request->start_date && $request->end_date, function ($query) use ($request) {
            return $query->where('start_date', '<=', $request->end_date)
                ->where('end_date', '>=', $request->start_date);
        })
        ->when($request->start_date && !isset($request->end_date), function ($query) use ($request) {
            return $query->where('end_date', '>=', $request->start_date);
        })
        ->when(!isset($request->start_date) && $request->end_date, function ($query) use ($request) {
            return $query->where('start_date', '<=', $request->end_date);
        })
        ->get();
        $this->reservations = $reservations;

        $callback = function()
        {
            $createCsvFile = fopen('php://output', 'w'); //ファイル作成
            
            $columns = [ //1行目の情報
                'id',
                'storage_id',
                'status',
                'created_at',
                'created_by',
                'updated_at',
                'updated_by',
            ];

            mb_convert_variables('SJIS-win', 'UTF-8', $columns); //文字化け対策
    
            fputcsv($createCsvFile, $columns); //1行目の情報を追記

            $reservations = $this->reservations;

            foreach ($reservations as $reservation) {  //データを1行ずつ回す
                $csv = [
                    $reservation->id,  //オブジェクトなので -> で取得
                    $reservation->storage_id,
                    $reservation->status,
                    $reservation->created_at,
                    $reservation->created_by,
                    $reservation->updated_at,
                    $reservation->updated_by,
                ];

                mb_convert_variables('SJIS-win', 'UTF-8', $csv); //文字化け対策

                fputcsv($createCsvFile, $csv); //ファイルに追記する
            }
            fclose($createCsvFile); //ファイル閉じる
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
