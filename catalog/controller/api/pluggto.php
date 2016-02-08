<?php

class ControllerApiPluggto extends Controller {

	public function index() {
		$json = ['status' => 'operational', 'HTTPcode' => 200];

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
		
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		
			$this->response->addHeader('Access-Control-Max-Age: 1000');
		
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');		
		}

		$this->response->addHeader('Content-Type: application/json');
		
		$this->response->setOutput(json_encode('$json'));

	}

	public function cronGetProductsAndOrders() {
		$num_orders_pluggto  = $this->saveOrdersInPluggTo($this->existNewOrdersOpenCart());
		$num_orders_opencart = $this->saveOrdersInOpenCart($this->existNewOrdersPluggTo());

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(['orders_created_pluggto' => $num_orders_pluggto]));
	}

	public function existNewOrdersOpenCart() {
    	$this->load->model('pluggto/pluggto');

    	$response  = [];
		$allOrders = $this->model_pluggto_pluggto->getOrders();

		foreach ($allOrders->rows as $order) {
			if ($this->model_pluggto_pluggto->orderExistInPluggTo($order['order_id'])) {
				continue;
			}

			$response[] = $order;
		}

		return $response;
	}

	public function existNewOrdersPluggTo() {
		$this->load->model('pluggto/pluggto');

		$response = [];

		$allOrders = $this->model_pluggto_pluggto->getOrdersPluggTo();

		if (!$allOrders->result) {
			return false;
		}

		foreach ($allOrders->result as $order) {
			if ($this->model_pluggto_pluggto->orderExistInPluggTo($order->Order->external)) {
				continue;
			}

			$response[] = $order;
		}

		return $response;
	}

	public function saveOrdersInOpenCart($orders) {
		echo '<pre>';print_r($orders);exit;
	}

	public function saveOrdersInPluggTo($orders) {
    	$this->load->model('pluggto/pluggto');

    	$cont = 0;
    	foreach ($orders as $order) {
    		$params = [
    			'external' 			  => $order['order_id'],
    			'status' 			  => 'pending', //$order['order_status'],
    			'total' 			  => $order['total'],
    			'subtotal' 			  => '',
    			'shipping' 			  => '',
    			'discount' 			  => '',
    			'receiver_name'       => $order['shipping_firstname'],
    			'receiver_lastname'   => $order['shipping_lastname'],
    			'receiver_address'    => $order['shipping_address_1'],
    			'receiver_zipcode'    => $order['shipping_postcode'],
    			'receiver_city'       => $order['shipping_city'],
    			'receiver_state'      => '',
    			'receiver_country'    => 'Brasil',
    			'receiver_phone_area' => '',
    			'receiver_phone'      => $order['telephone'],
    			'receiver_email'      => $order['email'],
    			'delivery_type'       => 'onehour', //$order['shipping_method'],
    			'payer_name'          => $order['shipping_firstname'],
    			'payer_lastname'      => $order['shipping_lastname'],
    			'payer_address'       => $order['shipping_address_1'],
    			'payer_zipcode'       => $order['shipping_postcode'],
    			'payer_city'          => $order['shipping_city'],
    			'payer_state'         => '',
    			'payer_country'       => 'Brasil',
    			'payer_phone_area'    => '',
    			'payer_phone'         => $order['telephone'],
    			'payer_email'         => $order['email'],
    			'payer_cpf' 		  => '',
    			'payer_cnpj' 		  => '',
    			'payer_razao_social'  => '',
    			'payer_ie'			  => '',
    			'payer_gender'        => 'n/a',
    			'items'				  => [],
    			'shipments'           => [],
     		];

     		$response = $this->model_pluggto_pluggto->createOrder($params);

     		if ($response->Order->id) {
	     		$this->model_pluggto_pluggto->createRelationOrder($response->Order->id, $order['order_id']);
	     		$cont++;
     		}
    	}
	}

	public function getNotification() 
	{
		$this->load->model('pluggto/pluggto');

		$fields = [
			'resource_id'   => empty($this->request->post['resource_id']) ? '' : $this->request->post['resource_id'],
			'type'          => empty($this->request->post['type']) ? '' : $this->request->post['type'],
			'action'        => empty($this->request->post['action']) ? '' : $this->request->post['action'],
			'date_created'  => time(),
			'date_modified' => time(),
			'status'        => 1
		];

		$result = $this->model_pluggto_pluggto->createNotification($fields);

		$response = [
			'message' => $result === true ? 'Notification received sucessfully' : 'Failure getting notification. The field: '.$result.' can not be empty',
			'code'    => 200,
			'status'  => is_bool($result) ? $result : false
		];

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: POST');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	public function existNewOrders() {
    	$this->load->model('pluggto/pluggto');

    	$response  = [];
		$allOrders = $this->model_pluggto_pluggto->getOrders();

		foreach ($allOrders->rows as $order) {
			if ($this->model_pluggto_pluggto->orderExistInPluggTo($order['order_id'])) {
				continue;
			}

			$response[] = $order;
		}

		return $response;
	}

	public function cronUpdateProducts()
	{
		$this->load->model('pluggto/pluggto');

		$productsQuery = $this->model_pluggto_pluggto->getProductsNotification();

		$message = [];
		foreach ($productsQuery as $key => $value) {			
			$product = $this->model_pluggto_pluggto->getProduct($value['resource_id']);
			
			if (isset($product->Product)) {				
				$message[$key]['product_id'] = $product->Product->id;

				$this->model_pluggto_pluggto->prepareToSaveInOpenCart($product);
				$message[$key]['saved'] = $this->model_pluggto_pluggto->updateStatusNotification($product->Product->id);
				
			}
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}
		
		$response = [
			'code'    => 200,
			'message' => $message
		];

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

}