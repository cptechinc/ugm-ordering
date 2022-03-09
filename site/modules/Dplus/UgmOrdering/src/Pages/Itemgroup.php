<?php namespace Dplus\UgmOrdering\Pages;
// Propel ORM Library
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Models
use InvGroupCodeQuery, InvGroupCode;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\Page;


class Itemgroup extends Base {
	const TEMPLATE  = 'item-group';
	const MODEL     = 'InvGroupCode';
	const MODEL_KEY = 'code';
	const PAGE_KEY  = 'groupcode';

	protected static $instance;

/* =============================================================
	Selectors
============================================================= */
	/**
	 * Return Selector for Item group pages
	 * @param  string $id Item Group ID
	 * @return string
	 */
	public function selectorGroupid($id) {
		$template = static::TEMPLATE;
		return "template=$template, groupcode=$id";
	}

/* =============================================================
	Dplus Database Functions
============================================================= */
	/**
	 * Return Item Groups
	 * @return InvGroupCode[]
	 */
	public function getInvItemgroups() {
		return InvGroupCodeQuery::create()->find();
	}

/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Returns if Item Group Exists
	 * @param  InvGroupCode|string $group   Item Group | Code
	 * @return bool
	 */
	public function pageExists($group) {
		$itemgroup = is_object($group) ? $group->code : $group;
		$template = self::TEMPLATE;

		$p = $this->wire('pages')->get($this->selectorGroupid($itemgroup));
		return boolval($p->id);
	}

	/**
	 * Returns Item Group Page
	 * @param  InvGroupCode|string $group   Item Group | Code
	 * @return Page
	 */
	public function getPage($group) {
		$itemgroup = is_object($group) ? $group->code : $group;
		$template = self::TEMPLATE;
		return $this->wire('pages')->get($this->selectorGroupid($itemgroup));
	}

/* =============================================================
	CRUD Batch
============================================================= */
	/**
	 * Create / Update Item Group Pages
	 * @return array
	 */
	public function updateAll() {
		$this->turnAllPagesInvisible();
		$groups = $this->getInvItemgroups();
		$updated = [];

		foreach ($groups as $group) {
			if ($this->pageExists($group)) {
				$updated[$group->code] = $this->updatePage($group);
			} else {
				$updated[$group->code] = $this->createPage($group);
			}

			if ($this->pageExists($group)) {
				$this->updatePageVisibility($group, true);
			}
		}
		return $updated;
	}

/* =============================================================
	CRUD Creates, Updates
============================================================= */
	/**
	 * Creates Item Group Page
	 * @param  InvGroupCode $group Item Group
	 * @return bool
	 */
	public function createPage(ActiveRecordInterface $group) {
		$parent = $this->wire('pages')->get('template=items-menu');
		$p = new Page();
		$p->of(false);
		$p->parent    = $parent;
		$p->template  = self::TEMPLATE;
		$p->name      = $this->wire('sanitizer')->pageName($group->code);
		$p->title     = $group->description;
		$p->groupcode = $group->code;
		$saved = $p->save();

		if ($saved) {
			return $this->updatePage($group);
		} else {
			return $saved;
		}
	}

	/**
	 * Updates Item Group Page
	 * @param  InvGroupCode $group Item Group
	 * @return bool
	 */
	public function updatePage(ActiveRecordInterface $group) {
		$p = $this->getPage($group);
		$p->of(false);
		$p->name      = $this->wire('sanitizer')->pageName($group->code);
		$p->title     = $group->description;
		$p->groupcode = $group->code;
		return $p->save();
	}
}
