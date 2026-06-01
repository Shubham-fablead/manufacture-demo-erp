<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function lead_list(Request $request)
    {
        return view('lead.leadlist');
    }

    public function add_lead(Request $request)
    {
        return view('lead.addlead');
    }

    public function edit_lead(Request $request, $id)
    {
        return view('lead.editlead', ['id' => $id]);
    }

    public function view_lead(Request $request, $id)
    {
        return view('lead.viewlead', ['id' => $id]);
    }
}
