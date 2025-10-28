<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ScheduleExport;


class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cinemas = Cinema::all();
        $movies = Movie::all();

        //karena cinema _id dan movie_id itu foreign key, maka kita harus mengambil data dari tabel cinema dan movie
        $schedules = Schedule::with(['cinema', 'movie'])->get();

        return view('staff.schedule.index', compact('cinemas', 'movies', 'schedules'));
    }


   public function datatables()
{
    $schedules = Schedule::with(['cinema', 'movie'])->get();

    $collection = $schedules->map(function ($schedule) {
        $hours = is_array($schedule->hours)
            ? $schedule->hours
            : json_decode($schedule->hours, true);

        return [
            'id' => $schedule->id,
            'cinema_name' => $schedule->cinema->name ?? '-',
            'movie_title' => $schedule->movie->title ?? '-',
            'hours' => $hours ?? [], // kalau null tetap array kosong
        ];
    });

    return datatables()->of($collection)
        ->addIndexColumn()
        ->addColumn('hours', function ($schedule) {
            $hours = is_array($schedule['hours'])
                ? $schedule['hours']
                : json_decode($schedule['hours'], true);

            if (empty($hours)) {
                return '<em class="text-muted">Tidak ada jadwal</em>';
            }

            $list = '<ul class="list-unstyled mb-0">';
            foreach ($hours as $hour) {
                $list .= '<li>' . e($hour) . '</li>';
            }
            $list .= '</ul>';
            return $list;
        })
        ->addColumn('btnActions', function ($schedule) {
            $btnEdit = '<a href="' . route('staff.schedules.edit', $schedule['id']) . '" class="btn btn-sm btn-secondary">Edit</a>';
            $btnDelete = '
                <form action="' . route('staff.schedules.delete', $schedule['id']) . '" method="POST" style="display:inline-block;">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Yakin ingin hapus data ini?\')">Hapus</button>
                </form>
            ';

            return '<div class="d-flex gap-2">' . $btnEdit . $btnDelete . '</div>';
        })
        ->rawColumns(['hours', 'btnActions'])
        ->make(true);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cinema_id' => 'required',
            'movie_id' => 'required',
            'hours.*' => 'required|date_format:H:i',
            'price' => 'required|numeric',

        ], [
            'cinema_id.required' => 'Bioskop wajib diisi',
            'movie_id.required' => 'film wajib diisi',
            "price.required" => 'harga wajib diisi',
            "price.numeric" => 'harga wajib berupa angka',
            "hours.*.required" => 'jam wajib diisi minimal 1 data',
            "hours.*.date_format" => 'jam wajib berformat jam:menit',

        ]);
        // pengecekean apakah ada bioskop dan film yang di pilih sekarang di db nya kelau ada ambil jam nya
        $hours = Schedule::where('cinema_id', $request->cinema_id)->where('movie_id', $request->movie_id)->value('hours');
        // jika sudah ada data dengan bioskop dan film yang sama maka ambil data jam tersebut, jika tidak buat array kosong
        $hoursBefore = $hours ?? [];
        // gabungkan array jam sebelumnya dengan array jam yang baru ditambahkan
        $mergeHours = array_merge($hoursBefore, $request->hours);
        // jika ada jam duplikat ambil salah satu
        // gunakan data ini untuk disimpan di db
        $newHours = array_unique($mergeHours);

        // update or create : mengubah jika sudah ada, menambahkan hika belum ada
        $createData = Schedule::updateOrCreate([
            // arrray pertama acuan pencarian data
            'cinema_id' => $request->cinema_id,
            'movie_id' => $request->movie_id,
        ], [
            // array kedua data yang akan di simpan
            'price' => $request->price,
            'hours' => $newHours,
        ]);

        if ($createData) {
            return redirect()->route('staff.schedules.index')->with('success', 'Jadwal berhasil ditambahkan');
        } else {
            return redirect()->route('staff.schedules.index')->with('error', 'Jadwal gagal ditambahkan');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule, $id)
    {
        $schedule = Schedule::where('id', $id)->with(['cinema', 'movie'])->first();
        return view('staff.schedule.edit', compact('schedule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule, $id)
    {
        $request->validate([
            'price' => 'required|numeric',
            'hours.*' => 'required|date_format:H:i'
        ], [
            'price.required' => 'harga wajib diisi',
            'price.numeric' => 'harga harus diisi berupa angka',
            'hours.*.required' => 'jam tayang harus diisi',
            'hours.*.date_format' => 'Jam tayang harus diisi dengan format jam:menit',
        ]);

        $updateData = Schedule::where('id', $id)->update([
            'price' => $request->price,
            'hours' => $request->hours,
        ]);

        if ($updateData) {
            return redirect()->route('staff.schedules.index')->with('success', 'Berhasil mengubah data');
        } else {
            return redirect()->back()->with('error', 'Gagal coba lagi');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule, $id)
    {
        Schedule::where('id', $id)->delete();
        return redirect()->route('staff.schedules.index')->with('success', 'Berhasil menghapus data');
    }

    public function trash()
    {
        $scheduleTrash = Schedule::with(['cinema', 'movie'])->onlyTrashed()->get();
        return view('staff.schedule.trash', compact('scheduleTrash'));
    }

    public function restore($id)
    {
        $schedule = Schedule::onlyTrashed()->find($id);
        $schedule->restore();
        return redirect()->route('staff.schedules.index')->with('success', 'Berhasil mengembalikan data');
    }

    public function deletePermanent($id)
    {
        $schedule = Schedule::onlyTrashed()->find($id);
        $schedule->forceDelete();
        return redirect()->back()->with('success', 'Berhasil menghapus data secara permanen');
    }

    public function exportExcel()
    {
        $fileName = 'data-jadwal-tayang.xlsx';
        return Excel::download(new ScheduleExport, $fileName);
    }
}
