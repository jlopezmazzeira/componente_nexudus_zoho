<?php

/**
* 
*/
include_once('Config.php');

class Zoho {
	static private $url_insert_invoice = 'xml/Invoices/insertRecords';
	static private $url_account = 'json/Accounts/getRecordById';
	static private $url_contact = 'json/Contacts/getRecordById';
	static private $url_contact_data = 'json/Contacts/getSearchRecordsByPDC';
	static private $url_products = 'json/Products/getRecords';
	static private $url_prefix = 'crm.zoho.com/crm/private/';
	
	function getUrlInsertInovice(){ 
		return self::$url_insert_invoice; 
	}

	function getUrlAccount(){ 
		return self::$url_account; 
	}

	function getUrlContact(){ 
		return self::$url_contact; 
	}

	function getUrlContactData(){ 
		return self::$url_contact_data; 
	}

	function getUrlProducts(){ 
		return self::$url_products; 
	}

	function getUrlPrefix(){ 
		return self::$url_prefix; 
	}

	public function getIdContact($email){
		if(!empty($email)) {
			$select_columns = 'Contacts(contactid)';
			$search_column = 'email';
			$search_value = $email;
			$url = $this->getPrefixUrl($this->getUrlContactData());
			$url = $url.'selectColumns='.$select_columns.'&searchColumn='.$search_column.'&searchValue='.$search_value;
			$data = $this->getData($url);
			$ID = '';
			if (array_key_exists('result', $data['response'])) {
				$ID = $data['response']['result']['Contacts']['row']['FL']['content'];
			}
			return $ID;	
		}  else {
			throw new Exception("008", 1);
		}
	}

	public function getDataContact($contact_id,$email_coworker){
		$contact = array(
			'contact_id' => '',
			'name' => '',
			'lastname' => '',
			'email' => $email_coworker,
			'account_id' => '', 
			'account_name' => ''
			);

		if (!empty($contact_id)) {
			$url = $this->getPrefixUrl($this->getUrlContact());
			$url = $url.'id='.$contact_id;
			$data = $this->getData($url);

			$data_array = $data['response']['result']['Contacts']['row']['FL'];
			
			for ($i=0; $i < count($data_array); $i++) { 
				if ($data_array[$i]['val'] == 'CONTACTID') {
					$contact['contact_id'] = $data_array[$i]['content'];
				} elseif ($data_array[$i]['val'] == 'First Name') {
					$contact['name'] = $data_array[$i]['content'];
				} elseif ($data_array[$i]['val'] == 'Last Name') {
					$contact['lastname'] = $data_array[$i]['content'];
				} elseif ($data_array[$i]['val'] == 'Email') {
					$contact['email'] = $data_array[$i]['content'];
				} elseif ($data_array[$i]['val'] == 'ACCOUNTID') {
					$contact['account_id'] = $data_array[$i]['content'];
				} elseif ($data_array[$i]['val'] == 'Account Name') {
					$contact['account_name'] = $data_array[$i]['content'];
				}
			}
		}

		return $contact;
	}

	public function getDataAccount($account_id){
		$account = array(
			'account_id' => '',
			'account_name' => '',
			'giro' => '',
			'razon_social' => '',
			'RUT' => '',
			'address' => '',
			'comuna' => ''
		);

		if (!empty($account_id)) {
			$url = $this->getPrefixUrl($this->getUrlAccount());
			$url = $url.'id='.$account_id;
			$data = $this->getData($url);
			
			$data_array = $data['response']['result']['Accounts']['row']['FL'];
			
			for ($i=0; $i < count($data_array); $i++) { 
				if ($data_array[$i]['val'] == 'ACCOUNTID') {
					$account['account_id'] = $data_array[$i]['content'];
				} elseif ($data_array[$i]['val'] == 'Account Name') {
					$account['account_name'] = $data_array[$i]['content'];
				} elseif ($data_array[$i]['val'] == 'Giro') {
					$account['giro'] = $data_array[$i]['content'];
				} elseif ($data_array[$i]['val'] == 'Razón Social') {
					$account['razon_social'] = $data_array[$i]['content'];
				} elseif ($data_array[$i]['val'] == 'RUT') {
					$account['RUT'] = $data_array[$i]['content'];
				} elseif ($data_array[$i]['val'] == 'Dirección') {
					$account['address'] = $data_array[$i]['content'];
				} elseif ($data_array[$i]['val'] == 'Comuna') {
					$account['comuna'] = $data_array[$i]['content'];
				}
			}
		}

		return $account;
	}

	public function getProducts(){
		$url = $this->getPrefixUrl($this->getUrlProducts());
		$url = $url.'fromIndex=1&toIndex=200';
		$data = $this->getData($url);
		if (!empty($data)) {
			$products = array();

			for ($i=0; $i < count($data['response']['result']['Products']['row']); $i++) {
				$product = array(
						'product_id' => $data['response']['result']['Products']['row'][$i]['FL'][0]['content'],
						'product_name' => $data['response']['result']['Products']['row'][$i]['FL'][3]['content'],
						'product_code' => $data['response']['result']['Products']['row'][$i]['FL'][4]['content'],
						'product_category' => $data['response']['result']['Products']['row'][$i]['FL'][6]['content'],
						'product_price' => $data['response']['result']['Products']['row'][$i]['FL'][14]['content']
					);

				array_push($products, $product);
			}

			return $products;
		}  else {
			throw new Exception("009", 1);
		}
	}

	public function proccessProduct($products,$products_invoices){
		$products_data = array();
		for ($i=0; $i < count($products_invoices); $i++) { 
			for ($j=0; $j < count($products); $j++) {
				$product = explode("-", $products_invoices[$i]['name']);
				for ($k=0; $k < count($product); $k++) {
					$name_product = $product[$k];
					$aparition = substr_count($name_product, 'Sala');
					$position = 0;
					
					if ($aparition >= 2) {
						$position = strrpos($name_product, 'Sala');
						if($position > 0){
							$name_product = substr($name_product, 0, $position - 1);
						}
					}
					
					$name_product = trim($name_product);
					$name = trim($products[$j]['product_name']);
					
					if ($name_product == $name) {
						$product_detail = array(
								'id' => $products[$j]['product_id'],
								'name' => $name, 
								'price_unit' => $products[$j]['product_price'], 
								'quantity' => $products_invoices[$i]['quantity'], 
								'sub_total' => $products_invoices[$i]['sub_total'],
								'discount' => 0,
								'total_discount' => 0,
								'list_price' => $products_invoices[$i]['sub_total'] / $products_invoices[$i]['quantity'],
								'total' => $products_invoices[$i]['sub_total'],
						);

						array_push($products_data, $product_detail);
					}
				}
			}
		}

		if (!empty($products_data)) {
			$products_xml = "";
			for ($i=0; $i < count($products_data); $i++) {
				$no = $i + 1;
				$products_xml .= '<product no="'.$no.'"><FL val="Product Id">'.$products_data[$i]['id'].'</FL><FL val="Product Name">'.$products_data[$i]['name'].'</FL><FL val="Unit Price">'.$products_data[$i]['price_unit'].'</FL><FL val="Quantity">'.$products_data[$i]['quantity'].'</FL><FL val="List Price">'.$products_data[$i]['list_price'].'</FL><FL val="Discount">'.$products_data[$i]['discount'].'</FL><FL val="Total">'.$products_data[$i]['sub_total'].'</FL><FL val="Total After Discount">'.$products_data[$i]['total_discount'].'</FL><FL val="Net Total">'.$products_data[$i]['total'].'</FL></product>';
			}
			return $products_xml;
		} else {
			throw new Exception("010", 1);	
		}
		
	}

	public function paramInvoice($params,$account,$contact,$products_xml){
		$param = '<Invoices><row no="1"><FL val="Invoice Date">'.$params['date_invoce'].'</FL><FL val="Fecha de Pre Factura">'.$params['date_invoce'].'</FL><FL val="Subject">'.$params['invoice_number'].'</FL><FL val="Account Name">'.$account['account_name'].'</FL><FL val="ACCOUNTID">'.$account['account_id'].'</FL><FL val="Estado de Pago">Pendiente de Pago</FL><FL val="RUT">'.$account['RUT'].'</FL><FL val="Rut empresa">'.$account['RUT'].'</FL><FL val="Email Notificación">'.$contact['email'].'</FL><FL val="Product Details">'.$products_xml.'</FL><FL val="Sub Total">'.$params['sub_total_invoice'].'</FL><FL val="Tax">'.$params['tax_invoice'].'</FL><FL val="Grand Total">'.$params['total'].'</FL><FL val="Total a Pagar">'.$params['total'].'</FL><FL val="Razón Social">'.$account['razon_social'].'</FL><FL val="Dirección">'.$account['address'].'</FL><FL val="Giro">'.$account['giro'].'</FL><FL val="Tipo Factura">Afecta</FL><FL val="Factura Nexus Asociada">'.$params['invoice_number'].'</FL><FL val="Id Factura Nexus">'.$params['invoice_id'].'</FL></row></Invoices>';
		return $param;
	}

	public function sendInvoice($param_invoice){
		$url = Config::getProtocolHttps().$this->getUrlPrefix().$this->getUrlInsertInovice();
		$param = 'authtoken='.Config::getToken().'&scope='.Config::getScope().'&xmlData='.$param_invoice;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url_insert_invoices);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	function getPrefixUrl($subsequent){
		$prefix = Config::getProtocolHttps().$this->getUrlPrefix().$subsequent.'?authtoken='.Config::getToken().'&scope='.Config::getScope().'&';
		return $prefix;
	}

	function getData($url){
		$content = file_get_contents($url);
		$result = json_decode($content,true);
		return $result;
	}

}
?>