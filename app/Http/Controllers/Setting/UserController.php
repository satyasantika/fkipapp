<?php

namespace App\Http\Controllers\Setting;

use App\Models\User;
use App\Models\Departement;
use Illuminate\Http\Request;
use App\DataTables\UsersDataTable;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('layouts.setting');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = new User();
        return view('forms.user',array_merge(
            [ 'user' => $user ],
            $this->_dataSelection(),
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $name = strtoupper($request->name);
        $data = $request->merge([
            'password'=> bcrypt($request->password),
        ]);
        User::create($data->all());
        return to_route('users.index')->with('success','user '.$name.' telah ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('forms.user',array_merge(
            [ 'user' => $user ],
            $this->_dataSelection(),
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $name = strtoupper($user->name);
        $data = $request->all();
        $user->fill($data)->save();

        return to_route('users.index')->with('success','user '.$name.' telah diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $name = strtoupper($user->name);
        $user->delete();
        return to_route('users.index')->with('success','user '.$name.' telah dihapus');
    }

    private function _dataSelection()
    {
        return [
            // 'roles' =>  Role::all()->pluck('name')->sort(),
            'departements' =>  Departement::all()->sort(),
        ];
    }

}
