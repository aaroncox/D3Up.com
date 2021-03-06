<?php

class Build_Controller extends Base_Controller {

	public $restful = true;

	public function getBuilds($params = array()) {
		// Filtering on the Build List
		$query = array(
			'private' => false,
		);
		// How are we sorting them?
		$sort = array(
			'_created' => -1
		);
		// Do we have a class specified?
		if($class = Request::get('class')) {
			$query['class'] = $class;
		}
		// Did the user request a sort?
		if($sortBy = Request::get('sort')) {
			$sort = array('stats.'.$sortBy => -1);
			$query['stats.'.$sortBy] = array('$gt' => 0);
		}
		// Check our Params
		if(!empty($params) && isset($params['filter'])) {
			switch($params['filter']) {
				case "user":
					if($user = Auth::user()) {
						$query['_createdBy'] = $user->createReference();
						$sort = array(
							'paragon' => -1,
							'level' => -1,
						);
					}					
					break;
			}
		}
		// If we're being passed a BattleTag, search for it.
		if($battletag = strtolower(Request::get('battletag'))) {
			$query['_characterBt'] = str_replace("-", "#", $battletag);
		}
		$builds = Epic_Mongo::db('build')->find($query)->sort($sort);
		return $builds;
	}
	
	public function getPagination($builds) {
		// Build List Parameters
		$curPage = Request::get('page') ?: 1;	// Either the currently requested page, or if null, page #1
		$perPage = 20;
		$skip = ($curPage - 1) * $perPage;
		// Pagination Options
		$paginationOptions = array(
			'class' => Request::get('class'),
			'sort' => Request::get('sort'),
		);
		// If we're being passed a BattleTag, search for it.
		if($battletag = strtolower(Request::get('battletag'))) {
			$paginationOptions['battletag'] = str_replace("-", "#", $battletag);
		}
		// Add a paginator
		$pagination = Paginator::make($builds->limit($perPage)->skip($skip), $builds->count(), $perPage);
		return $pagination->appends($paginationOptions);
	}

	public function get_index() {
		return View::make('build.index');
	}
	public function get_index2() {
		// Fetch the Builds
		$builds = $this->getBuilds();
		// Fetch the Pagination
		$pagination = $this->getPagination($builds);
		// If we got a battletag, and no results, scan the API and present results
		$characters = array();	// Array to return with characters
		if($builds->count() === 0) {
			if($battletag = Request::get('battletag')) {
				if(Cache::has('apicache-'.$battletag)) {
					$characters = Cache::get('apicache-'.$battletag);
				} else {
					$sync = new D3Up_Sync();
					foreach(array(1 => 'US', 2 => 'EU', 3 => 'AS') as $key => $region) {
						$characters[$key] = $sync->getCharacters($key, $battletag);
					} 
					// Cache::put('apicache-'.$battletag, $characters, 5);				
				}
			}			
		}
		return View::make('build.index')
						->with('builds', $builds)
						->with('pagination', $pagination)
						->with('characters', $characters);
	}

	public function get_view($id, $data = false) {
		$build = Epic_Mongo::db('build')->findOne(array('id' => (int) $id));
		if(!$build) {
			return Response::error('404');
		}
		// Possible Caching Mechanism, found in the Bundle D3Up-Cache
		// return View::cached('build.view', array('build' => $build));
		return View::make('build.view')->with('build', $build);
	}
	
	public function post_view($id, $data = false) {
		$build = Epic_Mongo::db('build')->findOne(array('id' => (int) $id));
		if(!$build) {
			return Response::error('404');
		}
		$user = Auth::user();
		if(!$user || $user->id != $build->_createdBy->id) {
			return Response::error('404');
		}
		$input = Input::all();
		$rules = array(
			'name' => 'required|between:2,70',
	    'class' => 'required|in:barbarian,demon-hunter,monk,wizard,witch-doctor',
			'gender' => 'required|min:0|max:1',
			'level' => 'min:1|max:60',
			'paragon' => 'min:0|max:100',
		);
		$validation = Validator::make($input, $rules);
		if ($validation->fails()) {
			return Redirect::to('/b/'.$id.'#tab-edit')->with_errors($validation)->with_input();
		}
		// We've now modified this build.. it's no longer authentic to battle.net
		//		Set this to null to avoid entry into the sparse index.
		$build->_authentic = null;
		// Set the Fields based on the Input
		$build->name = $input['name'];
		$build->class = $input['class'];
		$build->gender = (int) $input['gender'];
		$build->level = (int) $input['level'];
		$build->paragon = (int) $input['paragon'];
		// Is this build Hardcore? True or False
		$build->hardcore = isset($input['hardcore']) ? true : false;
		// Should this build be public? By default, builds are public. 
		// 		If we aren't public, set this field to null to avoid indexing
		$build->public =	isset($input['public']) ? true : null;
		$build->save();
		return Redirect::to('/b/'.$id);
	}
	
	public function get_compare($id1, $id2) {
		$build1 = Epic_Mongo::db('build')->findOne(array('id' => (int) $id1));
		$build2 = Epic_Mongo::db('build')->findOne(array('id' => (int) $id2));
		if(!$build1 || !$build2) {
			return Response::error('404');
		}
		return View::make('build.compare')->with('build1', $build1)->with('build2', $build2);
	}
	
	public function get_sync($id, $data = false) {
		// Get the Build
		$build = Epic_Mongo::db('build')->findOne(array('id' => (int) $id));
		if(!$build) {
			return Response::error('404');
		}
		// Sync the Build and get the results
		$results = $build->sync(Request::get('type'));
		// Return the Results and Build to the View
		return View::make('build.sync')->with('build', $build)->with('results', $results);
	}
	
	public function get_create() {
		// Are we requesting characters?
		$bt = Input::get('battletag');
		$rg = Input::get('region');
		if($bt && $rg && Request::ajax()) {
			$sync = new D3Up_Sync();
			return Response::json($sync->getCharacters($rg, $bt));
		}
		// Are we logged in with characters?
		$characters = array();
		if($user = Auth::user()) {
			if($user->region && $user->battletag) {
				$sync = new D3Up_Sync();
				$characters = $sync->getCharacters($user->region, $user->battletag);
			}
		}
		// Return the View
		return View::make('build.create')
							 ->with('characters', $characters);
	}
	
	public function post_create() {
		// Create a new Build
		$build = Epic_Mongo::db('doc:build');
		// Set the Custom Options passed in the Form
		$build->name = Input::get('name');
		$build->class = Input::get('class');
		$build->level = (int) Input::get('level');
		$build->paragon = (int) Input::get('paragon');
		$build->_created = time();
		// Save this data on the Build
		$build->save();
		// Are we sync'ing this build?
		if(Input::get('character-id') && Input::get('character-bt') && Input::get('character-rg')) {
			// Set the Battle.net Information on the Build
			$build->_characterRg = Input::get('character-rg');
			$build->_characterBt = strtolower(str_replace("-","#", Input::get('character-bt')));	// Ensure it's always lower and has # instead of - in it
			$build->_characterId = Input::get('character-id');
			// Sync the Data from Battle.net
			$results = $build->sync();
			// Return to the Success view
			return Redirect::to_action('build@success')
													->with('build', $build->id)
													->with('results', $results);
			// return View::make('build.sync')->with('build', $build)->with('results', $results);
		}
		// Redirect to the build
		return Redirect::to_action('build@view', array('id' => $build->id));
	}
	
	public function get_success() {
		if(!Session::has('build') || !Session::has('results')) {
			throw new Exception("You've reached this page in error.");
		}
		$build = Epic_Mongo::db('build')->findOne(array('id' => (int) Session::get('build')));
		$results = Session::get('results');
		return View::make('build.sync')->with('build', $build)->with('results', $results);
	}
	
	public function get_user() {
		// Fetch the Builds
		$builds = $this->getBuilds(array('filter' => 'user'));
		// Fetch the Pagination
		$pagination = $this->getPagination($builds);
		return View::make('build.index')
						->with('builds', $builds)
						->with('pagination', $pagination);
	}
	
	public function makeFloats($array) {
		$data = array();
		foreach($array as $k => $v) {
			if(is_array($v)) {
				$data[$k] = $this->makeFloats($v);
			} else if(is_numeric($v)) {
				$data[$k] = (float) $v;
			} else {
				$data[$k] = $v;
			}
		}
		return $data;
	}
	
	public function post_cache($id) {
		// Get the Stats from the AJAX Request (Generated via the Calculator)
		$toCache = Request::get('stats');
		// Unset the Skill Data, we don't need this
		unset($toCache['skillData']);
		// Load the build up
		$build = Epic_Mongo::db('build')->findOne(array('id' => (int) $id));
		// 404 if no build is found
		if(!$build) {
			return Response::error('404');
		}
		// Does this build have an owner or a syncKey?
		if($build->_createdBy || $build->_syncKey) {
			// If we have a sync key, is it the right one?
			if($key = Request::get('syncKey')) {
				if($key == $build->_syncKey) {
					// Remove the SyncKey
					$build->_syncKey = null;
					// Set the Stats array on the build equal to the data from the calculator
					$build->stats = $this->makeFloats($toCache);
					// Save it
					$build->save();				
				}
			}
			// If so, is our user logged in the owner?
			if(Auth::check() && Auth::user()->id === $build->_createdBy->id) {
				// Set the Stats array on the build equal to the data from the calculator
				$build->stats = $this->makeFloats($toCache);
				// Save it
				$build->save();				
			}
		}
	}
	
	public function get_signature($id) {
		$build = Epic_Mongo::db('build')->findOne(array('id' => (int) $id));
		if(!$build) {
			return Response::error('404');
		}

		return View::make('build.signature')->with('build', $build);
	}
	
	public function post_signature($id) {
		$build = Epic_Mongo::db('build')->findOne(array('id' => (int) $id));
		if(!Auth::user()) {
			throw new Exception("You must be logged in to regenerate your signatures.");			
		}
		if(!$build->_createdBy) {
			throw new Exception("Anonymous Builds (Builds created by non-registered users) are not allowed to have signatures");
		}
		if($build->_createdBy->id !== Auth::user()->id) {
			throw new Exception("You do not own this build, and are not allowed to regenerate this signature.");
		}
		if(!$build) {
			return Response::error('404');
		}
		$signature = new D3Up_Signature($build);		
		return Redirect::to('/b/'.$build->id.'/signature');
	}
}