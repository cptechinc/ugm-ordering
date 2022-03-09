<?php namespace Dplus\UgmOrdering\Pages;
// Propel ORM Library
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\Page;


abstract class Base extends WireData {
	const TEMPLATE  = '';
	const MODEL     = '';
	const MODEL_KEY = '';
	const PAGE_KEY  = '';

	protected static $instance;

	/**
	 * Return Instance
	 * @return static
	 */
	public static function instance() {
		if (empty(static::$instance)) {
			$instance = new static();
			static::$instance = $instance;
		}
		return static::$instance;
	}

/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Returns if Model Record Exists
	 * @param  ActiveRecordInterface|string $record   Model Record | Record Key
	 * @return bool
	 */
	abstract public function pageExists($record);

	/**
	 * Returns Model Record Page
	 * @param  ActiveRecordInterface|string $record   Model Record | Record Key
	 * @return Page
	 */
	abstract public function getPage($record);


/* =============================================================
	CRUD Batch
============================================================= */
	/**
	 * Create / Update Model Record Pages
	 * @return array
	 */
	abstract public function updateAll();

	/**
	 * Toggle Invsibility for all Model Record Pages
	 * @return void
	 */
	public function turnAllPagesInvisible() {
		$key      = static::PAGE_KEY;
		$template = static::TEMPLATE;
		$pages = $this->wire('pages')->find("template=$template");

		foreach ($pages as $p) {
			$this->updatePageVisibility($p->$key, $visible = false);
		}
	}


/* =============================================================
	CRUD Creates, Updates
============================================================= */
	/**
	 * Creates Model Record Page
	 * @param  ActiveRecordInterface $record Model Record
	 * @return bool
	 */
	abstract public function createPage(ActiveRecordInterface $record);

	/**
	 * Updates Model Record Page
	 * @param  ActiveRecordInterface $record Model Record
	 * @return bool
	 */
	abstract public function updatePage(ActiveRecordInterface $record);

	/**
	 * Sets visibility for Model Record Page
	 * @param  ActiveRecordInterface|string $record   Model Record | Record Key
	 * @param  bool                $visible
	 * @return void
	 */
	public function updatePageVisibility($record, bool $visible) {
		$p = $this->getPage($record);
		$p->of(false);

		if ($visible) {
			$p->status(['hidden' => false, 'unpublished' => false]);
			return $p->save();
		}
		$p->addStatus(Page::statusHidden);
		return $p->save();
	}
}
