<?php
class ModelCheckoutSuccess extends Model {
    public function getMessage($order_id){
        $message = '';
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "'");

        if(!empty($query->num_rows)) {
            $order_status_id = $query->row['order_status_id'];

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id ='" . $this->config->get('config_language_id') ."'") ;

            if(!empty($query->num_rows)) {
                $message = $query->row['message'];
            }
        }

        return $message;
    }
}