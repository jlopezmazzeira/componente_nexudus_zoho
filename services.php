<?php 
	$token = 'mi_token_crm';
	$pass = 'mi_clave_nexudus';
	$login = 'mi_usuario_administrador_nexudus';
	$url = 'spaces.nexudus.com/api/billing/coworkerinvoicelines';
	$url_cowerkers = 'spaces.nexudus.com/api/spaces/coworkers?Coworker_Id=';
	$protocol = 'http://';
	$protocol_security = 'https://';
	
	$entity_body = file_get_contents('php://input');
	$json = json_decode($entity_body,true);
	$params = getDataRequest($json);
	$email_coworker = getDataCoworker($protocol_security,$login,$pass,$url_cowerkers,$params['id_contact']);

	$contact_id = getIdContact($token,$email_coworker);
	$contact = getDataContact($token,$contact_id);
	$account = getDataAccount($token,$contact['account_id']);
        
	$products_i = getProductsInvoices($protocol,$login,$pass,$url,$params);
	$products_invoices = proccessProductInvoice($products_i);
	$products = getProducts($token);
	$products_xml = proccessProduct($products,$products_invoices);
       
	insertInvoices($token,$params,$account,$contact,$products_xml);

	function getDataRequest($json){
		$params = array(
			'email' => $json[0]['UpdatedBy'],
			'invoice_number' => $json[0]['InvoiceNumber'],
			'subject' => $json[0]['BillToName'],
			'address' => $json[0]['BillToAddress'],
			'product' => $json[0]['Description'],
			'total' => $json[0]['TotalAmount'],
			'tax' => $json[0]['TaxAmount'],
			'sub_total' => $json[0]['TotalAmount'] - $json[0]['TaxAmount'],
			'id_contact' => $json[0]['CoworkerId'],
			'invoices_id' => $json[0]['Id']);

		return $params;	
	}
	
	function getDataCoworker($protocol_security,$login,$pass,$url_cowerkers,$contact_coworker){
		$content = file_get_contents($protocol_security.$login.':'.$pass.'@'.$url_cowerkers.$contact_coworker); 
		$data = json_decode($content, true);
		return $data['Records'][0]['Email'];
	}
	
	function getIdContact($token,$email){
		$select_columns = 'Contacts(contactid)';
		$search_column = 'email';
		$search_value = $email;
		$url_prefix = 'https://crm.zoho.com/crm/private/json/Contacts/getSearchRecordsByPDC?';
		$params = 'authtoken='.$token.'&scope=crmapi&selectColumns='.$select_columns.'&searchColumn='.$search_column.'&searchValue='.$search_value;
		$url = $url_prefix.$params;
		$entity_body = file_get_contents($url);
		$data = json_decode($entity_body, true);
                
	    return $data['response']['result']['Contacts']['row']['FL']['content'];
	}

	function getDataContact($token,$contact_id){
		$url = 'https://crm.zoho.com/crm/private/json/Contacts/getRecordById?&authtoken='.$token.'&scope=crmapi&id='.$contact_id;
		$entity_body = file_get_contents($url);
		$data = json_decode($entity_body, true);
		$contact = array(
				'contact_id' => $data['response']['result']['Contacts']['row']['FL'][0]['content'],
				'name' => $data['response']['result']['Contacts']['row']['FL'][3]['content'],
				'lastname' => $data['response']['result']['Contacts']['row']['FL'][4]['content'],
				'email' => $data['response']['result']['Contacts']['row']['FL'][7]['content'],
				'account_id' => $data['response']['result']['Contacts']['row']['FL'][5]['content'], 
				'account_name' => $data['response']['result']['Contacts']['row']['FL'][6]['content']
				);
			
		return $contact;
	}

	function getDataAccount($token,$account_id){
		$url = 'https://crm.zoho.com/crm/private/json/Accounts/getRecordById?&authtoken='.$token.'&scope=crmapi&id='.$account_id;
		$entity_body = file_get_contents($url);
		$data = json_decode($entity_body, true);
		$account = array(
				'account_id' => $data['response']['result']['Accounts']['row']['FL'][0]['content'],
				'account_name' => $data['response']['result']['Accounts']['row']['FL'][3]['content'],
				'giro' => $data['response']['result']['Accounts']['row']['FL'][11]['content'],
				'razon_social' => $data['response']['result']['Accounts']['row']['FL'][13]['content'],
				'RUT' => $data['response']['result']['Accounts']['row']['FL'][14]['content'],
				'address' => $data['response']['result']['Accounts']['row']['FL'][16]['content'],
				'comuna' => $data['response']['result']['Accounts']['row']['FL'][15]['content']
			);

		return $account;
	}

	function getProductsInvoices($protocol,$login,$pass,$url,$params){
		$products_i = array();
		$content = file_get_contents($protocol.$login.':'.$pass.'@'.$url); 
		$data = json_decode($content, true);

		for ($i=0; $i < count($data['Records']); $i++) { 
			if ($data['Records'][$i]['CoworkerInvoiceId'] == $params['invoices_id']) {		
				array_push($products_i, $data['Records'][$i]);
			}
		}

		return $products_i;
	}

	function proccessProductInvoice($products){
		$data = array();
		for ($i=0; $i < count($products); $i++) {
	 		$product = array(
				'name' => $products[$i]['Description'],
				'quantity' => $products[$i]['Quantity'],
				'sub_total' => $products[$i]['SubTotal'],
				'tax_amount' => $products[$i]['TaxAmount'],
				'tax_rate' =>$products[$i]['TaxRate'],
				'price_unit' => $products[$i]['SubTotal'] / $products[$i]['Quantity']);

			array_push($data, $product);
		}

		return $data;
	}
	
	function getProducts($token){
		$url = 'https://crm.zoho.com/crm/private/json/Products/getRecords?authtoken='.$token.'&scope=crmapi&fromIndex=1&toIndex=200';
		$entity_body = file_get_contents($url);
		$data = json_decode($entity_body, true);
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
	}

	function proccessProduct($products,$products_invoices){
		$products_data = array();
		for ($i=0; $i < count($products_invoices); $i++) { 
			for ($j=0; $j < count($products); $j++) { 
				if ($products_invoices[$i]['name'] == $products[$j]['product_name']) {
					$product_detail = array(
							'id' => $products[$j]['product_id'],
							'name' => $products[$j]['product_name'], 
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

		$products_xml = "";

		for ($i=0; $i < count($products_data); $i++) {
			$no = $i + 1; 
			$products_xml .= '<product no="'.$no.'"><FL val="Product Id">'.$products_data[$i]['id'].'</FL><FL val="Unit Price">'.$products_data[$i]['price_unit'].'</FL><FL val="Quantity">'.$products_data[$i]['quantity'].'</FL><FL val="Total">'.$products_data[$i]['sub_total'].'</FL><FL val="Discount">'.$products_data[$i]['discount'].'</FL><FL val="Total After Discount">'.$products_data[$i]['total_discount'].'</FL><FL val="List Price">'.$products_data[$i]['list_price'].'</FL><FL val="Net Total">'.$products_data[$i]['total'].'</FL></product>';
		}
		return $products_xml;
	}

	function insertInvoices($token,$params,$account,$contact,$products_xml){
		$param = '<Invoices><row no="1"><FL val="Invoice Date">'.date('m/d/Y').'</FL><FL val="Fecha de Pre Factura">'.date('m/d/Y').'</FL><FL val="Subject">'.$params['subject'].'</FL><FL val="Account Name">'.$account['account_name'].'</FL><FL val="ACCOUNTID">'.$account['account_id'].'</FL><FL val="Estado de Pago">Pendiente de Pago</FL><FL val="RUT">'.$account['RUT'].'</FL><FL val="Rut empresa">'.$account['RUT'].'</FL><FL val="Email Notificación">'.$contact['email'].'</FL><FL val="Product Details">'.$products_xml.'</FL><FL val="Sub Total">'.$params['sub_total'].'</FL><FL val="Tax">'.$params['tax'].'</FL><FL val="Grand Total">'.$params['total'].'</FL><FL val="Total a Pagar">'.$params['total'].'</FL><FL val="Razón Social">'.$account['razon_social'].'</FL><FL val="Dirección">'.$account['address'].'</FL><FL val="Giro">'.$account['giro'].'</FL><FL val="Tipo Factura">Afecta</FL><FL val="Factura Nexus Asociada">'.$params['invoice_number'].'</FL><FL val="Id Factura Nexus">'.$params['invoices_id'].'</FL></row></Invoices>';
        //$file = fopen("test.txt", "w");
        //fwrite($file, "param => " .$param. PHP_EOL);
        //fclose($file);
		header('Location: https://crm.zoho.com/crm/private/xml/Invoices/insertRecords?newFormat=1&authtoken='.$token.'&scope=crmapi&xmlData='.$param);
	
	}

?>
