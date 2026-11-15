<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;

use App\Episode;
use App\Song;

class SongController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }
	
	public function songsExists(Request $request)
	{
		$episode_id = $request->input('episode_id');
		if(Song::where('episode_id', $episode_id)->exists()){
			return response()->json(['songsExists' => 1]); 
		} else {
			return response()->json(['songsExists' => 0]);
		}
	}
	
	public function getSongs(Request $request)
    {
        $episode_id = $request->input('episode_id');
		$episode = Episode::find($episode_id);
		$songs = '';
		if(Song::where('episode_id', $episode_id)->exists()){
			$songs = Song::where('episode_id', $episode_id)->get();
			return response()->json(['songs' => $songs, 'episode' => $episode]);
		}
		return response()->json(['songs' => $songs, 'episode' => $episode]);
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
		$song_id = $request->input('song_id');
        $song_lyric = $request->input('song_lyric');
		$song = Song::find($song_id);
		$song->lyrics = $song_lyric;
		$song->save();
		return response()->json(['song_lyric' => $song_lyric]);
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
}
