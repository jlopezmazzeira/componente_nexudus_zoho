<?php
	include_once ('Nexudus.php');
	include_once ('Zoho.php');
	include_once ('Email.php');
	try {
		$request = file_get_contents('php://input');
		$request = '';
		$nexudus = new Nexudus();
		$data = $nexudus->getDataRequest($request);
		$params = $nexudus->getDataInvoinces($data);
		$email_coworker = $nexudus->getDataCoworker($params['id_contact']);
		$products_i = $nexudus->getProductsInvoices($params['invoices_id']);
		$products_invoices = $nexudus->proccessProductInvoice($products_i);
		$params = $nexudus->updateTaxAndSubTotal($products_invoices,$params);
		
		$zoho = new Zoho();
		$contact_id = $zoho->getIdContact($email_coworker);
		$contact = $zoho->getDataContact($contact_id,$email_coworker);
		$account = $zoho->getDataAccount($contact['account_id']);
		$products = $zoho->getProducts();
		$products_xml = $zoho->proccessProduct($products,$products_invoices);
		$param_invoice = $zoho->paramInvoice($params,$account,$contact,$products_xml);
		$zoho->sendInvoice($param_invoice);	
	} catch (Exception $e) {
		$address = '';
		$subject = '';
		$body = '';
		$email = new Email();
		$email->send($address,$subject,$body);
	}

?>