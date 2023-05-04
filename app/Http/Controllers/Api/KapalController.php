<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kapal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use TheSeer\Tokenizer\Exception;

class KapalController extends Controller
{
    public function index()
    {
        $query = Kapal::when(request()->kode, function ($q) {
            return $q->where('kode', 'like', '%' . request()->kode . '%');
        })
            ->where('user_id', auth()->user()->id)
            ->get();
        return response()->json([
            'success' => true,
            'kapal'    => $query
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'kode'     => 'required|string|max:255|unique:kapals',
            'nama_kapal' => 'required',
            'nama_pemilik' => 'required',
            'alamat_pemilik' => 'required',
            'ukuran_kapal' => 'required',
            'kapten' => 'required',
            'jumlah_anggota' => 'required',
            'foto_kapal' => 'required|mimes:jpeg,jpg,png,gif|max:10000',
            'nomor_izin' => 'required',
            'dokumen_perizinan' => 'required|mimes:jpeg,jpg,png,gif|max:10000',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        DB::beginTransaction();
        try {
            $image_kapal = $request->file('foto_kapal');
            $image_kapal->storeAs('public/kapal', $image_kapal->hashName());

            $dokumen_perizinan = $request->file('dokumen_perizinan');
            $dokumen_perizinan->storeAs('public/kapal', $dokumen_perizinan->hashName());

            $data['user_id']            = auth()->user()->id;
            $data['foto_kapal']        = $image_kapal->hashName();
            $data['dokumen_perizinan']  = $dokumen_perizinan->hashName();

            $kapal = Kapal::create($data);
            DB::commit();
            return response()->json([
                'success' => true,
                'kapal'    => $kapal
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message'    => $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'kode'     => 'required',
            'nama_kapal' => 'required',
            'nama_pemilik' => 'required',
            'alamat_pemilik' => 'required',
            'ukuran_kapal' => 'required',
            'kapten' => 'required',
            'jumlah_anggota' => 'required',
            // 'foto_kapal' => 'required|mimes:jpeg,jpg,png,gif|max:10000',
            // 'nomor_izin' => 'required',
            // 'dokumen_perizinan' => 'required|mimes:jpeg,jpg,png,gif|max:10000',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        // cek kapal berdasarkan kode
        $kapal = Kapal::where('kode', $request->kode)->first();
        if (!$kapal) {
            return response()->json([
                'success' => false,
                'message'    => "Kapal Berdasarkan " . $request->kode . " Tidak ada"
            ], 404);
        }
        DB::beginTransaction();
        try {
            $image_kapal = $request->file('foto_kapal');
            if ($image_kapal) {
                $image_kapal->storeAs('public/kapal', $image_kapal->hashName());
                $data['foto_kapal']        = $image_kapal->hashName();
            }

            $dokumen_perizinan = $request->file('dokumen_perizinan');
            if ($dokumen_perizinan) {
                $dokumen_perizinan->storeAs('public/kapal', $dokumen_perizinan->hashName());
                $data['dokumen_perizinan']  = $dokumen_perizinan->hashName();
            }

            DB::commit();
            $kapal->update($data);
            return response()->json([
                'success' => true,
                'kapal'    => $kapal
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message'    => $e->getMessage()
            ], 400);
        }
    }
}
