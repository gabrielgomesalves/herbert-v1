<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Redirect;
use DB;

use App\Serie;
use App\Glossary;

class GlossaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$series = Serie::orderBy('original_name', 'asc')->where('current_state', false)->get();
		$glossaries = array();
        return view('glossary.index', ['series' => $series, 'glossaries' => $glossaries]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
	
	
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$glossary = new Glossary;
		$glossary->serie_id = $request->input('serie_id');
		$glossary->word = $request->input('word');
		$glossary->pronounce = $request->input('pronounce');
		$glossary->save();

		$series = Serie::orderBy('original_name', 'asc')->where('current_state', false)->get();
		$glossaries = Glossary::get();
		return Redirect::back()->with(['series' => $series, 'glossaries' => $glossaries]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
	
	public function getGlossary(Request $request){
		$serie_id = $request->input('serie_id');
		$glossaries = Glossary::where("serie_id", "=", $serie_id)->get();
		return json_encode($glossaries);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
	
	public function delete(Request $request)
	{
		DB::beginTransaction();
		try {
			$glossary_id = $request->input('glossary_id');
			$glossary = Glossary::find($glossary_id);
			$glossary->delete();
			DB::commit();
		}
		catch(\Exception $ex)
		{
			DB::rollback();
			return response()->json(['error_msg' => $ex->getMessage()], 400);
		}
		return response()->json('success', 200);
	}
}
