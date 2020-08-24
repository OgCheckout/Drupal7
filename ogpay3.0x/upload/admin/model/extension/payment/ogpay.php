<?php

class ModelExtensionPaymentOgpay extends Model {

    public function addColumn() {
		$checkColumn = $this->db->query("SHOW columns from `" . DB_PREFIX . "order` where field='track_id'");
        if($checkColumn->num_rows<1){
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD COLUMN track_id VARCHAR(255) NULL");
        }

    }
}