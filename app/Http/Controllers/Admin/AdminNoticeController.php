<?php

// app/Http/Controllers/Admin/AdminNoticeController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotice;
use Illuminate\Http\Request;

class AdminNoticeController extends Controller
{
    public function index(Request $request)
    {
        $q = AdminNotice::query()
            ->when($request->filled('audiencia'), fn($qq)=>$qq->where('audiencia',$request->audiencia))
            ->when($request->filled('nivel'),     fn($qq)=>$qq->where('nivel',$request->nivel))
            ->latest();

        $items = $q->paginate(15)->withQueryString();

        return view('admin.notices.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo'        => ['required','string','max:160'],
            'mensaje'       => ['required','string'],
            'nivel'         => ['required','in:info,success,warning,danger'],
            'audiencia'     => ['required','in:todos,aspirantes,admin'],
            'visible_desde' => ['nullable','date'],
            'visible_hasta' => ['nullable','date','after_or_equal:visible_desde'],
            'activo'        => ['nullable','boolean'],
        ]);
        $data['activo']     = $request->boolean('activo', true);
        $data['created_by'] = $request->user()->id;

        AdminNotice::create($data);

        return back()->with('status','Notificación creada.');
    }

    public function update(Request $request, AdminNotice $n)
    {
        $data = $request->validate([
            'titulo'        => ['required','string','max:160'],
            'mensaje'       => ['required','string'],
            'nivel'         => ['required','in:info,success,warning,danger'],
            'audiencia'     => ['required','in:todos,aspirantes,admin'],
            'visible_desde' => ['nullable','date'],
            'visible_hasta' => ['nullable','date','after_or_equal:visible_desde'],
            'activo'        => ['nullable','boolean'],
        ]);
        $data['activo'] = $request->boolean('activo', true);

        $n->update($data);

        return back()->with('status','Notificación actualizada.');
    }

    public function destroy(AdminNotice $n)
    {
        $n->delete();
        return back()->with('status','Notificación eliminada.');
    }

    public function toggle(AdminNotice $n)
    {
        $n->activo = ! $n->activo;
        $n->save();
        return back()->with('status', $n->activo ? 'Notificación activada.' : 'Notificación desactivada.');
    }
}
