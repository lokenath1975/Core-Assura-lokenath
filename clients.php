<?php
//error_reporting(E_ALL);
require_once('clients.model.custom.php');
include(CORE_ABSOLUTE_PATH . 'clients/bre.model.php');
include_once(COMMON_PATH."clsDocument.php");
include_once(MAGNUM_PATH."boot_core.php");

class clientsExtended extends clients
{
	private $modelObjCustom;
	private $quoteEngineObj;
	private $magnumBoot;
	function __construct()
	{
		parent::__construct();
		$this->modelObjCustom = new clientsModelCustom();
		$this->modelObjBre = new breModelCustom(); 
		$this->magnumBoot = new bootModel();
		$this->arrDuration = array(
			"1" => "Yearly",
			"2" => "Monthly",
			"3" => "Other"
		);
	}
	function magnumTest(){
		$Token = $this->magnumBoot->getToken();
		echo $Token;
		
		die('STOP');
	}
	function admin_clientRiskDetails($allQuestionGroupIds = '') {
		$encryptedBusinessEventId = $this->request['businessEventId'];
         $businessEventId = $GLOBALS['cfn']->decryptIt($encryptedBusinessEventId);
		
        $QuickQuoteData = $this->modelObjCustom->getQuickQuoteData($businessEventId);
        $businessEventDetails = $this->modelObjCustom->getBusinessIdWrtBusinessEventId($businessEventId);

        $businessId = $businessEventDetails['business_id'];
        
        $sqlRisk= "SELECT r.*,re.risk_event_id, bre.business_event_id FROM ".CORE_DETAILS_RISK." r
        	LEFT JOIN ".CORE_DETAILS_PRODUCT_VERSION_SECTION." pvs ON pvs.section_version_id=r.section_version_id
        	LEFT JOIN ".CORE_DETAILS_PRODUCT_BUILDER_SECTION_TABLE." ps ON ps.product_builder_section_id=pvs.section_id
        	LEFT JOIN ".CORE_DETAILS_RISK_EVENT." re ON re.risk_id=r.risk_id
        	LEFT JOIN ".CORE_DETAILS_BUSINESS_EVENT_RISK." bre ON bre.risk_event_id=re.risk_event_id
        WHERE r.business_id=".$businessId." AND bre.business_event_id=".$businessEventId." AND ps.section_name LIKE 'D-Life' 
        ORDER BY r.risk_id DESC";
		$queryRisk = $GLOBALS['db']->sqlQuery($sqlRisk);
		$resultRisk = $GLOBALS['db']->sqlFetchRowset($queryRisk);
		$arrRiskEventId=array();
		$sectionId=array();
		foreach($resultRisk as $riskEvents){
			if(!in_array($riskEvents['section_version_id'],$sectionId)){
				array_push($sectionId,$riskEvents['section_version_id']);
				array_push($arrRiskEventId,$riskEvents['risk_event_id']);
			}
		}
		$riskEventIds=implode(",",$arrRiskEventId);
		
		$question_code = 'Mag1';
		$sqlQRes="SELECT rer.risk_data FROM ".CORE_DETAILS_RISK_EVENT_GROUP." reg
			LEFT JOIN ".CORE_DETAILS_RISK_EVENT_QUESTION." req ON req.risk_event_group_id=reg.risk_event_group_id
			LEFT JOIN ".CORE_DETAILS_QUESTION_DETAILS_TABLE." qd ON qd.question_id=req.question_id
			LEFT JOIN ".CORE_DETAILS_RISK_EVENT_RESPONSE." rer ON rer.risk_event_question_id=req.risk_event_question_id
		WHERE reg.risk_event_id IN (".$riskEventIds.") AND qd.question_code='".$question_code."' AND qd.question_group_id=reg.question_group_id";
		$queryQRes = $GLOBALS['db']->sqlQuery($sqlQRes);
		$resultQRes = $GLOBALS['db']->sqlFetchRowset($queryQRes);
		$magnumCaseUUID = '';
		if(isset($resultQRes[0]['risk_data']) && $resultQRes[0]['risk_data'] != ''){
			$magnumCaseUUID = $resultQRes[0]['risk_data'];
		}

		$businessEventClientData = $this->modelObjCustom->getBusinessEventClientData($businessEventId);

		
        $lifeFPH = array(
			'First_name'=>$businessEventClientData['firstname'],
			'Last_name'=>$businessEventClientData['surname'],
			'Gender' => $businessEventClientData['gender'],
			'DateOfBirth' => $businessEventClientData['dob'],
			'SmokingStatus' => $businessEventClientData['smoker_id'],
			'Benefits' => ''		
		);		
		
		$additional_client_data = $this->modelObjCustom->getadditionalClientData($businessEventId);
       
        $lifeSPH  = array(
			'First_name'=>$additional_client_data['firstname'] ?? '',
			'Last_name'=>$additional_client_data['surname'] ?? '',
			'Gender' => $additional_client_data['gender'] ?? '',
			'DateOfBirth'=> $additional_client_data['dob'] ?? '',
			'SmokingStatus' => $additional_client_data['smoker_id'],
			'Benefits' => ''		
		);
		
		$benefitFPH = array();
		$benefitSPH = array();
		if(!empty($QuickQuoteData)) {
			foreach($QuickQuoteData as $QuickQuote){
				$benefit = array(
					'type_of_policy' => $QuickQuote['type_of_policy'],
					'amount' => $QuickQuote['suminsured'],
					'termBasis' => 'YEARS',
					'term' => $QuickQuote['term_duration'],
					'TPDDisabilityDefinition' => 'Any'
				);
				array_push($benefitFPH,$benefit);
				if($QuickQuote['type_of_policy'] == '2') {
					array_push($benefitSPH,$benefit);
				}
				
			}
		}
		$lifeFPH['Benefits'] = $benefitFPH;
		$lifeSPH['Benefits'] = $benefitSPH;
		
		$requestMagnun = array(
			'magnumCaseUUID' => $magnumCaseUUID,
			'LifeFPH' => $lifeFPH,
			'LifeSPH' => $lifeSPH,
			'LifeRole' => 'Adult Life',
			'CoverPurpose' => 'Personal / family protection',
			'displayName' => 'Dividend Term Life with Critical Illness'
		);
		//pr($requestMagnun);die('SS');
		$Res = $this->magnumBoot->getToken($requestMagnun);
		$Token = '';
		if($Res){
			$magnumCaseUUID = $Res['magnumCaseUUID'];
			$Token = $Res['Token'];
		}
		//pr($Res);die('STOP');
		$this->smarty->assign('magnumCaseUUID', $magnumCaseUUID);
		$this->smarty->assign('Token', $Token);
		parent::admin_clientRiskDetails($allQuestionGroupIds);
    }

	protected function includeFilesCustom($tpl)
	{
		$this->includeFiles['tpl'][0] = 'site_header.tpl';
		$this->includeFiles['tpl'][1] = 'site_leftpanel.tpl';
		$this->includeFiles['tpl'][2] = $tpl . '.tpl';
		$this->includeFiles['tpl'][3] = 'site_rightpanel.tpl';
		$this->includeFiles['tpl'][4] = 'site_footer.tpl';
		parent::createOutput('clients', $this->includeFiles);
	}
	
	function admin_startQuote() 
	{
		
		$termYears = array();
		for($i=10;$i<=40;$i++){
			$termYears[$i] = $i;
		}
		$this->smarty->assign('termYears', $termYears);
		parent::admin_startQuote();
	}

	function admin_productDurationCustom($selectOptionFlag = 0)
	{
		if ($this->request['selectOptionFlag'] > 0)
			$selectOptionFlag = $this->request['selectOptionFlag'];
		else
			$selectOptionFlag = 0;
		$productDuration = array();
		if ($this->request['product_id'] != '')
			$GLOBALS['cfn']->isNumericCheck($this->request['product_id']);
		$productId = $this->request['product_id'];
		$term_duration = $this->request['term_duration'];
		$durations = $this->modelObjCustom->getProductDurationCustom($productId);

		$defaultCheckTearm = $GLOBALS['cfn']->getValueFromTable(CORE_CONFIGURATION_CLIENT, 'config_code', 'defaultTearm', 'config');
		if (count($durations) > 1 && $selectOptionFlag == 0) {
			// T021169 [START]
			//$productDuration[0] = array(0 => 0, 1 => 'Please Select Term');
			$productDuration[0] = array(0 => 0, 1 => 'Please Select ' . POLICY_TERM);
			// T021169 [END]

			$flag = 0;
		} else {
			$flag = 1;
			$age=$this->request['age'];

			if($age>0){
				$client_id=$this->request['clientId'];
			  
				$client_id=$GLOBALS['cfn']->decryptIt($this->request['clientId']);			   
				$get_client_dob = $this->modelObjCustom->getClientDob($client_id);
				$client_dob='';
				if($get_client_dob && $get_client_dob!='0000-00-00'  ){
					$client_dob = date("d/m/Y", strtotime( $get_client_dob));
				}			   
				$client_dob_time='00:00:01';
				$date = $client_dob;
				$time = $client_dob_time;
				$term_duration = $age;

			}else{
				$date = $this->request['dateToday'];
				$time = $this->request['timeToday'];
			}
			$date = $GLOBALS['cfn']->dateConvert($date);
			$termId = $durations[0]['product_version_duration_id'];
			if ($termId != '')
				$GLOBALS['cfn']->isNumericCheck($termId);

			$datetime = $date . ' ' . $time;
			$tearmArr = $this->modelObjCustom->getTermDurationDateCustom($termId, $term_duration, $datetime);
			
		}
		foreach ($durations as $key => $value) {
			$productDuration[$key + 1] = array(0 => $value['product_version_duration_id'], 1 => $this->arrDuration[$value['flag']]);

			$tearmName = $this->arrDuration[$value['flag']];
			if ($tearmName == 'Yearly' && $defaultCheckTearm == 1) {
				$selected = $value['product_version_duration_id'];
			} elseif ($tearmName == 'Monthly' && $defaultCheckTearm == 2) {
				$selected = $value['product_version_duration_id'];
			} elseif ($tearmName == 'Other' && $defaultCheckTearm == 3) {
				$selected = $value['product_version_duration_id'];
			} elseif ($defaultCheckTearm == 0 || $defaultCheckTearm == '') {
				$selected = '';
			}
		}

		$readableFlag = 0;
		//echo "<br>productId=$productId";
		//echo "<br>AUS_PRODUCT_ID=".AUS_PRODUCT_ID;
		
		if (defined('AUS_PRODUCT_ID') && $productId == AUS_PRODUCT_ID) {
			$timezoneAUSConfig = $GLOBALS['cfn']->getValueFromTable(CORE_MASTER_MASTER_PRODUCT_CONFIGCODE, 'product_code', AUS_PRODUCT_CODE, 'config');
			$actionArray = explode(':', $timezoneAUSConfig);
			$defaultTimezone = $actionArray[0];
			$readableFlag = $actionArray[1];
			$tearmArr['timeEnd'] = $GLOBALS['DEFAULT_AUS_END_TIME'];
		} else if (defined('UK_PRODUCT_ID') && $productId == UK_PRODUCT_ID) {
			$timezoneUKConfig = $GLOBALS['cfn']->getValueFromTable(CORE_MASTER_MASTER_PRODUCT_CONFIGCODE, 'product_code', UK_PRODUCT_CODE, 'config');
			$actionArray = explode(':', $timezoneUKConfig);
			$defaultTimezone = $actionArray[0];
			$readableFlag = $actionArray[1];
			$tearmArr['timeEnd'] = $GLOBALS['DEFAULT_END_TIME'];
		} else if (defined('US_PRODUCT_ID') && $productId == US_PRODUCT_ID) {
			$timezoneUSConfig = $GLOBALS['cfn']->getValueFromTable(CORE_MASTER_MASTER_PRODUCT_CONFIGCODE, 'product_code', US_PRODUCT_CODE, 'config');
			$actionArray = explode(':', $timezoneUSConfig);
			$defaultTimezone = $actionArray[0];
			$readableFlag = $actionArray[1];
			$tearmArr['timeEnd'] = $GLOBALS['DEFAULT_END_TIME'];
		} else {
			$defaultTimezone = $this->modelObjCustom->getDefaultTimezone();
		}
		$value = '#####' . $selected . '#####' . $flag . '#####' . $tearmArr['dateEnd'] . '#####' . $tearmArr['timeEnd'] . '#####' . $tearmArr['termDuration'] . '#####' . $defaultTimezone . '#####' . $readableFlag;
		$this->smarty->assign('name', 'term_id');
		$this->smarty->assign('select', $selected);
		$this->smarty->assign('dropDown', $productDuration);
		$this->smarty->assign('selectVal', $value);
		$this->includeFiles['tpl'][0] = 'site_dropdown_options.tpl';
		parent::createOutput('clients', $this->includeFiles);
		exit;
	}
	
	function admin_getPremiumBreakup()
	{
		$eventOrRiskEventId = $this->request['event_or_risk_event_id'];
		$ratingType = $this->modelObj->getCoverTypeRatingMethod($eventOrRiskEventId);
		if($ratingType != '3' && $ratingType != '4') 
		{
			$this->admin_getPremiumBreakupCustom();		
			exit();
		}
		else
		{			
			$fromType = $this->request['fromType'];
			$dispTyp = $this->request['dispTyp'];
			$bEventId = $this->modelObj->getBusinessFmRisk($eventOrRiskEventId);
			$businessEventDetails = $this->modelObj->getBusinessIdWrtBusinessEventId($bEventId);
			$businessId = $businessEventDetails['business_id'];
			$isDeclarationProduct = $GLOBALS['cfn']->fetchProductType($businessId);
			$clientId = $businessEventDetails['clients_id'];
			
			$discountDetails = $this->modelObj->getDiscountLoading($eventOrRiskEventId);
			$breResponse = $this->modelObjCustom->getBreResponse($eventOrRiskEventId);
			$currency_id = $businessEventDetails['currency_id'];
			$currencyShortCode = $GLOBALS['cfn']->getValueFromTable(CORE_DETAILS_CURRENCY_TABLE, 'currency_id', $currency_id, 'short_code');
			
			$breResponse = str_replace('_CUR',$currencyShortCode,$breResponse);
			
			if($breResponse['GQRSD_owflag_yesNo'] == 1 )
			{
				$breResponse = str_replace('[VAR_OverWrittenPremium]','<span style="text-align:right; width:100%"><strong>Over Written Premium</strong></span>',$breResponse);
			}
			else
			{
				$breResponse = str_replace('[VAR_OverWrittenPremium]','',$breResponse);
			}
			
			//[T025457] MTA on existing policy - adding new property cannot update the rates
			//if($dispTyp == 'view' || $fromType == 'MTA')
			if($dispTyp == 'view')
			{
				$oldInputText = '<input type="text"';
				$newInputText = '<input type="text" readonly="readonly" ';
				$breResponse['response_data'] =  str_replace($oldInputText,$newInputText,$breResponse['response_data']);
				
				$oldRadioText = 'type="radio"';
				$newRadioText = 'type="radio" disabled="disabled"';
				$breResponse['response_data'] =  str_replace($oldRadioText,$newRadioText,$breResponse['response_data']);
				
				$breResponse['response_data'] = str_replace('id="breResBtn"','id="breResBtn" style="display:none;"', $breResponse['response_data']);
			}
			$this->smarty->assign('responseData', $breResponse['response_data']);
			$this->includeFiles['tpl'][0] = 'brePremiumBreakupHtmlCustom.tpl';
			
			parent::createOutput('clients', $this->includeFiles);
		}
	}

	function admin_getPremiumBreakupCustom()
	{
		$eventOrRiskEventId = $this->request['event_or_risk_event_id'];
		$fromType = $this->request['fromType'];
		$dispTyp = $this->request['dispTyp'];
		$bEventId = $this->modelObj->getBusinessFmRisk($eventOrRiskEventId);
		$businessEventDetails = $this->modelObj->getBusinessIdWrtBusinessEventId($bEventId);
		$businessId = $businessEventDetails['business_id'];
		$proRataConfiguration = $businessEventDetails['proRataConfiguration'];
		$policy_start_date = $businessEventDetails['policy_start_date'];
		$policy_expiry_date = $businessEventDetails['policy_expiry_date'];
		$isDeclarationProduct = $GLOBALS['cfn']->fetchProductType($businessId);
		$clientId = $businessEventDetails['clients_id'];
		$ratingMethod = $this->modelObj->getCoverTypeRatingMethod($eventOrRiskEventId);
		$discountDetails = $this->modelObj->getDiscountLoading($eventOrRiskEventId);
		$this->smarty->assign('ratingMethod', $ratingMethod);
		if($ratingMethod < 3) {
			$premiumBreakupResults = $this->modelObjCustom->getQueResDataWithRate($eventOrRiskEventId);
			$this->smarty->assign('premiumBreakupResults', $premiumBreakupResults);
		}
		else {
			$quoteRiskDetails = $this->modelObj->getQuoteRiskDetails($eventOrRiskEventId,1);
			$this->smarty->assign('premiumBreakuphtml', $quoteRiskDetails['premium_breakup_html']);
		}
		
		// [I004379] SLIP Product issues
		$signedPerc = 1;
		$riskEventInfo = $GLOBALS['db']->dataArray(CORE_DETAILS_RISK_EVENT, "*", "risk_event_id = $eventOrRiskEventId","1");
		if($riskEventInfo && $riskEventInfo['0']['business_basis'] == '1') {
			$signedPerc = 0;
			$writtenDetails = $this->modelObjOpenMarket->getHereon($eventOrRiskEventId,$bEventId);
			if($writtenDetails) {
				if($writtenDetails['signed_line_percentage'] == "0" || $writtenDetails['signed_line_percentage'] == ""){
					$signedPerc = $writtenDetails['actual_estimate_line'] ;
				}
				else{
					if($writtenDetails['signed_of'] == '1')
						$signedPerc = $writtenDetails['signed_line_percentage'];
					else
						$signedPerc = (($writtenDetails['signed_line_percentage'] * $writtenDetails['signed_order_percentage'])/100);
				}				   
			}
		}
		$this->smarty->assign('signedPerc', $signedPerc);
		$this->smarty->assign('writtenDetails', ($writtenDetails ? $writtenDetails : ''));	  
		////////////////
		//pr($premiumBreakupResults);exit;

		$this->smarty->assign('clientId', $GLOBALS['cfn']->encryptIt($clientId));
		$this->smarty->assign('businessId', $GLOBALS['cfn']->encryptIt($businessId));
		$this->smarty->assign('bEventId', $GLOBALS['cfn']->encryptIt($bEventId));
		$this->smarty->assign('riskEventId', $GLOBALS['cfn']->encryptIt($eventOrRiskEventId));

		$this->smarty->assign('ratingMethod', $ratingMethod);
		$this->smarty->assign('fromType', $fromType);
		$this->smarty->assign('isDeclarationProduct', $isDeclarationProduct);
		$this->smarty->assign('discountDetails', $discountDetails);
		$this->smarty->assign('dispTyp', $dispTyp);
		$this->smarty->assign('proRataConfiguration', $proRataConfiguration);

		if($proRataConfiguration != '1') {
			if($proRataConfiguration == '3')
				$premiumRateTerm = $this->modelObjBre->calculateTermDurationForLeapYear($policy_start_date,$policy_expiry_date);
			else
				$premiumRateTerm = 365;
			
			$policy_start_date = $GLOBALS['cfn']->formatDate($policy_start_date);
			$policy_start_date = $GLOBALS['cfn']->dateConvert($policy_start_date);
			$policy_start_date = date_create($policy_start_date);

			$policy_expiry_date = $GLOBALS['cfn']->formatDate($policy_expiry_date);
			$policy_expiry_date = $GLOBALS['cfn']->dateConvert($policy_expiry_date);
			$policy_expiry_date = date_create($policy_expiry_date);

			$policyTerm = date_diff($policy_start_date, $policy_expiry_date);
			$policyTermDuration = ($policyTerm->days)+1; //[T026677] MTA - Policy Period Extension missing from reason list

			$prorataFactor = ($policyTermDuration)/($premiumRateTerm);
			
			//$this->smarty->assign('prorataFactor', $prorataFactor);
			$this->smarty->assign('policyTermDuration', $policyTermDuration);
			$this->smarty->assign('premiumRateTerm', $premiumRateTerm);
		}
		
		$this->includeFiles['tpl'][0] = 'premiumBreakupHtmlCustom.tpl';
		parent::createOutput('clients', $this->includeFiles);

		//Event Log Created By Bishakha Banerjee, 12/06/2018
		$fieldContext = array();
		$fieldContext[2] = $businessId;
		$fieldContext[3] = $clientId;

		$GLOBALS["eventLog"]->subEventLogEntry(8, "Premium Breakup Display", $fieldContext, $quoteRefNo['quote_no']);
	}

	function admin_getPremiumBreakupRecalculate()
	{
		$eventOrRiskEventId = $this->request['event_or_risk_event_id'];
		$breResponse = $this->modelObjCustom->getBreResponse($eventOrRiskEventId);
		
		$bEventId = $this->modelObj->getBusinessFmRisk($eventOrRiskEventId);
		$businessEventDetails = $this->modelObj->getBusinessIdWrtBusinessEventId($bEventId);
		$currency_id = $businessEventDetails['currency_id'];
		$currencyShortCode = $GLOBALS['cfn']->getValueFromTable(CORE_DETAILS_CURRENCY_TABLE, 'currency_id', $currency_id, 'short_code');
		
		$breResponseData = str_replace('_CUR',$currencyShortCode,$breResponse['response_data']);
		echo $breResponseData;
		exit();

	}

	function admin_updateRating()
	{
		$this->modelObjBre->modelObjClients = $this->modelObj;
		
		$request = $this->request;
		$riskEventId = $request['newriskEventId'];
		$newFromType = $request['newFromType'];
		$breFunName = $request['breFunName'];

		$sectionVersionId = $this->modelObjCustom->getSectionVersionId($riskEventId);
		$businessDetails = $this->modelObjBre->getBusinessIdWrtBusinessEventId(0,$riskEventId);
		$busEventId = $businessDetails['business_event_id'];
		$business_id = $GLOBALS['cfn']->encryptIt($businessDetails['business_id']);

		//pr($request);
		//die('AA');
		$postRequest = array();
		$postRequest = $request;
		//Get section version id and from type and assign into the request array
		$postRequest['section_version_id'] = $sectionVersionId;
		$postRequest['fromType'] = $newFromType;
		$postRequest['business_id'] = $business_id;
		$postRequest['clickEvent'] = 'update';

		//Get All Question Value
		$arrQuestionCodeWithValue = $this->modelObjBre->getAllQuestionValue($riskEventId,$postRequest);

		//Holding Breakup Arrays
		$arrQuestionCodeWithValue = $this->modelObjBre->buildHoldingBreakupArrays($arrQuestionCodeWithValue,$riskEventId,'',0);

		$arrQuestionCodeWithValue['riskEventId'] = $riskEventId;
		
		//Get Tax Details
		$taxRateDetails =  $this->modelObjBre->getAllTaxRateDetails($arrQuestionCodeWithValue, $riskEventId);

		$isNewQuote = false;
		if($riskEventId == '' || $riskEventId == '0') {
			if($request['action'] == '')
				$isNewQuote = true;
		}

		include(CURR_SITE_DIR.'quoteEngine/quoteEngine.php');
		$qE = new quoteEngine();

		$countryId = $this->modelObjBre->getQuoteCountryId($riskEventId,'');

		$arrQuestionCodeWithValue['countryCode'] = $this->modelObjBre->taxCountryCode;

		$arrQuestionCodeWithValue['taxRateDetails'] = $taxRateDetails;
		
		$quickQuoteBre = array();
		$premiumBaQuestionGroup = $qE->$breFunName($arrQuestionCodeWithValue, $postRequest, $quickQuoteBre);

		$policyStartDate = $arrQuestionCodeWithValue['policyStartDate'];

		//echo "before premium =====<br>";
		//pr($premiumBaQuestionGroup);die('STOP');
 
		$groupPremWithAmountType = $this->modelObjBre->distributePremiumAmongEntitiesNew($premiumBaQuestionGroup,$arrQuestionCodeWithValue,$riskEventId); 
		
		$isDeclarationProduct = $GLOBALS['cfn']->fetchProductType($businessDetails['business_id']);
		//Calculated pro-rata premium both amoun type 2,3
		if($arrQuestionCodeWithValue['transactionType'] == 'MTA'){
			$proratedPrem = $this->modelObjBre->getProrataPrem($arrQuestionCodeWithValue,$groupPremWithAmountType,$isDeclarationProduct);
			$groupPremWithAmountType = $proratedPrem;
		}										 
		$this->modelObjBre->modelObjClients = $this->modelObj;

		$riskPremNow = $this->modelObjBre->saveQuote($groupPremWithAmountType,$riskEventId,$busEventId,$policyStartDate,$arrQuestionCodeWithValue['transactionType'],$isNewQuote,$sectionVersionId);

		$omr_risk_id = 0 ;
		$this->modelObjBre->sumGroupAndRiskEventWisePremium($riskEventId, $busEventId, $newFromType, 1, $omr_risk_id);
		
		$condition = "business_event_id = '".$busEventId."' AND amount_type = '3' ";
		$quoteDetails = $GLOBALS['db']->dataArray(CORE_DETAILS_QUOTE_EVENT, "quote_event_id", $condition, "quote_event_id DESC LIMIT 1");
		$quoteEventId = $quoteDetails[0]['quote_event_id'] ;  
			
		$this->modelObjBre->getEntityFeeTaxWiseBreakupWrtQevent($quoteEventId);
		echo json_encode($riskPremNow);die;
	}

	function submitSale()
	{
			$dataArray = array(
			'submit_sale' => 1,
			'submit_sale_date' => date('Y-m-d h:i:s')			
		);
		$businessId=$this->request['businessId'];
		$businessId = $GLOBALS['cfn']->decryptIt($businessId);
		$condition = "business_id = '".$businessId."'";
		$GLOBALS["db"]->sqlUpdate(CORE_DETAILS_BUSINESS, $dataArray, $condition);
		$message = $GLOBALS['cfn']->successErrorMessage(1017);
		$_SESSION['msg'] = $message;
		$res['SUCCESS'] = 1;
		
		echo json_encode($res);
		exit; 

	}
	//Remove Quotes
	function removeQuotes(){
	$quickquoteResponseId = $this->request['quickquoteResponseId'];
	//echo $quickquoteResponseId;exit;
	$removeQuoteData = $this->modelObjCustom->deleteQuickSingleQuoteData($quickquoteResponseId);
	}
	function admin_paymentDetails()
	{
		$encryptedBusinessEventId = $this->request['businessEventId'];
        $businessEventId = $GLOBALS['cfn']->decryptIt($encryptedBusinessEventId);
        $businessEventDetails = $this->modelObjCustom->getBusinessIdWrtBusinessEventId($businessEventId);
		//pr($businessEventDetails);exit;
        $quickQuoteData = $this->modelObjCustom->getQuickSingleQuoteData($businessEventId);
        $businessEventClientData = $this->modelObjCustom->getBusinessEventClient($businessEventId);
        $product_name = $businessEventDetails['product_name'];
        $submit_sale = $businessEventDetails['submit_sale'];
        $submit_sale_date = $businessEventDetails['submit_sale_date'];
		
        $premium_frequency = $businessEventDetails['premium_frequency']==1?'Monthly':'Annualy';
        $policy_type = $quickQuoteData['policy_type']==1?'Term Life':'Term Life with Critical illness';
        $suminsured = $quickQuoteData['suminsured']!= ''?number_format($quickQuoteData['suminsured'],'0','.',','):'0.00';
        $preferred_payment_day = $businessEventClientData['preferred_payment_day'];
        $first_payment_date = $businessEventClientData['first_payment_date'];
        $policy_start_date = $quickQuoteData['policy_start_date'];
		$policy_dateArr=explode(" ",$policy_start_date);
		$policy_date=explode("-",@$policy_dateArr[0]);
		if(@$policy_date[2] !='00' && @y_date[1] !='00' && @$policy_date[0] !='0000'){
			$policy_start_date = $GLOBALS['cfn']->formatDate($policy_start_date);
		}else{
			$policy_start_date= date('d/m/Y');
		}
		$defaultStartDate = date('d/m/Y');
		$days_options = array();
		for($i=1;$i<=28;$i++){
			$days_options[str_pad($i,2,"0",STR_PAD_LEFT)] = str_pad($i,2,"0",STR_PAD_LEFT);
		}
		
		$this->smarty->assign('submit_sale', $submit_sale);
		$this->smarty->assign('submit_sale_date', $submit_sale_date);
		$this->smarty->assign('product_name', $product_name);
		$this->smarty->assign('policy_type', $policy_type);
		$this->smarty->assign('suminsured', $suminsured);
		$this->smarty->assign('premium_frequency', $premium_frequency);
		$this->smarty->assign('preferred_payment_day', $preferred_payment_day);
		$this->smarty->assign('first_payment_date', $first_payment_date);
		$this->smarty->assign('policy_start_date', $policy_start_date);
		$this->smarty->assign('defaultStartDate', $defaultStartDate);
		$this->smarty->assign('days_options', $days_options);
		parent::admin_paymentDetails();
	}

	function admin_paymentdatecalculation()
	{
		$res = array('SUCCESS' => 0, 'DATA' => '');
		$preferred_payment_day = $_POST['preferred_payment_day'];
		$bankdetails = $_POST['bankdetails'];
		
		$businessEventId = $GLOBALS['cfn']->decryptIt($_POST['bEventId']);
		$businessEventDetails = $this->modelObjCustom->getBusinessIdWrtBusinessEventId($businessEventId);
		$policy_start_date = $businessEventDetails['policy_start_date'];
		$policy_start_date = date('Y-m-d', strtotime($policy_start_date));
		$application_submission_date = date('Y-m-d');
		$calculationDate = '';
		if($policy_start_date > $application_submission_date) {
			$calculationDate = $policy_start_date;
		}
		else {
			$calculationDate = $application_submission_date;
		}
		
		$cDay = date('d', strtotime($calculationDate));
		$cMonth = date('m', strtotime($calculationDate));
		$cYear = date('Y', strtotime($calculationDate));
		if($cDay>$preferred_payment_day) {
			if($cMonth == 12) {
				$cMonth = '01';
				$cYear++;
			}
			else {
				$cMonth++;
			}
		}
		$tempfirstpaymentdate = date('Y-m-d', strtotime($cYear.'-'.str_pad($cMonth,2,"0",STR_PAD_LEFT).'-'.str_pad($preferred_payment_day,2,"0",STR_PAD_LEFT)));	
		//echo "<br>calculationDate=$calculationDate";
		//echo "<br>tepmfirstpaymentdate=$tempfirstpaymentdate";
		$calculationDate = date_create($calculationDate);
		$tempfirstpaymentdate = date_create($tempfirstpaymentdate);
		$totalDaysDiffObj = date_diff($calculationDate, $tempfirstpaymentdate);
		$totalDaysDiff = $totalDaysDiffObj->days + 1;
		
		//echo "<br>totalDaysDiff=$totalDaysDiff";
		if($totalDaysDiff>14){
			$firstpaymentdate = $tempfirstpaymentdate->format('Y-m-d');
		}
		else {
			$calculationDateObj = date_add($calculationDate, date_interval_create_from_date_string("14 days"));
			$firstpaymentdate = $calculationDateObj->format('Y-m-d');
			
			/*$cDate = date('Y-m-d', strtotime($calculationDate));
			$date = new DateTime($cDate);
			$date->add(new DateInterval('P14D'));
			$firstpaymentdate = $date->format('Y-m-d');*/
		}
		$dataArray = array(
			'preferred_payment_day' => $preferred_payment_day,
			'first_payment_date' => $firstpaymentdate,
			'bank_details_id' =>$bankdetails
			
		);
	
		//pr($dataArray);
		$condition = "business_event_id = '".$businessEventId."'";
		$GLOBALS["db"]->sqlUpdate(CORE_DETAILS_BUSINESS_EVENT_CLIENT, $dataArray, $condition);
		//---------------
		
		$returnData = array(
			'firstpaymentdate' => $GLOBALS['cfn']->formatDate($firstpaymentdate),
			'bank_details_id' => $bankdetails
		);
		$res['SUCCESS'] = 1;
		$res['DATA'] = $returnData;
		echo json_encode($res);
		exit;
	}
	
	function sendQuickQuote()
	{
		$res = array('SUCCESS' => 0);
		$businessEventId = $GLOBALS['cfn']->decryptIt($_POST['businessEventId']);
		
		$businessDetails = $this->modelObjCustom->getBusinessDetailsUsingBusinessEventId($businessEventId);
		
		$mainPolicyHolderDetailsSql = "SELECT MG.gender_id, MG.gender, MT.title_id, MT.title,  A.firstname, A.surname, A.email_id, A.dob, A.smoker_id, A.employment_status_id FROM ".CORE_DETAILS_BUSINESS_EVENT_CLIENT." AS A LEFT JOIN ".CORE_MASTER_GENDER." MG ON MG.gender_id = A.gender_id LEFT JOIN ".CORE_MASTER_TITLE." MT ON MT.title_id = A.title_id WHERE A.business_event_id = $businessEventId";
		$mainPolicyHolderDetailsQuery = $GLOBALS['db']->sqlQuery($mainPolicyHolderDetailsSql);
		$mainPolicyHolderDetailsResult = $GLOBALS['db']->sqlFetchRow($mainPolicyHolderDetailsQuery);
		
		if(!empty($mainPolicyHolderDetailsResult)) {
			$mainPolicyHolderDetailsName = $mainPolicyHolderDetailsResult['title'].' '.$mainPolicyHolderDetailsResult['firstname'].' '.$mainPolicyHolderDetailsResult['surname'];
			
			//Send Mail Start---
			$quickQuoteMail = $this->modelObjCustom->getQuickQuoteMail();
			$mail_subject = $quickQuoteMail['mail_subject'];
			$mail_body = $quickQuoteMail['mail_body'];
			//Variable Replacement
			$quickQuoteData = $this->modelObjCustom->getQuickQuoteData($businessEventId);			
			$mail_body = str_replace('[VAR_CURRENCY]', 'GBP', $mail_body);
			$mail_body = str_replace('[VAR_QUOTE_CLIENTS]',$mainPolicyHolderDetailsName , $mail_body);
			$mail_body = str_replace('[VAR_QUOTE_NUMBER]',$businessDetails['quote_no'] , $mail_body);
			$mail_body = str_replace('[VAR_INDICATIVE_PREMIUM]',$quickQuoteData['premium'] , $mail_body);
			
			//Signatures on system emails
			$assignedUnderwriterUserID = $_SESSION['MI_USERID'];
			if(isset($businessDetails['assignedTo']) && $businessDetails['assignedTo'] > 0) {
				$assignedUnderwriterUserID = $businessDetails['assignedTo'];
			}
			$staffDetails = array();
			$staffDetails = $this->modelObjCustom->userSignature($assignedUnderwriterUserID);
			$underwriterName=$staffDetails['firstName'].' '.$staffDetails['lastName'];
			$underwriterSign=$staffDetails['user_sign'];
			$arrayFile 			= explode("/",$staffDetails['user_sign_file_path']);
			$signatureStaff    = end($arrayFile);
			if($signatureStaff!='') {
				$imgHtml = '<img src="' .ABSOLUTE_PATH. 'uploadedFiles/Signature/'.$signatureStaff.'" alt="signature" style="height:auto; width:130px">';
				$mail_body = str_replace('[VAR_CUSTOM_SIGNATURE]', $imgHtml, $mail_body);		
			}
			else if($underwriterSign!='') {
				$mail_body = str_replace('[VAR_CUSTOM_SIGNATURE]', $underwriterSign, $mail_body);
			}
			else {
				$mail_body = str_replace('[VAR_CUSTOM_SIGNATURE]', $underwriterName, $mail_body);
			}
			//---------------------
			$policy_logo = '<img src="' .ABSOLUTE_PATH. 'uploadedFiles/Signature/policy_logo.png" alt="policyLogo" style="height:110px; width:auto;">';
			$mail_body		= 	str_replace('[VAR_POLICY_LOGO]', $policy_logo, $mail_body) ;
			//------------------
			$toEmail = $mainPolicyHolderDetailsResult['email_id'];
			$buffer = array();
			$buffer['settings_id'] 		= '1';
	        $buffer['sender_ip'] 		= $_SERVER['REMOTE_ADDR'];
	        $buffer['posted_by'] 		= $_SESSION['MI_USERID'];
	        $buffer['email_to'] 		= $toEmail;
	        $buffer['email_subject'] 	= $mail_subject;
	        $buffer['email_body'] 		= $mail_body;
	        $buffer['attachment'] 		= '';
	        $buffer['file_extension'] 	= '';
	        $bufferId = $this->modelObjCustom->mailBufferInsert($buffer);
			if ($bufferId > 0) {
	            $fieldContext = array();
	             $bufferContext['email_buffer_id'] = $bufferId;
	            $this->modelObjCustom->mailBufferContextInsert($bufferContext);
	        }
	        //Send Mail End---
	        $res['SUCCESS'] = 1;
	        echo json_encode($res);
	        exit;
		}
		else {
			$res['SUCCESS'] = 0;
		}
		echo json_encode($res);
	}
	
/*	function recalculateDate()
	{
		$res = array('SUCCESS' => 0);
		$policy_start_time = '00:00:01';
		$policy_start_date = $GLOBALS['cfn']->dateConvert($this->request['plan_start_date']) . " " . $policy_start_time;

		$businessEventId = $GLOBALS['cfn']->decryptIt($this->request['bEventId']);

		$quickQuoteDetails = $this->modelObjCustom->getQuickQuoteData($businessEventId);
		$quickQuotedateArr=array();
		foreach ($quickQuoteDetails as $key => $value){		
			$policy_end_time='23:59:59';
			$policy_end_day=$GLOBALS['cfn']->dateConvert($this->request['plan_start_date']);	
			$policy_end_days = date('Y-m-d', strtotime($policy_end_day. '+'.$value['term_duration'].' years'));			
			$policy_end_days = date( "Y-m-d", strtotime( $policy_end_days . "-1 day"));
			
			$policy_end_date = $policy_end_days." ". $policy_end_time;		
			
			$quickQuotedateArr['policy_start_date']=$policy_start_date;
			$quickQuotedateArr['policy_end_date']=$policy_end_date;
			$quickQuotedId = $this->modelObjCustom->addEditQuickQuote($quickQuotedateArr,$value['quickquote_response_id']);
		}
		$res['SUCCESS'] = 1;		
		echo json_encode($res);
		exit;
	}
	function recalculatePolicyDate()
	{
		$res = array('SUCCESS' => 0);
		$policy_start_time = '00:00:01';
		$policy_start_date = $GLOBALS['cfn']->dateConvert($this->request['plan_start_date']) . " " . $policy_start_time;

		$businessEventId = $GLOBALS['cfn']->decryptIt($this->request['bEventId']);

		$quickQuoteDetails = $this->modelObjCustom->getQuickQuoteData($businessEventId);
		$quickQuotedateArr=array();
		foreach ($quickQuoteDetails as $key => $value){		
			$policy_end_time='23:59:59';
			$policy_end_day=$GLOBALS['cfn']->dateConvert($this->request['plan_start_date']);	
			$policy_end_days = date('Y-m-d', strtotime($policy_end_day. '+'.$value['term_duration'].' years'));			
			$policy_end_days = date( "Y-m-d", strtotime( $policy_end_days . "-1 day"));
			
			$policy_end_date = $policy_end_days." ". $policy_end_time;		
			
			$quickQuotedateArr['policy_start_date']=$policy_start_date;
			$quickQuotedateArr['policy_end_date']=$policy_end_date;
			$quickQuotedId = $this->modelObjCustom->addEditQuickQuote($quickQuotedateArr,$value['quickquote_response_id']);
		}
		
		$res['SUCCESS'] = 1;		
		echo json_encode($res);
		exit;
	}*/
	

	function callQuickQuoteDividendLifeCoverBre()
	{
		$this->modelObjBre->modelObjClients = $this->modelObj;
		include(CURR_SITE_DIR.'quoteEngine/quoteEngine.php');
		$qE = new quoteEngine();
		$postRequest = $_POST;

		//Add Update Quick Quote CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE
			$businessEventId = $GLOBALS['cfn']->decryptIt($_POST['businessEventId']);		
			$commsn_sacrifice = preg_replace('/[^0-9]/', '', $_POST['comm_sacrfc']);
			$plan_details_counter = $_POST['planDetailsCounter'];
			
			$basePremium = 0;
			$quickQuoteBre = array();
			
			if(isset($plan_details_counter) && count($plan_details_counter) > 0)
			{
				for ($i = 0; $i < $plan_details_counter; $i++)
				{
					$quickQuoteId = $_POST['quickquote_response_id'][$i];
					$type_of_policy = 1;
					if(isset($_POST['type_of_policy_SH'][$i]) && $_POST['type_of_policy_SH'][$i] == '2') {
						$type_of_policy = 2;
					}
					
					$quickQuote =  array();
					$quickQuote['business_event_id'] = $businessEventId;
					$quickQuote['type_of_policy'] = $type_of_policy; 					
					$quickQuote['premium_frequency'] = $_POST['premium_frequency'][$i]; 
					$quickQuote['term_duration'] = $_POST['term_duration'][$i]; 
					$quickQuote['term_duration_age'] = $_POST['term_duration_age'][$i];						
					$quickQuote['type_of_cover'] = $_POST['pd_type_of_cover'][$i];
					$quickQuote['policy_type'] = $_POST['pd_type_of_policy'][$i];
					$quickQuote['suminsured'] = preg_replace('/[^0-9]/', '', $_POST['pd_sum_insured'][$i]);
					$quickQuote['budget'] = $_POST['pd_budget_si'][$i];
					//$quickQuote['waiver_premium'] = $_POST['pd_waiver_prem'][$i];
					$quickQuote['tpd'] = $_POST['pd_tpd'][$i];
					$quickQuote['commsn_sacrifice'] = $commsn_sacrifice;
					//For Fresh/Edit Plan Detail Entry
					$quickQuoteId = $this->modelObjCustom->addEditQuickQuote($quickQuote, $quickQuoteId);
					//$quickQuoteId = quickquote_response_id of tbl_custom_quickquote_response Table
					$quickQuoteBre[$quickQuoteId]['business_event_id'] = $quickQuote['business_event_id'];
				}
			}

			//function callDividendLifeCoverBre($arrQuestionCodeWithValue, $postRequest, $quickQuoteBre) In quoteEngine AND quoteEngineLC2
			$arrQuestionCodeWithValueArr = array();
			$postRequestArr = array();
			$returnVal = $qE->callDividendLifeCoverBre($arrQuestionCodeWithValueArr, $postRequestArr, $quickQuoteBre);
			
			//Update To PLAN Details BRE Return Process has been Done on quoteEngineLC2 
		    $basePremium = $returnVal['basePremium'];
			$finalReturnVal = array('basePremium' => $basePremium);		
			
			//Custom Quote Reference Number Generation
				$optionToQuoteUpdate = array(
					'businessEventId' => $businessEventId,
					'basePremium' => $basePremium,
				);
				$this->modelObjCustom->updateQuote($optionToQuoteUpdate);
			   
				$business_details = $this->modelObjCustom->getBusinessDetailsUsingBusinessEventId($businessEventId);
				$business_id = $business_details['business_id'];
				$quoteRefNo = $this->getQuoteOrPolicyRefNo($business_id, true);
				$policyRefNo = $this->getQuoteOrPolicyRefNo($business_id, false);
				$policyUpdate = array(
						'policy_no' => $policyRefNo,
						'quote_no' => $quoteRefNo
				);
				$GLOBALS["db"]->sqlUpdate(CORE_DETAILS_BUSINESS, $policyUpdate, " business_id = '" . $business_id . "'");
			//Custom Quote Reference Number Generation
		//Add Update Quick Quote CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE
		echo json_encode($finalReturnVal);
		
		exit;
		
	}
	
	function admin_clientPlanDetails($allQuestionGroupIds = '') 
	{
        $form_random_key = $GLOBALS['cfn']->generateRandomKey();
        $GLOBALS['cfn']->addEditFormSubmitKey($form_random_key);
        $this->smarty->assign('form_random_key', $form_random_key);
        
        if (!empty($_SESSION['msg'])) {
            $message = $_SESSION['msg'];
            unset($_SESSION['msg']);
            $this->smarty->assign('message', $message);
        }
        
		$enBusinessEventId = $this->request['businessEventId'];		
        $encryptedClientId = $this->request['clientId'];	
		$client_data = $this->modelObjCustom->getClientData($GLOBALS['cfn']->decryptIt($encryptedClientId)); 
		
        $riskEventId = $this->request['riskEventId'];
        $versionId = $this->request['versionId'];
        $fromType = $this->request['fromType'];
        $bEventId = $GLOBALS['cfn']->decryptIt($enBusinessEventId);
		$business_event_client_data = $this->modelObjCustom->getBusinessEventClientData($GLOBALS['cfn']->decryptIt($enBusinessEventId));
			//pr($business_event_client_data);exit;
		//Lokenath Add code to calculate age
		if($business_event_client_data['dob']>0){
		$client_age = round((time()-strtotime($business_event_client_data['dob']))/(3600*24*365.25));
		}
		
		$additional_client_data = $this->modelObjCustom->getadditionalClientData($bEventId);
		if($additional_client_data['dob']>0){
		$additional_client_age = round((time()-strtotime($additional_client_data['dob']))/(3600*24*365.25));
		}else{
		$additional_client_age=0;
		}
		
		//get plan details fetch by lokenath on 21-07-2022
		$premiumToShow = 0.00;
		$quickQuoteResponseDtls = array();
		if($fromType =='MTA'){
			
			$quickQuoteData = $this->modelObjCustom->getQuickSingleQuoteData($bEventId);
			$premiumToShow=$quickQuoteData['premium'];
			array_push($quickQuoteResponseDtls, $quickQuoteData);
		}else{

			$quickQuoteResponseDtls = $this->modelObjCustom->getQuickQuoteData($bEventId);
			
			foreach ($quickQuoteResponseDtls as $key => $value) {
			
				if($value['premium']!='') {
					$premiumToShow += $value['premium'];
					
				}
			}
		
		}
		$premiumToShow = number_format($premiumToShow,2,".",",");
		//pr($quickQuoteResponseDtls);die('AA');
		$this->smarty->assign('quickQuoteResponseDtls', $quickQuoteResponseDtls);
		$this->smarty->assign('premiumToShow', $premiumToShow);
		
		$riskEventPremium = $this->modelObjCustom->getRiskEventPremium($bEventId);
		//pr($riskEventPremium);die('STOP');
		if(isset($riskEventPremium[0]) && !empty($riskEventPremium[0])) {
			$riskEventId = $riskEventPremium[0]['risk_event_id'];
        	$versionId = $riskEventPremium[0]['section_version_id'];
		}
		
		$planDetaisContinueUrl = WEB_PATH . 'clients/admin_clientRiskDetails/businessEventId/' . $enBusinessEventId . '/clientId/' . $encryptedClientId . '/riskEventId/' . $riskEventId . '/versionId/' . $versionId . '/fromType/' . $fromType;
		
		
		$businessEventDetails = $this->modelObjCustom->getBusinessIdWrtBusinessEventId($bEventId);
        $businessId = $businessEventDetails['business_id'];
        $quoteDetails = $this->modelObjCustom->getBusinessDetails($businessId);
        $isDeclarationProduct = $this->modelObjCustom->isDeclarationProduct($businessEventDetails['product_id']);
        
        
        $clientId = $GLOBALS['cfn']->decryptIt($encryptedClientId);
        $clientDetails = $this->modelObjCustom->fetchClients($clientId);
		//pr($clientDetails)
			
		
		$termYears = array();
		for($i=5;$i<=40;$i++){
			$termYears[$i] = $i;
		}
		//pr($termYears);exit;
		//$quoteRefNo = $this->modelObj->getRefNo($businessId, $bEventId);
		$quoteRefNo['quote_no'] = $quoteDetails['quote_no'];
		$coverName = $this->modelObjCustom->getCoverNameBySectionVersionId($GLOBALS['cfn']->decryptIt($versionId));
		$this->smarty->assign('coverName', $coverName);
		$this->smarty->assign('termYears', $termYears);
		$this->smarty->assign('clientAge', $client_age);
		$this->smarty->assign('additionalClientAge', $additional_client_age);	
		$this->smarty->assign('quoteRefNo', $quoteRefNo);
        	$this->smarty->assign('quoteDetails', $quoteDetails);
        	$this->smarty->assign('isDeclarationProduct', $isDeclarationProduct);
        	$this->smarty->assign('riskEventPremium', $riskEventPremium);
        	$this->smarty->assign('clientDetails', $clientDetails);
		$this->smarty->assign('planDetaisContinueUrl', $planDetaisContinueUrl);
		//$this->smarty->assign('businessEventId', $enBusinessEventId);
		$this->smarty->assign('enBusinessEventId', $enBusinessEventId);
		$this->smarty->assign('clientId', $encryptedClientId);
		$this->smarty->assign('riskEventId', $riskEventId);
		$this->smarty->assign('versionId', $versionId);
		$this->smarty->assign('fromType', $fromType);
		$this->smarty->assign('client_data', $client_data);
		$this->smarty->assign('additional_client_data', $additional_client_data);
		$this->smarty->assign('businessEventDetails', $businessEventDetails);
		
		$this->includeFiles('planDetails');
	}
}
?>
