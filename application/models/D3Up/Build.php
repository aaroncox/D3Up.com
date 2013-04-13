<?php

class D3Up_Build extends D3Up_Mongo_Document_Sequenced {
	
	protected $_collection = 'builds';
	protected $_sequenceKey = 'build';
	
  protected $_requirements = array(
		'gear' => array('doc:gearset'),
		'_gear' => array('doc:gearsetcache', 'auto'),
		'_createdBy' => array('doc:user', 'ref'),
		'_original' => array('doc:build', 'ref'),
  );

	// These fields will be renamed/removed, null = remove, rename = string
	protected $_fieldMod = array(
		// Internal Flags / Timestamps
		'_id' => null, 
		'_created' => null, 
		'_lastCrawl' => null,
		'crawlCount' => null,
		'_type' => null,
		'private' => null,
		'_createdBy' => null,
		'views' => null,
		// Twitch Information
		'_twitchEnabled' => null,
		'_twitchLastCheck' => null,
		'_twitchOnline' => null,
		// Character BattleNet Info
		'_characterBt' => 'battletag',
		'_characterId' => null,
		'_characterRg' => null,
		// Old Description Fields
		'description' => null,
		'descriptionSource' => null,
		'_defaultToDescription' => null, 
		// The characters gear/stats, we don't need it here
		'gear' => null,
		'_gear' => null,
		'_original' => null,
		'equipment' => null,
		'equipmentCount' => null,
		'stats' => null,
		// Since JS reserves class, we use heroClass in JS
		'class' => 'heroClass',
		// This can be removed safely 
		'test' => null,
	);

	public function sync($type = null) {
		$tool = new D3Up_Sync();
		return $tool->run($this, $type);
	}
	
	public function json() {
		$data = $this->export();
		foreach($this->_fieldMod as $field => $mod) {
			if($mod && isset($data[$field])) {
				$data[$mod] = $data[$field];
			}
			unset($data[$field]);				
		}
		return json_encode($data);
	}
	
	public function getGear() {
		// If our cached gearset is a brand new document, skip over it
		if(!$this->_gear->isNewDocument()) {
			// If it's not a new document, return the cached version
			return $this->_gear;
		}
		// And use our normal gear
		return $this->gear;
	}
	
	// public function save($whole = true) {
	// 	if(Request::cli()) {
	// 		return parent::save();
	// 	}
	// 	throw new Exception("Saving is currently disabled.");
	// 	return parent::save($whole);
	// }
}