<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Bet;
use App\Models\Game;
use App\Models\User;
use App\Http\Requests;
use App\Models\Outcome;
use App\Models\Business;
use Illuminate\Http\Request;

class BettingController extends Controller
{
    public function index(){
    	return view('betting.index')->with('games', Game::with('outcomes')->get());
    }

    public function show($id){
    	if($game = Game::with('outcomes')->find($id)){
    		$outcomes = Outcome::select('outcome_name', 'total_volume_pending')->where('game_id', $id)->get();
    		return view('betting.show')->with(compact('game', 'outcomes'));
    	}
    	return redirect('/');
    }

    public function store(Request $request){
    	$this->validate($request, [
    		"outcome_name" => "required|string",
    		"odds" => "required",
    		"amount" => "required|numeric|min:1|max:".Auth::user()->account_balance,
    	]);

    	$bet = Bet::createBet($request);
        Business::updateBankBalance($bet->stake);
    	Outcome::updateOutcomes($bet);
    	Bet::matchBets($bet);
        User::updateAccountBalance($bet);

    	return redirect('/betting/'.$request->gameId);
    }
}
