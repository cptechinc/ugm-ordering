<?php
	$result = array();
	$itemgroupm = $modules->get('UgmOrderingPagesItemGroup');
	$itemsm = $modules->get('UgmOrderingPagesItem');
	$result['groups'] = $itemgroupm->update_itemgroup_pages();
	$result['items'] = $itemsm->updatePages();

	echo json_encode($result);
