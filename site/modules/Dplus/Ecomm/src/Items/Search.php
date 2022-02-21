<?php namespace Dplus\Ecomm\Items;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\PageArray;
use ProcessWire\User;
// Dplus Warehouse Management
use Dplus\Wm\Inventory\Lots\Lookup\ExcludePackBin as WhseLots;

/**
 * Class for Searching Item Pages
 * @property User   $user          User
 * @property string $selector      Selector String
 * @property bool   $inStockOnly   Search only items with available stock? default = true
 * @property string $query         Search Query
 * @property string $sortby        Property to Sort By
 * @property string $sortrule      Sort Role
 * @property string $restrictItems Restrict Items for User?
 */
class Search extends WireData {
	// Processwire Selectors
	// https://processwire.com/api/selectors/
	const SELECTOR_TEMPLATE = 'template=item';
	const SELECTOR_SEARCH = 'title|body|itemid|name|description1';

	public function __construct() {
		$this->user     = $this->wire('user');
		$this->selector = self::SELECTOR_TEMPLATE;
		$this->query = '';
		$this->inStockOnly = true;
		$this->sortby   = 'itemid';
		$this->sortrule = 'asc';
		$this->restrictItems = true;
	}

/* =============================================================
	Setter Functions
============================================================= */
	/**
	 * Set User
	 * @param User
	 */
	public function setUser(User $user) {
		$this->user = $user;
	}

	/**
	 * Set Search Query
	 * @param string $q
	 */
	public function setQuery($q) {
		$this->query = $q;
	}

	/**
	 * Set SortBy
	 * @param string $sortby   Field to Sort By
	 * @param string $sortrule asc|desc
	 */
	public function setSort($sortby, $sortrule) {
		$sortrule = strtolower($sortrule);
		$this->sortby  = $sortby;
		$this->sortrule = in_array($sortrule, array('asc', 'desc')) ? $sortrule : 'asc';
	}

	/**
	 * Set if Searching only Items that have available stock
	 * @param bool $onlystocked
	 */
	public function setInStockOnly($stocked = true) {
		$this->inStockOnly = $stocked;
	}

	/**
	 * Return PW sort rule
	 * @return string
	 */
	public function pwSortrule() {
		$sortrule = strtolower($this->sortrule) == 'desc' ? '-' : '';
		return $this->sortby . $sortrule;
	}

/* =============================================================
	Selector Functions
============================================================= */
	/**
	 * Return Selector for searching
	 * @return string
	 */
	private function getQuerySelector() {
		$replace = ['-' => '', '–' => ''];
		$q = str_replace('–', '', $this->query);
		$sanitizer = $this->wire('sanitizer');
		$selector = self::SELECTOR_TEMPLATE;
		$searchFields = self::SELECTOR_SEARCH;
		$selector .= $this->restrictItems ? $this->getRestrictedItemsSelector() : '';
		$selector .= $this->query ? ", ($searchFields%=$q)" : '';
		$selector .= $this->query ? ", ($searchFields~=$q)" : '';

		if ($this->query) {
			$subselector = '';
			$words = explode(' ', $q);

			foreach ($words as $word) {
				$subselector .= ",$searchFields*=$word";
			}
			$subselector = ltrim($subselector, ",");
			$selector .= ", ($subselector)";
		}
		// $selector .= $this->query ? ", ($searchFields~%=$q)" : '';
		return $selector;
	}

	/**
	 * Return Selector for Restricted Items for User
	 * @return string
	 */
	private function getRestrictedItemsSelector() {
		$user  = $this->user;
		$pages = $this->wire('pages');

		if ($user->has_item_restrictions() === false) {
			return '';
		}

		$groups = implode('|', $user->restricted_itemgroups());
		$ids    = implode('|', $pages->find("template=item-group,groupcode=$groups")->explode('id'));
		return ",parent=$ids";
	}

/* =============================================================
	Find Functions
============================================================= */
	/**
	 * Return the number of Item Pages that match selector
	 * @return int
	 */
	public function count() {
		$selector = $this->getQuerySelector();
		$this->selector = $selector;
		$count = $this->wire('pages')->count($selector);
		return $this->inStockOnly ? $this->countStocked($selector) : $count;
	}

	/**
	 * Return the Number of Item Pages that match selector and have stock
	 * @param  string $selector
	 * @return int
	 */
	public function countStocked($selector) {
		$inventory = $this->getWhseLots();
		$items     = $this->wire('pages')->find($selector);
		$itemIDs   = $items->explode('itemid');
		return $inventory->countInstockByItemidDistinct($itemIDs);
	}

	/**
	 * Return Pages(template=item) that match Selector
	 * @param  int    $pagenbr  What Page number to start on
	 * @param  int    $limit    Number of Pages to return
	 * @return PageArray
	 */
	public function find($pagenbr = 1, $limit = 10) {
		$start    = $pagenbr > 1 ? ($pagenbr * $limit) - $limit : 0;
		$selector = $this->getQuerySelector();
		$items    = $this->wire('pages')->find($selector);
		$items = $this->inStockOnly ? $this->findStocked($items) : $items;

		if ($limit) {
			$selector = "limit=$limit, start=$start";
		}
		$selector .= ", sort=" . $this->pwSortrule();
		$this->selector = $selector;
		return $items->find($selector);
	}

	/**
	 * Return Pages(template=item) that match Selector, and that are stocked
	 * @param  int    $pagenbr  What Page number to start on
	 * @param  int    $limit    Number of Pages to return
	 * @return PageArray
	 */
	public function findStocked(PageArray $items) {
		$inventory = $this->getWhseLots();
		$itemIDs   = $inventory->getItemidsWithQty($items->explode('itemid'));
		return $items->find('itemid='.implode('|', $itemIDs));
	}

	public function getWhseLots() {
		$whseLots = WhseLots::getInstance();
		$whseLots->setWhseID(1);
		return $whseLots;
	}
}
