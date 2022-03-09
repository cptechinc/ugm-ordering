<?php namespace Dplus\UgmOrdering\Pages;
// Propel ORM Library
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Models
use ItemMasterItemQuery, ItemMasterItem;
use WarehouseInventoryQuery, WarehouseInventory;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\Page;


class Item extends Base {
	const TEMPLATE  = 'item';
	const MODEL     = 'ItemMasterItem';
	const MODEL_KEY = 'itemid';
	const PAGE_KEY  = 'itemid';

	protected static $instance;


/* =============================================================
	Selectors
============================================================= */
	/**
	 * Return Selector for Item pages
	 * @param  string $itemID Item ID
	 * @return string
	 */
	public function selectorItemid($itemID) {
		$template = static::TEMPLATE;
		return "template=$template, itemid=$itemID, include=all";
	}

	/**
	 * Return Selector for Item IDs not Equal in Array
	 * @param  array  $itemIDs Item ID
	 * @return string
	 */
	public function selectorNotItemids(array $itemIDs) {
		$ids      = $this->wire('sanitizer')->selectorValue(implode(',', $itemIDs));
		$template = static::TEMPLATE;
		return "template=$template, itemid!=$ids, include=all";
	}

	/**
	 * Return Selector for All Item pages
	 * @return string
	 */
	public function selectorAll() {
		$template = static::TEMPLATE;
		return "template=$template, include=all";
	}

/* =============================================================
	Dplus Database Functions
============================================================= */
	/**
	 * Return all Items
	 * @return ItemMasterItem[]
	 */
	public function getItems() {
		return ItemMasterItemQuery::create()->find();
	}

	/**
	 * Return All item IDs
	 * @return array
	 */
	public function getItemids() {
		$q = ItemMasterItemQuery::create();
		$q->select(ItemMasterItem::aliasproperty('itemid'));
		return $q->find()->toArray();
	}

	/**
	 * Return all Items filtered by Items that Are Active
	 * @return ItemMasterItem[]
	 */
	public function getActiveItems() {
		$itemIDs = $this->getActiveItemids();
		$q = ItemMasterItemQuery::create();
		$q->filterByItemid($itemIDs);
		return $q->find();
	}

	/**
	 * Return item IDs that are active
	 * @return array
	 */
	public function getActiveItemids() {
		$q = WarehouseInventoryQuery::create();
		$q->select(WarehouseInventory::get_aliasproperty('itemid'));
		$q->filterByStatus(WarehouseInventory::STATUS_ACTIVE);
		return $q->find()->toArray();
	}

	/**
	 * Return item IDs that are active, and don't exist as a page
	 * @return array
	 */
	public function getActiveItemidWithoutPages() {
		$q = WarehouseInventoryQuery::create();
		$q->select(WarehouseInventory::get_aliasproperty('itemid'));
		$q->filterByStatus(WarehouseInventory::STATUS_ACTIVE);
		$q->filterByItemid($this->wire('pages')->find('template=item')->explode('itemid'), Criteria::NOT_IN);
		return $q->find()->toArray();
	}

	/**
	 * Return all Items filtered by Items that Are Active
	 * @return ItemMasterItem[]
	 */
	public function getActiveItemsWithoutPages() {
		$itemIDs = $this->getActiveItemidWithoutPages();
		$q = ItemMasterItemQuery::create();
		$q->filterByItemid($itemIDs);
		return $q->find();
	}

/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Returns if Item Group Exists
	 * @param  ItemMasterItem|string $item   Item | Item ID
	 * @return bool
	 */
	public function pageExists($item) {
		$itemID = is_object($item) ? $item->itemid : $item;
		$exists = $this->itemidPageExists($itemID);

		if ($exists === false) {
			$itemID = $this->wire('sanitizer')->pageName($itemID);
			return $this->itemidPageExists($itemID);
		}
		return $exists;
	}

	/**
	 * Return if Item ID Page Exists
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function itemidPageExists($itemID) {
		$p = $this->wire('pages')->get($this->selectorItemid($itemID));
		return boolval($p->id);
	}

	/**
	 * Returns Item Group Page
	 * @param  ItemMasterItem|string $item   Item | Item ID
	 * @return Page
	 */
	public function getPage($item) {
		$itemID = is_object($item) ? $item->itemid : $item;
		$p = $this->getItemidPage($itemID);
		if (boolval($p->id) === false) {
			$itemID = $this->wire('sanitizer')->pageName($itemID);
			return $this->getItemidPage($itemID);
		}
		return $p;
	}

	/**
	 * Return Page(template=item) for Item ID
	 * @param  string $itemID Item ID
	 * @return Page
	 */
	public function getItemidPage($itemID) {
		return $this->wire('pages')->get($this->selectorItemid($itemID));
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
		$items = $this->getActiveItems();
		$updated = [];

		foreach ($items as $item) {
			if ($this->pageExists($item)) {
				$updated[$item->itemid] = $this->updatePage($item);
			} else {
				$updated[$item->itemid] = $this->createPage($item);
			}

			if ($this->pageExists($item)) {
				$this->updatePageVisibility($item, true);
			}
		}
		return $updated;
	}

	/**
	 * Create Pages For Items that don't have pages yet
	 * @return array
	 */
	public function createPagesForNewItems() {
		$items = $this->getActiveItemsWithoutPages();
		$updated = [];

		foreach ($items as $item) {
			if ($this->pageExists($item)) {
				$this->updatePage($item);
			} else {
				$updated[$item->itemid] = $this->createPage($item);
			}

			if ($this->pageExists($item)) {
				$this->updatePageVisibility($item, true);
			}
		}
		return $updated;
	}

	/**
	 * Delete Pages that are for Items that don't exist
	 * @return array
	 */
	public function deletePagesForOldItemids() {
		$deleted  = [];
		$selector = $this->selectorNotItemids($this->getActiveItemids());
		$pages    = $this->wire('pages')->find($selector);

		// Check if Page count is not the same as all item pages before deleting
		if ($pages->count() == $this->wire('pages')->count($this->selectorAll())) {
			return $deleted;
		}

		foreach ($pages as $page) {
			$deleted[$page->itemid] = $page->delete();
		}
		return $deleted;
	}

/* =============================================================
	CRUD Creates, Updates
============================================================= */
	/**
	 * Creates Item Page
	 * @param  ItemMasterItem $item Item
	 * @return bool
	 */
	public function createPage(ActiveRecordInterface $item) {
		$parent = Itemgroup::instance()->getPage($item->itemgroup);
		$p = new Page();
		$p->of(false);
		$p->parent    = $parent;
		$p->template  = static::TEMPLATE;
		$p->name         = $this->wire('sanitizer')->pageName($item->itemid);
		$p->itemid       = $item->itemid;
		$p->title        = $item->itemid;
		$p->description1 = $item->description;
		$saved = false;

		try {
			$saved = $p->save();
		} catch (\Exception $e) {
			return false;
		}

		if ($saved) {
			return $this->updatePage($item);
		} else {
			return $saved;
		}
	}

	/**
	 * Updates Item Group Page
	 * @param  ItemMasterItem $item Item Group
	 * @return bool
	 */
	public function updatePage(ActiveRecordInterface $item) {
		$parent = Itemgroup::instance()->getPage($item->itemgroup);

		$p = $this->getPage($item);
		$p->of(false);
		$p->parent       = $parent;
		$p->name         = $this->wire('sanitizer')->pageName($item->itemid);
		$p->itemid       = $item->itemid;
		$p->title        = $item->itemid;
		$p->description1 = $item->description;
		return $p->save();
	}
}
