<?php
// app/Http/Controllers/Admin/NotificationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $items = AdminNotification::latest('id')->paginate(20);
        return view('admin.notifications.index', compact('items'));
    }

    public function create()
    {
        return view('admin.notifications.form', ['item'=>new AdminNotification()]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'titulo' => 'required|string|max:255',
            'mensaje'=> 'nullable|string',
            'nivel'  => 'required|string|in:info,warning,success,danger',
            'activo' => 'boolean',
            'inicio' => 'nullable|date',
            'fin'    => 'nullable|date|after_or_equal:inicio',
        ]);
        AdminNotification::create($data);
        return redirect()->route('admin.notificaciones.index')->with('ok','Creado');
    }

    public function edit(AdminNotification $notificacione)
    {
        return view('admin.notifications.form', ['item'=>$notificacione]);
    }

    public function update(Request $r, AdminNotification $notificacione)
    {
        $data = $r->validate([
            'titulo' => 'required|string|max:255',
            'mensaje'=> 'nullable|string',
            'nivel'  => 'required|string|in:info,warning,success,danger',
            'activo' => 'boolean',
            'inicio' => 'nullable|date',
            'fin'    => 'nullable|date|after_or_equal:inicio',
        ]);
        $notificacione->update($data);
        return redirect()->route('admin.notificaciones.index')->with('ok','Actualizado');
    }

    public function destroy(AdminNotification $notificacione)
    {
        $notificacione->delete();
        return back()->with('ok','Eliminado');
    }
}
