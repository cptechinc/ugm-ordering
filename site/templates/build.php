<?php
	$result = array();
	$itemgroupm = $modules->get('UgmOrderingPagesItemGroup');
	$itemsm = $modules->get('UgmOrderingPagesItem');
	$result['groups'] = $itemgroupm->update_itemgroup_pages();
	$result['items'] = $itemsm->updatePages();
	$result['pricing'] = $modules->get('ItemSearchDplus')->cleanup_pricing();

	echo json_encode($result);
