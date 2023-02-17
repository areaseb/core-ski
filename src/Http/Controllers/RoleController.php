<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\{Permission, Role};
use App\User;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name', 'ASC')->get();
        return view('areaseb::core.roles.index', compact('roles'));
    }

    public function create()
    {
        $allPermissions = Permission::all();
        $permissions = [];
        foreach ($allPermissions as $permission)
        {
            $arr = explode('.',$permission->name);
            $permissions[$arr[0]][] = [
                'id' => $permission->id,
                'action' => $arr[1]
            ];
        }
        return view('areaseb::core.roles.create', compact('permissions'));
    }

    public function store()
    {
        $this->validate(request(), [
            'name' => 'required',
            'permission_id' => 'present|array'
        ]);

        $role = Role::create(['name' => request('name')]);
        $permissions = Permission::whereIn('id', request('permission_id'))->get();
        foreach ($permissions as $permission)
        {
            $role->givePermissionTo($permission);
        }

        return redirect(route('roles.index'))->with('message', 'Ruolo creato');
    }

    public function edit(Role $role)
    {
        $allPermissions = Permission::all();
        $permissions = [];
        foreach ($allPermissions as $permission)
        {
            $arr = explode('.',$permission->name);
            $permissions[$arr[0]][] = [
                'id' => $permission->id,
                'action' => $arr[1]
            ];
        }

        return view('areaseb::core.roles.edit', compact('role', 'permissions'));
    }

    public function update(Role $role)
    {
        $this->validate(request(), [
            'name' => 'required',
            'permission_id' => 'present|array'
        ]);

        $role->name = request('name');
        $role->save();

        $permissions = Permission::whereIn('id', request('permission_id'))->pluck('name')->toArray();

        $role->syncPermissions($permissions);

        return redirect(route('roles.index'))->with('message', 'Ruolo aggiornato');
    }

    public function destroy(Role $role)
    {
        foreach(User::role($role->name)->get() as $user)
        {
            $user->removeRole($role->name);
        }
        foreach($role->permissions as $permission)
        {
            $role->revokePermissionTo($permission->name);
        }
        $role->delete();

        return redirect(route('roles.index'))->with('message', 'Ruolo eliminato');
    }

}
