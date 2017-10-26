<?php  

/**
* 
*/
include_once('Config.php');

class Nexudus {

	static private $url_invoice = 'spaces.nexudus.com/api/billing/coworkerinvoices?CoworkerInvoice_Id=';
	static private $url_products_invoice = 'spaces.nexudus.com/api/billing/coworkerinvoicelines?CoworkerInvoiceLine_CoworkerInvoice='; 
	static private $url_cowerker = 'spaces.nexudus.com/api/spaces/coworkers?Coworker_Id=';
	
	function getUrlInvoice(){ 
		return self::$url_invoice; 
	}

	function getUrlProductsInvoice(){ 
		return self::$url_products_invoice; 
	}

	function getUrlCowerker(){ 
		return self::$url_cowerker; 
	}
	
	public function getDataRequest($request){
		if(!empty($request)) {
			$json_request = json_decode($request,true);
			$invoice_id = $json_request[0]['Id'];
			$invoice_number = $json_request[0]['InvoiceNumber'];
			$file = fopen("log.txt", "a");
			fwrite($file, '-----------INICIO-------------' . PHP_EOL);
			fwrite($file, $invoice_id . PHP_EOL);
			fwrite($file, $invoice_number . PHP_EOL);
			fwrite($file, '-----------FIN----------------' . PHP_EOL);
			fclose($file);
			if (!empty($invoice_id)) {
				$url = $this->getPrefixUrl().$this->getUrlInvoice().$invoice_id;
				$content = $this->getData($url);
				return $content['Records'][0];
			} else {
				throw new Exception("002", 1);	
			}
		} else {
			throw new Exception("001", 1);
		}
	}

	public function getDataInvoinces($data_invoices){
		if(empty($data_invoices)) {
			$updatedBy = ($data_invoices['UpdatedBy'] == '[System]') ? '': $data_invoices['UpdatedBy'];
			$date_invoce = new DateTime($data_invoices['CreatedOn']);	
			$date_invoce = date_format($date_invoce, 'm/d/Y');
			$subject = str_replace("amp;", "", $data_invoices['BillToName']);
			$subject = html_entity_decode($subject);
			$subject = $this->removeAccents($subject);

			$params = array(
				'email' => $updatedBy,
				'invoice_number' => $data_invoices['InvoiceNumber'],
				'subject' =>$subject,
				'address' => $data_invoices['BillToName'],
				'product' => $data_invoices['Description'],
				'total' => $data_invoices['TotalAmount'],
				'tax' => $data_invoices['TaxAmount'],
				'sub_total' => $data_invoices['TotalAmount'] - $data_invoices['TaxAmount'],
				'id_contact' => $data_invoices['CoworkerId'],
				'invoice_id' => $data_invoices['Id'],
				'date_invoce' => $date_invoce,
				'tax_invoice' => 0,
				'sub_total_invoice' => 0
				);

			return $params;	
		}  else {
			throw new Exception("003", 1);
		}
	}

	public function getDataCoworker($contact_coworker){
		if(!empty($contact_coworker)) {
			$url = $this->getPrefixUrl().$this->getUrlCowerker().$contact_coworker;
			$content = $this->getData($url);
			return $content['Records'][0]['Email'];	
		}  else {
			throw new Exception("004", 1);
		}
	}

	public function getProductsInvoices($invoice_id){
		if(!empty($invoice_id)) {
			$products_i = array();
			$url = $this->getPrefixUrl().$this->getUrlProductsInvoice().$invoice_id;
			$content = $this->getData($url);
			$products_i = $content['Records'];
			
			return $products_i;	
		}  else {
			throw new Exception("005", 1);
		}
	}

	public function proccessProductInvoice($products){
		if(!empty($products)) {}
			$data = array();
			for ($i=0; $i < count($products); $i++) {
				$name = $products[$i]['Description'];
				$aparition = substr_count($name, 'Sala');

				if ($aparition > 0) {
					$name = str_replace("<br/>", " ", $name);
		 		}

		 		$product = array(
					'name' => $name,
					'quantity' => $products[$i]['Quantity'],
					'sub_total' => $products[$i]['SubTotal'],
					'tax_amount' => $products[$i]['TaxAmount'],
					'tax_rate' =>$products[$i]['TaxRate'],
					'price_unit' => $products[$i]['SubTotal'] / $products[$i]['Quantity']);

				array_push($data, $product);
			}

			return $data;	
		}  else {
			throw new Exception("006", 1);
		}
	}

	public function updateTaxAndSubTotal($products_invoices,$params){
		if(!empty($products_invoices) && !empty($params)) {
			$sub_total = 0;
			for ($i=0; $i < count($products_invoices); $i++) { 
				$sub_total += $products_invoices[$i]['sub_total'];
			}
			$params['sub_total_invoice'] = $sub_total;
			$params['tax_invoice'] = $params['total'] - $sub_total;
			return $params;	
		}  else {
			throw new Exception("007", 1);
		}
	}

	public function recoverInvoice($invoice_id){
		if (!empty($invoice_id)) {
			$url = $this->getPrefixUrl().$this->getUrlInvoice().$invoice_id;
			$content = $this->getData($url);
			return $content['Records'][0];
		} else {
			throw new Exception("002", 1);	
		}
	}

	function getPrefixUrl(){
		$prefix = Config::getProtocolHttps().Config::getUser().':'.Config::getPass().'@';
		return $prefix;
	}

	function getData($url){
		$content = file_get_contents($url);
		$result = json_decode($content,true);
		return $result;
	}

	function removeAccents($string){
		//Ahora reemplazamos las letras
		$string = str_replace(
			array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
			array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
			$string
		);

		$string = str_replace(
			array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
			array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
			$string
		);

		$string = str_replace(
			array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
			array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
			$string
		);

		$string = str_replace(
			array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
			array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
			$string
		);

		$string = str_replace(
			array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
			array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
			$string
		);

		$string = str_replace(
			array('ñ', 'Ñ', 'ç', 'Ç'),
			array('n', 'N', 'c', 'C'),
			$string
		);

		return $string;
	}

}
?>