<?php
/**
 * Fired during plugin deactivation
 */

/**
 * Fired for creation of payment gateway object .
 *
 *
 * @since      1.0.0
 * @package    My_wallet_For_Woocommerce
 * @subpackage My_wallet_For_Woocommerce/admin/includes
 * @author     netleon
 */
    class My_Wallet_Payment_Gateway{

        public $id = null;
        public $title = null;
        public $gateway = null;
        
        public function __construct($gateway_name)
        {
            $this->gateway = $gateway_name;
            $this->create_gateway($gateway_name);
        }

        public function create_gateway($gateway_name){
            switch ($gateway_name) {
                case 'paypal':
                        $this->id = 'ppec_paypal'; 
                        $this->title = 'PayPal';
                        return $this;
                    break;
                
                default:
                    echo "Something weeeent wrong in custom-Payment-gateway.php file";
                    break;
            }
        }
    }

?>
