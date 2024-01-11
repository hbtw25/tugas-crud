<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\pegawai;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $image = DB::table('pegawais')->where('foto')->get();
        $pegawai = pegawai::where([
            ['nama_pegawai', '!=', Null],
            [function ($query) use ($request) {
                if (($s = $request->s)) {
                    $query->orWhere('nama_pegawai', 'LIKE', '%' . $s . '%')
                        ->orWhere('nip', 'LIKE', '%' . $s . '%')
                        ->get();
                }
            }]
        ])->paginate(6);

        return view('pegawai.index', compact('pegawai','image'));
    }

    public function create()
    {
        return view('pegawai.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required',
            'nama_pegawai' => 'required',
            'masa_kerja' => 'required',
            'jenis_kelamin' => 'required',
            'alamat' => 'required',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);


        if($request->has('foto')){

            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension();

            $filename = time().'.'.$extension;

            $path = 'uploads/category/';
            $file->move($path, $filename);
        }
        pegawai::create([
            'nip' => $request->nip,
            'nama_pegawai' => $request->nama_pegawai,
            'masa_kerja' => $request->masa_kerja,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
            'foto' => $path.$filename

        ]);

        return redirect()->route('pegawai.index')
            ->with('success', 'pegawai Created Successfully.');
    }

    public function show(pegawai $pegawai)
    {
        return view('pegawai.show', compact('pegawai'));
    }

    public function edit(pegawai $pegawai, )
    {


        return view('pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, pegawai $pegawai, int $id)
    {
        $request->validate([
            'nip' => 'required',
            'nama_pegawai' => 'required',
            'masa_kerja' => 'required',
            'jenis_kelamin' => 'required',
            'alamat' => 'required',
            'foto' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $category = Pegawai::findOrFail($id);
        if($request->has('image')){

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();

            $filename = time().'.'.$extension;

            $path = 'uploads/category/';
            $file->move($path, $filename);

            if(File::exists($category->image)){
                File::delete($category->image);
            }
        }
        // Update the employee data
        $pegawai->update([
            'nip' => $request->nip,
            'nama_pegawai' => $request->nama_pegawai,
            'masa_kerja' => $request->masa_kerja,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
            'foto' => $path.$filename
        ]);

        return redirect()->route('pegawai.index')
            ->with('success', 'Pegawai updated successfully.');
    }



    public function destroy(pegawai $pegawai, int $id)
    {
        $category = pegawai::findOrFail($id);
        if(File::exists($category->image)){
            File::delete($category->image);
        }
        $category->delete();
        $pegawai->delete();

        return redirect()->route('pegawai.index')
            ->with('success', 'pegawai Deleted Successfully.');
    }
}
