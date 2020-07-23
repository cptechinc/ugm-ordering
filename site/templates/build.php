<?php
	$result = array();
	$itemgroupm = $modules->get('UgmOrderingPagesItemGroup');
	$itemsm = $modules->get('UgmOrderingPagesItem');
	$result['groups'] = $itemgroupm->update_itemgroup_pages();
	$result['items'] = $itemsm->update_item_pages();

	echo json_encode($result);
