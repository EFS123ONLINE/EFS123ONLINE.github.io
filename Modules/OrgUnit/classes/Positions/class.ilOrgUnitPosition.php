<?php

// namespace ILIAS\Modules\OrgUnit\Positions;

// use ILIAS\Modules\OrgUnit\Positions\Authorities\Authority;

/**
 * Class ilOrgUnitPosition
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPosition extends \ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return "il_orgu_positions";
	}


	/**
	 * Override for correct on return value
	 *
	 * @return \ilOrgUnitPosition[]
	 */
	public static function get() {
		return parent::get();
	}


	/**
	 * @throws \ilException whenever you try to delete a core-position like employee or superior
	 */
	public function delete() {
		if ($this->isCorePosition()) {
			throw new ilException('Cannot delete Core-Position');
		}
		parent::delete();
	}


	/**
	 * @param int $orgu_ref_id
	 *
	 * @return \ilOrgUnitPosition[] array of Positions (all core-positions and all positions which
	 *                              have already UserAssignements at this place
	 */
	public static function getActiveForPosition($orgu_ref_id) {
		arObjectCache::flush(self::class);
		$q = "SELECT il_orgu_positions.*
 				FROM il_orgu_positions 
 				LEFT JOIN il_orgu_ua ON il_orgu_positions.id = il_orgu_ua.position_id AND il_orgu_ua.orgu_id = %s 
 				WHERE il_orgu_ua.user_id IS NOT NULL 
 					OR core_position = 1";
		$database = $GLOBALS['DIC']->database();
		$st = $database->queryF($q, array( 'integer' ), array( $orgu_ref_id ));

		$positions = array();

		while ($data = $database->fetchAssoc($st)) {
			$position = new self();
			$position->buildFromArray($data);
			$positions[] = $position;
		}

		return $positions;
	}


	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_sequence   true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $id = 0;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     512
	 */
	protected $title = "";
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     4000
	 */
	protected $description = "";
	/**
	 * @var bool
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $core_position = false;
	/**
	 * @var \ilOrgUnitAuthority[]
	 */
	protected $authorities = array();


	public function afterObjectLoad() {
		$this->authorities = ilOrgUnitAuthority::where(array( ilOrgUnitAuthority::POSITION_ID => $this->getId() ))
		                                       ->get();
	}


	/**
	 * @return array
	 */
	public function getAuthoritiesAsArray() {
		$return = array();
		foreach ($this->authorities as $authority) {
			$return[] = $authority->__toArray();
		}

		return $return;
	}


	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getTitle();
	}


	/**
	 * @return array  it's own authorities and also all which use this position
	 */
	public function getDependentAuthorities() {
		$dependent = ilOrgUnitAuthority::where(array( ilOrgUnitAuthority::FIELD_OVER => $this->getId() ))
		                               ->get();

		$arr = $dependent + $this->authorities;

		return (array)$arr;
	}


	/**
	 * This deletes the Position, it's Authorities, dependent Authorities and all User-Assignements!
	 */
	public function deleteWithAllDependencies() {
		foreach ($this->getDependentAuthorities() as $authority) {
			$authority->delete();
		}
		parent::delete();
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return bool
	 */
	public function isCorePosition() {
		return $this->core_position;
	}


	/**
	 * @param bool $core_position
	 */
	public function setCorePosition($core_position) {
		$this->core_position = $core_position;
	}


	/**
	 * @return \ilOrgUnitAuthority[]
	 */
	public function getAuthorities() {
		return $this->authorities;
	}


	/**
	 * @param \ilOrgUnitAuthority[] $authorities
	 */
	public function setAuthorities($authorities) {
		$this->authorities = $authorities;
	}
}