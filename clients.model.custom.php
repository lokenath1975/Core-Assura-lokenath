<?php
class clientsModelCustom
{
	function updateBusinessEventRiskForApplicant($businessEventId, $riskEventId, $dataToSave)
	{
		$cond = "business_event_id = $businessEventId AND risk_event_id = $riskEventId";
		$GLOBALS["db"]->sqlUpdate(CORE_DETAILS_BUSINESS_EVENT_RISK, $dataToSave, $cond);
		return true;
	}

	function getCoverVersionId($riskEventId)
	{
		$resultArray = array();
		$Sql = "SELECT tcdr.section_version_id FROM `".CORE_DETAILS_RISK_EVENT."` tcdre". 
				" LEFT JOIN  `".CORE_DETAILS_RISK."` tcdr ON tcdre.risk_id = tcdr.risk_id". 
				" WHERE tcdre.risk_event_id = ".$riskEventId;
		$query = $GLOBALS["db"]->sqlQuery($Sql);
		$resultArray = $GLOBALS["db"]->sqlFetchRow($query);
		return $resultArray;
	}

	function fetchDetails($table, $condition)
	{
		$sql = "SELECT * FROM " . $table . " WHERE  $condition ";
		$result = $GLOBALS['db']->sqlQuery($sql);
		$row = $GLOBALS['db']->sqlFetchRowsetAssoc($result);
		return $row;
	}
	
	function getBusinessDetails($businessId)
	{
		$resultArray = array();
		$Sql = "SELECT tcdb.*, tcdpb.product_id, tcdpb.product_code, tcdpb.product_name, tcdct.short_code, tcdet.display_name, tcdetB.display_name AS brokerName, tcdc.clients_id, CONCAT(tu.firstName, ' ', tu.lastName) AS user_full_name
				FROM `".CORE_DETAILS_BUSINESS."` tcdb". 
				" LEFT JOIN  `".CORE_DETAILS_CURRENCY_TABLE."` tcdct ON tcdb.currency_id = tcdct.currency_id". 
				" LEFT JOIN  `".CORE_DETAILS_PRODUCT_BUILDER."` tcdpb ON tcdb.product_id = tcdpb.product_id". 
				" LEFT JOIN  `".CORE_DETAILS_CLIENTS."` tcdc ON tcdb.clients_id = tcdc.clients_id". 
				" LEFT JOIN  `".CORE_DETAILS_ENTITY_TABLE."` tcdet ON tcdc.entity_id = tcdet.entity_id". 
				" LEFT JOIN  `".CORE_DETAILS_ENTITY_TABLE."` tcdetB ON tcdb.broker_id = tcdetB.entity_id". 
				" LEFT JOIN  `".CORE_DETAILS_USER."` tu ON tcdb.added_by = tu.user_id". 
				" WHERE tcdb.business_id = ".$businessId." AND tcdct.status = '1'";
		$query = $GLOBALS["db"]->sqlQuery($Sql);
		$resultArray = $GLOBALS["db"]->sqlFetchRow($query);
		return $resultArray;
	}

	function isAnySignedProductVersionAvailable($productId)
	{
		$cnt = $GLOBALS['db']->isAnySignedProductVersionAvailable($productId);
		return $cnt;
	}

	function getProductDurationCustom($productId,$policyStartDate = '')
	{
		$cnt = $this->isAnySignedProductVersionAvailable($productId);
		$condition = "";
		$durationCondition = "1";
		if ($cnt > 0) {
			$condition = " AND `status` = '3'";
		}
		if($policyStartDate != '')
		{
			$polQutLinkStat = $this->polQuoteLinkVal($productId) ;
			if($polQutLinkStat == 2){			   
				$condition .= " AND product_version.`effective_date` <= CURDATE() ";
			}else{			   
				$condition .= " AND product_version.`effective_date` <= '$policyStartDate'";
			}
		}
		if($perticularDuration != '')
			$durationCondition = " AND duration.flag = '$perticularDuration'";
		$productId = $GLOBALS["db"]->sqlValue(trim($productId));
		$SQL = "SELECT duration.product_version_duration_id,duration.flag, duration.max_duration_days
		FROM (SELECT MAX(product_version_id) product_version_id FROM `" . CORE_DETAILS_PRODUCT_VERSION . "` product_version
		WHERE product_version.`product_id` = $productId $condition) MAX_PRODUCT_VER
		INNER JOIN `" . CORE_DETAILS_PRODUCT_VERSION_DURATION . "` duration ON MAX_PRODUCT_VER.product_version_id = duration.product_version_id 
		WHERE $durationCondition";
		$query = $GLOBALS['db']->sqlQuery($SQL);
		$result = $GLOBALS['db']->sqlFetchRowset($query);
		return $result;
	}

	function getTermDurationDateCustom($termId,$term_duration,$date,$businessEventId = '',$formType= '')
	{
		$termId = $GLOBALS["db"]->sqlValue(trim($termId));
		$SQLterm = "SELECT duration.min_duration_days, duration.max_duration_days,duration.flag
		FROM  " . CORE_DETAILS_PRODUCT_VERSION_DURATION . " AS duration
		WHERE  duration.product_version_duration_id =  $termId";
		$queryterm = $GLOBALS['db']->sqlQuery($SQLterm);
		$resultterm = $GLOBALS['db']->sqlFetchRow($queryterm);
		
		$interval= 2;
		$SQL= "SELECT date_sub(date_add('".$date."', interval ".$term_duration." year),interval ".$interval." second) as dateEnd";
		if($formType == 'MTA')
		{
			 $SQL= "SELECT effective_end_date as dateEnd FROM ".CORE_DETAILS_BUSINESS_EVENT." WHERE business_event_id = '".$businessEventId."' ";
		}
		$query = $GLOBALS['db']->sqlQuery($SQL);
		$result = $GLOBALS['db']->sqlFetchRow($query);

		$dateEndTimeArr = explode(" ",$result['dateEnd']);
		$tearmArr['dateEnd']			=	$GLOBALS['cfn']->formatDate($dateEndTimeArr[0]);
		//$tearmArr['timeEnd']			= ($dateEndTimeArr[1] ? $dateEndTimeArr[1] : '23:59:59') ;
		$tearmArr['timeEnd']			= ($dateEndTimeArr[1] ? $dateEndTimeArr[1] : $GLOBALS['DEFAULT_END_TIME']) ;
		$tearmArr['max_duration_days']	=	$resultterm['max_duration_days'];
		$tearmArr['termDuration']		=	$resultterm['flag'];
		return $tearmArr;
	}
	
	function getClientDob($client_id)
	{
		$SQLterm = "SELECT clients.dob
		 FROM  " . CORE_DETAILS_CLIENTS . " AS clients
		 WHERE  clients.clients_id =  $client_id";
		 $queryterm = $GLOBALS['db']->sqlQuery($SQLterm);
		 $resultterm = $GLOBALS['db']->sqlFetchRow($queryterm);
		 if(isset($resultterm['dob'])){
		 return $resultterm['dob'];
		 }
		 
		 return false;

	}
    
	function getClientData($client_id)
	{
		$SQLterm = "SELECT * FROM  " . CORE_DETAILS_CLIENTS . " WHERE  clients_id =  $client_id";
		$queryterm = $GLOBALS['db']->sqlQuery($SQLterm);
		$resultterm = $GLOBALS['db']->sqlFetchRow($queryterm);
		return $resultterm;
	}

	function getBusinessEventClientData($event_id)
	{
		$SQLterm = "SELECT t1.*,g.gender FROM  " . CORE_DETAILS_BUSINESS_EVENT_CLIENT . " `t1`
		LEFT JOIN " . CORE_MASTER_GENDER . " g ON g.gender_id = t1.gender_id WHERE  business_event_id = $event_id";
		$queryterm = $GLOBALS['db']->sqlQuery($SQLterm);
		$resultterm = $GLOBALS['db']->sqlFetchRow($queryterm);
		return $resultterm;
	}

	function getadditionalClientData($event_id)
	{
	
		$Sql = "SELECT t2.*, g.gender
				FROM `".CORE_DETAILS_BUSINESS_EVENT_CLIENT."` t1". 
				" LEFT JOIN  `".CORE_DETAILS_ADDITIONAL_CLIENT."` t2 ON t1.business_event_client_id = t2.business_event_client_id
				LEFT JOIN " . CORE_MASTER_GENDER . " g ON g.gender_id = t2.gender_id"
				." WHERE t1.business_event_id = ".$event_id." ";

		$query = $GLOBALS["db"]->sqlQuery($Sql);
		$resultArray = $GLOBALS["db"]->sqlFetchRow($query);
		return $resultArray;

		
	}

	function getDefaultTimezone()
	{
		$SQL = "SELECT * FROM " . CORE_MASTER_TIMEZONE . " WHERE status = 1 AND isDefault = 1";
		$query = $GLOBALS['db']->sqlQuery($SQL);
		$result = $GLOBALS['db']->sqlFetchRow($query);

		return ($result ? $result['timezone_id'] : 0);
	}

	function getAllApplicantListByBusinessEventId($businessEventId)
	{	
		$sql = "SELECT risk_event_id,client_fk,client_fk_type FROM " .CORE_DETAILS_BUSINESS_EVENT_RISK. " WHERE business_event_id = $businessEventId";
		$resultchk  = $GLOBALS['db']->sqlQuery($sql);
		$rowchk	 = $GLOBALS['db']->sqlFetchRowset($resultchk);
		//pr($rowchk);
		$applicantList = array();
		foreach($rowchk as $value){
			$riskeventid = $GLOBALS['cfn']->encryptIt($value['risk_event_id']);
		   if($value['client_fk_type'] == 1)
			{
			 $sql1 = "SELECT CONCAT(MT.title,' ',A.firstname,' ',A.surname) AS full_name FROM ".CORE_DETAILS_BUSINESS_EVENT_CLIENT." AS A  INNER JOIN ".CORE_MASTER_TITLE." MT ON   A.title_id = 	MT.title_id 
				WHERE A.business_event_client_id = $value[client_fk]";
				$resultchk1  = $GLOBALS['db']->sqlQuery($sql1);
				$rowchk1	= $GLOBALS['db']->sqlFetchRow($resultchk1); 
				$applicantList[$riskeventid]=$rowchk1['full_name'];
			   
			}
			else if($value['client_fk_type'] == 2){
			   $sql1 = "SELECT CONCAT(MT.title,' ',A.firstname,' ',A.surname) AS full_name FROM ".CORE_DETAILS_ADDITIONAL_CLIENT."   AS A   INNER JOIN ".CORE_MASTER_TITLE." MT ON   A.title_id = MT.title_id 
				WHERE A.business_event_client_id = $value[client_fk]";
			
				 $resultchk1  = $GLOBALS['db']->sqlQuery($sql1);
				$rowchk1	 = $GLOBALS['db']->sqlFetchRow($resultchk1);
				$applicantList[$riskeventid]=$rowchk1['full_name'];

			   
			}
			
		}
		return  $applicantList;
	}
	
	function getBreResponse($risk_event_id)
	{
		$resultArray = array();
		$Sql = "SELECT * FROM ".CORE_DETAILS_BRE_RESPONSE." WHERE risk_event_id = '".$risk_event_id."' AND is_live = '1'";
		$query = $GLOBALS["db"]->sqlQuery($Sql);
		$resultArray = $GLOBALS["db"]->sqlFetchRow($query);
		return $resultArray;
	}

	function fetchClients($qId)
	{
		$qId = $GLOBALS["db"]->sqlValue(trim($qId));
		$SQL = "SELECT c.clients_id,c.entity_id as entity_id,e.code, c.portalStatus, c.title_id,c.firstname,c.surname,c.telephone AS clientTelephone,
		c.mobile_no,c.email_id,c.gender_id,c.client_type,c.company_name,c.maritalId,c.dob,e.entity_name,e.entity_name as entity_name_header,e.code,e.registration_number,e.commercial_emailaddress,
		c.id_no,c.alternate_email,c.profession_id,e.profession_id as entity_profession, e.status AS clientStatus
		FROM  " . CORE_DETAILS_CLIENTS . " AS c
		LEFT JOIN " . CORE_DETAILS_ENTITY_TABLE . " e ON e.entity_id = c.entity_id
		LEFT JOIN " . CORE_MASTER_GENDER . " g ON g.gender_id = c.gender_id
		LEFT JOIN " . CORE_MASTER_TITLE . " t ON t.title_id = c.title_id
		WHERE  c.clients_id =  $qId";

		$query = $GLOBALS['db']->sqlQuery($SQL);
		$result = $GLOBALS['db']->sqlFetchRow($query);
		return $result;
	}

	function fetchClientsByRiskEvent($risk_event_id)
	{
		$risk_event_id = $GLOBALS["db"]->sqlValue(trim($risk_event_id));
		$SQL = "SELECT a.business_event_id, a.risk_event_id, a.client_fk, a.client_fk_type FROM  " . CORE_DETAILS_BUSINESS_EVENT_RISK . " AS a WHERE  a.risk_event_id =  $risk_event_id";
		$RESULTSQL  = $GLOBALS['db']->sqlQuery($SQL);
		$ROWSQL	 = $GLOBALS['db']->sqlFetchRow($RESULTSQL);
        $clientList = array();
        if($ROWSQL['client_fk_type'] == 1)
        {
            $SQLSUB = "SELECT MG.gender_id, MG.gender, MT.title_id, MT.title,  A.firstname, A.surname, A.email_id, A.dob FROM ".CORE_DETAILS_BUSINESS_EVENT_CLIENT." AS A LEFT JOIN ".CORE_MASTER_GENDER." MG ON MG.gender_id = A.gender_id LEFT JOIN ".CORE_MASTER_TITLE." MT ON MT.title_id = A.title_id WHERE A.business_event_client_id = $ROWSQL[client_fk]";
            $RESULTSUB = $GLOBALS['db']->sqlQuery($SQLSUB);
            $clientList = $GLOBALS['db']->sqlFetchRow($RESULTSUB);
        }
        if($ROWSQL['client_fk_type'] == 2)
        {
            $SQLSUB = "SELECT MG.gender_id, MG.gender, MT.title_id, MT.title,  A.firstname, A.surname, A.email_id, A.dob FROM ".CORE_DETAILS_ADDITIONAL_CLIENT." AS A LEFT JOIN ".CORE_MASTER_GENDER." MG ON MG.gender_id = A.gender_id LEFT JOIN ".CORE_MASTER_TITLE." MT ON MT.title_id = A.title_id WHERE A.business_event_client_id = $ROWSQL[client_fk]";
            $RESULTSUB  = $GLOBALS['db']->sqlQuery($SQLSUB);
            $clientList = $GLOBALS['db']->sqlFetchRow($RESULTSUB);
        }
        return $clientList;
	}

	function getSectionVersionId($riskEventId)
    {
        $sql = "SELECT tr.section_version_id FROM `".CORE_DETAILS_RISK."` tr". 
                " LEFT JOIN  `".CORE_DETAILS_RISK_EVENT."` tre ON tr.risk_id = tre.risk_id". 
                " WHERE tre.risk_event_id = ".$riskEventId;
        $query = $GLOBALS["db"]->sqlQuery($sql);
        $resultArray = $GLOBALS["db"]->sqlFetchRow($query);
        return $resultArray['section_version_id'];         
    }

	function getBusinessDetailsUsingBusinessEventId($businessEventId)
	{
		$resultArray = array();
		$Sql = "SELECT tcdb.*, tcdpb.product_id, tcdpb.product_code, tcdpb.product_name, tcdct.short_code, tcdet.display_name, tcdetB.display_name AS brokerName, tcdc.clients_id, CONCAT(tu.firstName, ' ', tu.lastName) AS user_full_name, tcdbet.business_event_id 
				FROM `".CORE_DETAILS_BUSINESS_EVENT."` tcdbet". 
				" LEFT JOIN  `".CORE_DETAILS_BUSINESS."` tcdb ON tcdbet.business_id = tcdb.business_id".
				" LEFT JOIN  `".CORE_DETAILS_CURRENCY_TABLE."` tcdct ON tcdb.currency_id = tcdct.currency_id". 
				" LEFT JOIN  `".CORE_DETAILS_PRODUCT_BUILDER."` tcdpb ON tcdb.product_id = tcdpb.product_id". 
				" LEFT JOIN  `".CORE_DETAILS_CLIENTS."` tcdc ON tcdb.clients_id = tcdc.clients_id". 
				" LEFT JOIN  `".CORE_DETAILS_ENTITY_TABLE."` tcdet ON tcdc.entity_id = tcdet.entity_id". 
				" LEFT JOIN  `".CORE_DETAILS_ENTITY_TABLE."` tcdetB ON tcdb.broker_id = tcdetB.entity_id". 
				" LEFT JOIN  `".CORE_DETAILS_USER."` tu ON tcdb.added_by = tu.user_id". 
				" WHERE tcdbet.business_event_id = ".$businessEventId." AND tcdct.status = '1'";
		$query = $GLOBALS["db"]->sqlQuery($Sql);
		$resultArray = $GLOBALS["db"]->sqlFetchRow($query);
		return $resultArray;
	}
	function updateQuote($option){
		$businessEventId = $option['businessEventId'];
		$basePremium = $option['basePremium'];
		
		$InsurerPremium = $basePremium*70/100; //It comes from the BRE
		$MGAPremium = $basePremium*29/100; //It comes from the BRE
		$LBPremium = $basePremium*1/100; //It comes from the BRE
		
		
		//Check Risk ir created or not
        $sqlBER = "SELECT tber.risk_event_id  FROM `".CORE_DETAILS_BUSINESS_EVENT_RISK."` tber WHERE tber.business_event_id = ".$businessEventId;
        $queryBER = $GLOBALS["db"]->sqlQuery($sqlBER);
        $resultBER = $GLOBALS["db"]->sqlFetchRow($queryBER);
        $risk_event_id = isset($resultBER['risk_event_id'])?$resultBER['risk_event_id']:0;
        if($risk_event_id>0) {
        	
        	//Update quote_event Table
			$sqlQE = "SELECT tqe.quote_event_id FROM `".CORE_DETAILS_QUOTE_EVENT."` tqe WHERE tqe.business_event_id = ".$businessEventId ." AND amount_type='3'";
	        $queryQE = $GLOBALS["db"]->sqlQuery($sqlQE);
	        $resultQE = $GLOBALS["db"]->sqlFetchRow($queryQE);
	        $quote_event_id = $resultQE['quote_event_id'];
	        if($quote_event_id>0) {
		        $saveToQuoteEvent = array(
		        	'total_premium' => $basePremium,
		        	'original_total_premium' => $basePremium,
		        	'total_tax' => 0.00,
		        	'original_total_tax' => 0.00,
		        	'total_commission' => 0.00
		        );
				$cond = "quote_event_id = '". $quote_event_id."'";
		        $GLOBALS["db"]->sqlUpdate(CORE_DETAILS_QUOTE_EVENT, $saveToQuoteEvent, $cond);
			}
			
	        //Update tbl_quote_risk
	        $sqlQER = "SELECT tqr.quote_riskclass_id FROM `".CORE_DETAILS_QUOTE_RISK."` tqr WHERE tqr.quote_event_id = ".$quote_event_id ." AND tqr.risk_event_id='".$risk_event_id."'";
	        $queryQER = $GLOBALS["db"]->sqlQuery($sqlQER);
	        $resultQER = $GLOBALS["db"]->sqlFetchRow($queryQER);
	        $quote_riskclass_id = $resultQER['quote_riskclass_id'];
	        if($quote_riskclass_id>0) {
				$saveToQuoteEventRisk = array(
		        	'riskclass_premium' => $basePremium,
		        	'orginal_premium' => $basePremium,
		        	'riskclass_tax' => 0.00,
		        	'original_tax' => 0.00
		        );
				$cond = "quote_riskclass_id = '". $quote_riskclass_id."'";
		        $GLOBALS["db"]->sqlUpdate(CORE_DETAILS_QUOTE_RISK, $saveToQuoteEventRisk, $cond);
			}
			
	        //Update tbl_quote_risk_binding_group
	        $sqlQRBG = "SELECT tqrbg.quote_risk_binding_group_id FROM `".CORE_DETAILS_QUOTE_BINDING_GROUP."` tqrbg WHERE tqrbg.quote_riskclass_id = '".$quote_riskclass_id ."' ";
	        $queryQRBG = $GLOBALS["db"]->sqlQuery($sqlQRBG);
	        $resultQRBG = $GLOBALS["db"]->sqlFetchRow($queryQRBG);
	        $quote_risk_binding_group_id = $resultQRBG['quote_risk_binding_group_id'];
	        if($quote_risk_binding_group_id>0) {
				$saveToQuoteBindingGroup = array(
		        	'premium' => $basePremium,
		        	'tax' => 0.00
		        );
				$cond = "quote_risk_binding_group_id = '". $quote_risk_binding_group_id."'";
		        $GLOBALS["db"]->sqlUpdate(CORE_DETAILS_QUOTE_BINDING_GROUP, $saveToQuoteBindingGroup, $cond);
			}
			
	        //Update tbl_quote_risk_binding_group_breakup
	        $sqlQRBGB = "SELECT tqrbgb.quote_breakup_id, tqrbgb.foreign_key_type_id, tqrbgb.foreign_key_id  
	        FROM `".CORE_DETAILS_QUOTE_BREAKUP."` tqrbgb 
	        WHERE tqrbgb.quote_risk_binding_group_id = '".$quote_risk_binding_group_id ."' ";
	        $queryQRBGB = $GLOBALS["db"]->sqlQuery($sqlQRBGB);
	        $resultQRBGBs = $GLOBALS["db"]->sqlFetchRowset($queryQRBGB);
	        if(!empty($resultQRBGBs) && count($resultQRBGBs)>0) {
				foreach($resultQRBGBs as $resultQRBGB) {
					$quote_breakup_id = $resultQRBGB['quote_breakup_id'];
					//Update Insurer---
					if($resultQRBGB['foreign_key_type_id'] == '2' && $resultQRBGB['foreign_key_id'] == '10052') {
						$saveToQuoteBindingGroupBreakup = array(
				        	'commission_amount' => $InsurerPremium,
				        	'original_commission_amount' => $InsurerPremium,
				        	'applied_commission_value' => ($InsurerPremium*100/$basePremium)
				        );
						$cond = "quote_breakup_id = '". $quote_breakup_id."'";
				        $GLOBALS["db"]->sqlUpdate(CORE_DETAILS_QUOTE_BREAKUP, $saveToQuoteBindingGroupBreakup, $cond);
					}
					
					//Update MGA---
					else if($resultQRBGB['foreign_key_type_id'] == '2' && $resultQRBGB['foreign_key_id'] == '1') {
						$saveToQuoteBindingGroupBreakup = array(
				        	'commission_amount' => $MGAPremium,
				        	'original_commission_amount' => $MGAPremium,
				        	'applied_commission_value' => ($MGAPremium*100/$basePremium)
				        );
						$cond = "quote_breakup_id = '". $quote_breakup_id."'";
				        $GLOBALS["db"]->sqlUpdate(CORE_DETAILS_QUOTE_BREAKUP, $saveToQuoteBindingGroupBreakup, $cond);
					}
					
					//Update London Broker---
					else if($resultQRBGB['foreign_key_type_id'] == '4' && $resultQRBGB['foreign_key_id'] == '2') {
						$saveToQuoteBindingGroupBreakup = array(
				        	'commission_amount' => $LBPremium,
				        	'original_commission_amount' => $LBPremium,
				        	'applied_commission_value' => ($LBPremium*100/$basePremium)
				        );
						$cond = "quote_breakup_id = '". $quote_breakup_id."'";
				        $GLOBALS["db"]->sqlUpdate(CORE_DETAILS_QUOTE_BREAKUP, $saveToQuoteBindingGroupBreakup, $cond);
					}
				}
			}
		}
		
		return true;
		
	}
	function userSignature($userId)
	{
		$sql = "SELECT * FROM " . CORE_DETAILS_USER . " WHERE user_id ='" . $userId . "'";
		$query = $GLOBALS["db"]->sqlQuery($sql);
		$row = $GLOBALS['db']->sqlFetchRow($query);
		return $row;
	}
	
	function addEditQuickQuote($data, $qId="")
    {
        if (isset($qId) && $qId != "")
		{
            $cond = "quickquote_response_id = '" . $qId . "'";
            $GLOBALS["db"]->sqlUpdate(CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE, $data, $cond);
        } 
		else
		{
            $GLOBALS["db"]->sqlInsert(CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE, $data);
            $qId = $GLOBALS['db']->getInsertId();
			
        }
		
        return $qId;
    }
	//Lokeanth write code update quick response policy end date on 02-08-2022
	/*function updateQuickQuotedate($data, $qId)
    {
	    	if($qId != "") {
	            $cond = "quickquote_response_id = '" . $qId . "'";
	            $GLOBALS["db"]->sqlUpdate(CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE, $data, $cond);          
	        }
		return $qid;
	}*/
	function getQuickQuoteId($businessEventId)
	{
		$resultArray = array();
		$Sql = "SELECT qq.quickquote_response_id FROM `".CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE."` qq". " WHERE qq.business_event_id = ".$businessEventId."";
		$query = $GLOBALS["db"]->sqlQuery($Sql);
		$resultArray = $GLOBALS["db"]->sqlFetchRow($query);
		if(isset($resultArray['quickquote_response_id']))
			return $resultArray['quickquote_response_id'];
		else
			return false;
	}
	
	//Get Single quote data modified by lokenath 27-06-2022
	function getQuickSingleQuoteData($businessEventId)
	{
		$Sql = "SELECT qq.* FROM `".CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE."` qq". " WHERE qq.business_event_id = ".$businessEventId."";
		$query = $GLOBALS["db"]->sqlQuery($Sql);
		$resultArray = $GLOBALS["db"]->sqlFetchRow($query);
		return $resultArray;
	}
	//Delete Single quote data modified by lokenath 19-08-2022
	function deleteQuickSingleQuoteData($qId)
	{
		//Delete Quote
		 if ($qId != '' && $qId != 0) {		
            $GLOBALS['db']->sqlDelete(CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE, 'quickquote_response_id = ' . $qId);
			 return 1;
		 }else{
		 return 0;
		 }
	}
	
	//Get Multiple Quote Data modified by lokenath 27-06-2022
	function getQuickQuoteData($businessEventId)
	{
		$Sql = "SELECT qq.* FROM `".CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE."` qq". " WHERE qq.business_event_id = ".$businessEventId."";
		//$Sql = "SELECT qq.quickquote_response_id as quote_reponse_id,qq.* FROM `".CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE."` qq". " WHERE qq.business_event_id = ".$businessEventId."";
		$query = $GLOBALS["db"]->sqlQuery($Sql);
		$resultArray = $GLOBALS["db"]->sqlFetchRowset($query);
		return $resultArray;
	}

	function getQuickQuoteMail()
	{
		$Sql = "SELECT dmf.* FROM `".CORE_DETAILS_DOCUMENT_MAIL_FORMAT."` dmf". " WHERE dmf.mail_type = 37";
		$query = $GLOBALS["db"]->sqlQuery($Sql);
		$resultArray = $GLOBALS["db"]->sqlFetchRow($query);
		return $resultArray;
	}

	function mailBufferInsert($buffer)
    {
        return $bufferId = $GLOBALS["db"]->sqlInsert(CORE_DETAILS_EMAIL_BUFFER, $buffer);
    }

    function mailBufferContextInsert($buffer)
    {
        return $bufferContextId = $GLOBALS["db"]->sqlInsert(CORE_DETAILS_EMAIL_BUFFER_CONTEXT, $buffer);
    }
	
	function getBusinessIdWrtBusinessEventId($businessEventId)
    {
        $SQL = "SELECT bEvent.*,business.*,product.`product_basis_id`, product.`entity_id` coverholder_id, currency.`short_code` quote_currecny,
        product.`product_name`, product.`proRataConfiguration`, product.`product_code`, TMZ.CC AS timezone_cc
        FROM  " . CORE_DETAILS_BUSINESS_EVENT . " AS bEvent
		INNER JOIN " . CORE_DETAILS_BUSINESS . " AS business ON bEvent.`business_id` = business.`business_id`
		INNER JOIN " . CORE_DETAILS_PRODUCT_BUILDER . " product ON business.`product_id` = product.`product_id`
		INNER JOIN " . CORE_DETAILS_CURRENCY_TABLE . " currency ON business.`currency_id` = currency.`currency_id`
		LEFT JOIN " . CORE_MASTER_TIMEZONE . " TMZ ON TMZ.timezone_id = business.timezone_id
			WHERE  bEvent.business_event_id =  $businessEventId";
        $query = $GLOBALS['db']->sqlQuery($SQL);
        $result = $GLOBALS['db']->sqlFetchRowAssoc($query);

        return $result;
    }
	function getRefNo($businessId, $businessEventId, $isPolicyOrQuote = 'Q')
    {
        $refColumn = 'quote_no';
        if ($isPolicyOrQuote == 'P')
            $refColumn = 'policy_no';

        $data = array();
        $SQL = "SELECT TB.$refColumn,BE.quote_version_ref FROM " . CORE_DETAILS_BUSINESS . " TB INNER JOIN " . CORE_DETAILS_BUSINESS_EVENT . " BE ON BE.business_id = TB.business_id WHERE BE.business_event_id = $businessEventId AND TB.`business_id` = $businessId ";
        $query = $GLOBALS['db']->sqlQuery($SQL);
        $result = $GLOBALS['db']->sqlFetchRow($query);
        $quoteNo = $result['quote_no'];
        $quoteVerRef = $result['quote_version_ref'];
        $quoteNoArray = explode('/', $quoteNo);

        $newQuoteRefNo = $quoteNoArray[0];
        if ($quoteNoArray[1]) {
            $newQuoteRefNo .= '/' . $quoteNoArray[1];
        }
        if ($quoteNoArray[2]) {
            $newQuoteRefNo .= '/' . $quoteNoArray[2];
        }
        if ($quoteNoArray[3]) {
            $newQuoteRefNo .= '/' . $quoteNoArray[3];
        }
		if ($quoteNoArray[4]) {
            $newQuoteRefNo .= '/' . $quoteNoArray[4];
        }
		if ($quoteNoArray[5]) {
            $newQuoteRefNo .= '/' . $quoteNoArray[5];
        }

        $data['quote_no'] = $newQuoteRefNo;
        $data['version_no'] = $quoteVerRef;
        return $data;
    }
    function getBusinessEventClient($businessEventId)
	{
		
		$mainPolicyHolderDetailsSql = "SELECT 
		MG.gender_id, MG.gender, MT.title_id, MT.title,  A.firstname, A.surname, A.email_id, A.dob, A.smoker_id, A.employment_status_id, A.preferred_payment_day, A.first_payment_date
		FROM ".CORE_DETAILS_BUSINESS_EVENT_CLIENT." AS A 
		LEFT JOIN ".CORE_MASTER_GENDER." MG ON MG.gender_id = A.gender_id 
		LEFT JOIN ".CORE_MASTER_TITLE." MT ON MT.title_id = A.title_id 
		WHERE A.business_event_id = $businessEventId";
		$mainPolicyHolderDetailsQuery = $GLOBALS['db']->sqlQuery($mainPolicyHolderDetailsSql);
		$mainPolicyHolderDetailsResult = $GLOBALS['db']->sqlFetchRow($mainPolicyHolderDetailsQuery);
		return $mainPolicyHolderDetailsResult;
	}

    function isDeclarationProduct($productId)
    {
        $con = "product_id = $productId";
        $productDetails = $GLOBALS['db']->dataArray(CORE_DETAILS_PRODUCT_BUILDER, "product_basis_id", $con, "1");
        
        if($productDetails[0]['product_basis_id'] == '2'){
            $isDec = 'yes';
        }else{
            $isDec = 'no';
        }    
        return $isDec;    
    }

    function getRiskEventPremium($businessEventId)
    {
        $sql = "SELECT  business_event_risk.`action_type`,  cover_version.`section_id`,cover.`section_description`, risk.`section_version_id`,risk_event.`risk_event_id`,
        quote_risk.`riskclass_premium`, quote_risk.`riskclass_tax`,quote_risk.`quote_riskclass_id`,
        TC.short_code,TC.currency_html_code, TB.broker_id,TB.introducer_id,cover.is_prorated 
        FROM `" . CORE_DETAILS_BUSINESS_EVENT_RISK . "` business_event_risk
        INNER JOIN `" . CORE_DETAILS_BUSINESS_EVENT . "` TBE ON business_event_risk.`business_event_id` = TBE.`business_event_id`
        INNER JOIN `" . CORE_DETAILS_BUSINESS . "` TB ON TBE.`business_id` = TB.`business_id`
        INNER JOIN `" . CORE_DETAILS_CURRENCY_TABLE . "` TC ON TB.`currency_id` = TC.`currency_id`
        INNER JOIN `" . CORE_DETAILS_QUOTE_RISK . "` quote_risk ON business_event_risk.`risk_event_id` = quote_risk.`risk_event_id`
        INNER JOIN `" . CORE_DETAILS_RISK_EVENT . "` risk_event ON quote_risk.`risk_event_id` = risk_event.`risk_event_id`
        INNER JOIN `" . CORE_DETAILS_RISK . "` risk ON risk_event.`risk_id` = risk.`risk_id`
        INNER JOIN `" . CORE_DETAILS_PRODUCT_VERSION_SECTION . "` cover_version ON risk.`section_version_id` = cover_version.`section_version_id`
        INNER JOIN `" . CORE_DETAILS_PRODUCT_BUILDER_SECTION_TABLE . "` cover ON cover_version.`section_id` = cover.`product_builder_section_id`
        WHERE business_event_risk.`business_event_id` = $businessEventId AND business_event_risk.`action_type` <> '4'
        AND risk.`section_version_id` <> 0
        GROUP BY risk.`risk_id` ORDER BY risk.`risk_id` ASC";

        $query = $GLOBALS['db']->sqlQuery($sql);
        $result = $GLOBALS['db']->sqlFetchRowsetAssoc($query);
        $tableToJoin = CORE_DETAILS_QUOTE_BINDING_GROUP." QG INNER JOIN ".CORE_DETAILS_QUOTE_BREAKUP." QB ON QG.quote_risk_binding_group_id = QB. quote_risk_binding_group_id";
        foreach ($result as $columnName => $value) {
            $coverName = $this->getCoverNameEx($value['risk_event_id']);

            $quoteRiskBindingCond = "QG.quote_riskclass_id = ".$value['quote_riskclass_id']." AND QB.fee_type IN(1,4) AND QB.foreign_key_type_id = 3 ";
            $feesTotal = $GLOBALS['db']->dataArray($tableToJoin, "SUM(commission_amount) AS commission_amount,QB.fee_type", $quoteRiskBindingCond, "1");

            $quoteRiskBindingCond = "QG.quote_riskclass_id = ".$value['quote_riskclass_id']." AND QB.fee_type IN(2,3) AND QB.foreign_key_type_id = 3 ";
            $withinGwpFeesTotal = $GLOBALS['db']->dataArray($tableToJoin, "SUM(commission_amount) AS commission_amount", $quoteRiskBindingCond, "1");

            $result[$columnName]['risk_event_id'] = $GLOBALS['cfn']->encryptIt($value['risk_event_id']);
            $result[$columnName]['dec_risk_event_id'] = $value['risk_event_id'];
            $result[$columnName]['section_version_id'] = $GLOBALS['cfn']->encryptIt($value['section_version_id']);
            $result[$columnName]['covername'] = $coverName;
            $result[$columnName]['riskFeeTotal'] = $feesTotal[0]['commission_amount'];
            $result[$columnName]['riskFeeWithinGwpTotal'] = $withinGwpFeesTotal[0]['commission_amount'];
            $result[$columnName]['riskFeeFlag'] = (($feesTotal[0]['commission_amount'] != 0)?1:0);
            if($result[$columnName]['riskFeeFlag'] == 0)
            {
                 $result[$columnName]['riskFeeFlag'] = (($withinGwpFeesTotal[0]['commission_amount'] != 0)?1:0);
            }
        }

        return $result;
    }
	
	function getCoverNameEx($riskEventId)
    {
        $sql = "SELECT RER.risk_data,QD.field_type_id,REG.risk_event_group_id,REQ.question_id FROM ".CORE_DETAILS_RISK_EVENT_RESPONSE." RER "
                . "INNER JOIN ".CORE_DETAILS_RISK_EVENT_QUESTION." REQ ON(RER.risk_event_question_id = REQ.risk_event_question_id) "
                . "INNER JOIN ".CORE_DETAILS_QUESTION_DETAILS_TABLE." QD ON(REQ.question_id = QD.question_id) "
                . "INNER JOIN ".CORE_DETAILS_RISK_EVENT_GROUP." REG ON(REQ.risk_event_group_id = REG.risk_event_group_id) "
                . "WHERE REG.risk_event_id = '".$riskEventId."' AND QD.show_cover_name ='1'";
        $query = $GLOBALS['db']->sqlQuery($sql);
        $result = $GLOBALS['db']->sqlFetchRowsetAssoc($query);    
    
        $coverName = "";
        $count = (count($result) - 1);
        foreach($result as $columnName=>$value) {
            if($value['risk_data'] != ""){

                if($value['field_type_id']==2 || $value['field_type_id']==4 || $value['field_type_id']==5)
                {
                    $sqlIn = "SELECT QR.narrative FROM ".CORE_DETAILS_QUESTION_RESPONSE_MAPPING." QRM, ".CORE_DETAILS_QUESTION_RESPONSE." QR WHERE QRM.response_id=QR.response_id AND  QRM.question_id = '".$value['question_id']."' AND QR.`key`='".$value['risk_data']."' ";
                    $queryIn = $GLOBALS['db']->sqlQuery($sqlIn);
                    $perResIn = $GLOBALS['db']->sqlFetchRow($queryIn); 
                    $coverName .=$perResIn['narrative'] ;
                    
                }
                elseif($value['field_type_id']==1 || $value['field_type_id']==3 || $value['field_type_id']==6 || $value['field_type_id']==7){

                     if($columnName == $count){
                        $coverName .= $value['risk_data'];
                    } else {
                        $coverName .= $value['risk_data'].",";
                    }
                } else {
                    if($value['field_type_id'] == "8"){
                        $sql = "SELECT * FROM ".CORE_DETAILS_TBL_ADDRESS_TABLE." WHERE address_id = '".$value['risk_data']."'";
                        $query = $GLOBALS['db']->sqlQuery($sql);
                        $addResult = $GLOBALS['db']->sqlFetchRow($query);  
                        
                            if($columnName == $count){
                                $coverName .= $addResult['address_lines_id_1'].",".$addResult['address_lines_id_2'];
                            } else {
                                $coverName .= $addResult['address_lines_id_1'].",".$addResult['address_lines_id_2'].",";
                            }
                    } 
                }
            } else {
	        	if($value['field_type_id'] == "9"){
			        $sql = "SELECT * FROM ".CORE_DETAILS_QUESTION_DETAILS_PERSON." WHERE question_details_id = '".$value['question_id']."' AND is_visible = '1' AND show_cover_name = '1'";
			        $query = $GLOBALS['db']->sqlQuery($sql);
			        $perResult = $GLOBALS['db']->sqlFetchRowsetAssoc($query);
			        
			        $sql = "SELECT * FROM ".CORE_DETAILS_RISK_EVENT_PERSON_RESPONSE." WHERE risk_event_group_id = '".$value['risk_event_group_id']."'";
			        $query = $GLOBALS['db']->sqlQuery($sql);
			        $perResponse = $GLOBALS['db']->sqlFetchRow($query); 
			        $personCount = count($perResult);
			        foreach($perResult as $key => $perInfo){
			            switch ($perInfo['label_name']) {
			            case "Firstname":
			                if($key == $personCount){
			                $coverName .= $perResponse['firstname'];
			                } else {
			                $coverName .= $perResponse['firstname'].",";
			                }
			                break;
			            case "Lastname":
			                if($key == $personCount){
			                $coverName .= $perResponse['lastname'];
			                } else {
			                $coverName .= $perResponse['lastname'].",";
			                }
			                break;
			            case "Date Of Birth":
			                if($key == $personCount){
			                $coverName .= $perResponse['date_of_birth'];
			                } else {
			                $coverName .= $perResponse['date_of_birth'].",";
			                }
			                break;
			            case "Email":
			                if($key == $personCount){
			                $coverName .= $perResponse['email'];
			                } else {
			                $coverName .= $perResponse['email'].",";
			                }
			                break;
			            case "Mobile":
			                if($key == $personCount){
			                $coverName .= $perResponse['mobile'];
			                } else {
			                $coverName .= $perResponse['mobile'].",";
			                }
			                break;
			            case "Address":
			                $sql = "SELECT * FROM ".CORE_DETAILS_TBL_ADDRESS_TABLE." WHERE address_id = '".$perResponse['address_id']."'";
			                $query = $GLOBALS['db']->sqlQuery($sql);
			                $addResult = $GLOBALS['db']->sqlFetchRow($query);  
			                if($columnName == $count){
			                $coverName .= $addResult['address_lines_id_1'].",".$addResult['address_lines_id_2'];
			                } else {
			                $coverName .= $addResult['address_lines_id_1'].",".$addResult['address_lines_id_2'].",";
			                }
			                break;

			            }
			        }
			    }   
		    }
        }
        return $coverName;
    }
	
	function getPlanDetailsDataUsingBusinessEventId($businessEventId)
	{
		$SQL = "SELECT cqqr.* FROM ".CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE." AS cqqr WHERE  cqqr.business_event_id =  $businessEventId";
		$query = $GLOBALS['db']->sqlQuery($SQL);
		$result = $GLOBALS['db']->sqlFetchRowset($query);
		return $result;
	}
	
	function getPlanDetailsDataUsingQuickResponseId($quickquoteResponseId)
	{
		$SQL = "SELECT cqqr.* FROM ".CORE_DETAILS_CUSTOM_QUICK_QUOTE_RESPONSE." AS cqqr WHERE  cqqr.quickquote_response_id =  $quickquoteResponseId";
		$query = $GLOBALS['db']->sqlQuery($SQL);
		$result = $GLOBALS['db']->sqlFetchRow($query);
		return $result;
	}

	function getRiskEventData($risk_event_id='', $tableName='')
	{
		$sql="SELECT B.* FROM ".CORE_DETAILS_BRE_RESPONSE." A LEFT JOIN ".$tableName." B ON (A.res_id = B.res_id) WHERE A.is_live = '1' AND A.risk_event_id = ".$risk_event_id;
		$queryData = $GLOBALS['db']->sqlQuery($sql);
		$resultData = $GLOBALS['db']->sqlFetchRowsetAssoc($queryData);
		return $resultData;
	}

	function getALLCoverType($business_id='')
	{
		$sqlCover = "SELECT pbs.section_name, pbs.section_description, dr.section_version_id
						FROM  ".CORE_DETAILS_BUSINESS." b
						LEFT JOIN ".CORE_DETAILS_RISK." dr ON (b.business_id=dr.business_id)
						LEFT JOIN ".CORE_DETAILS_PRODUCT_VERSION_SECTION."  pvs ON dr.section_version_id = pvs.section_version_id
						LEFT JOIN ".CORE_DETAILS_PRODUCT_BUILDER_SECTION_TABLE."  pbs ON pvs.section_id = pbs.product_builder_section_id
						WHERE dr.business_id = ".$business_id." GROUP BY dr.section_version_id";

						$rsCover = $GLOBALS['db']->sqlQuery($sqlCover);
						$recCover = $GLOBALS['db']->sqlFetchRowset($rsCover);
		if(!empty($recCover))				
			return $recCover;
		else
			return 'N/A';
	}

	function getProductBuilderSectionDetails($product_id, $policyStartDate = '',$coverId = '')
	{
		//die($_REQUEST['UserController']."##".$_REQUEST['UserAction']);
		$userId = $_SESSION['MI_USERID'];
		$ruleProductCount = $this->getAssignedProductRuleCount($userId);
		$isTestProductPermission = $this->checkTestProductPermission($userId);
		$product_id = $GLOBALS["db"]->sqlValue(trim($product_id));

		$cnt = $this->isAnySignedProductVersionAvailable($product_id);
		$condition = "";

		if ($cnt > 0)
			$condition = " AND pvs.status = 3";

		if ($isTestProductPermission == 0) {
			if ($ruleProductCount[0] > 0) {
				$assignedCovers = $this->getAssignedCoverType($userId);
				 if($coverId != '')
					$assignedCovers = $coverId;
				$condition .= " AND pbs.product_builder_section_id IN (" . $assignedCovers . ")";
			}
		}
	   
		$polQutLinkStat = $this->polQuoteLinkVal($product_id) ;
		if($polQutLinkStat == 2){
			$subQueryCondition = "effective_date <= CURDATE()";
		}else{
			$subQueryCondition = "effective_date <= '$policyStartDate'";
		}
		
		//T019653
		$t1Condition='';
		if($product_id == T1_PRODUCTID && ($_REQUEST['UserAction']=='admin_clientRiskDetails' || $_REQUEST['UserAction']=='admin_clientQuoteCover'))
			$t1Condition=' AND pbs.product_builder_section_id='.NON_DEL_T1_SECTIONID[1];
		elseif($product_id == T1AM_PRODUCTID && ($_REQUEST['UserAction']=='admin_clientRiskDetails' || $_REQUEST['UserAction']=='admin_clientQuoteCover'))
			$t1Condition=' AND pbs.product_builder_section_id='.NON_DEL_T1AM_SECTIONID[1];
		elseif($product_id == T1ACC_PRODUCTID && ($_REQUEST['UserAction']=='admin_clientRiskDetails' || $_REQUEST['UserAction']=='admin_clientQuoteCover'))
			$t1Condition=' AND pbs.product_builder_section_id='.NON_DEL_T1ACC_SECTIONID[1];
		elseif($product_id == T5_PRODUCTID && ($_REQUEST['UserAction']=='admin_clientRiskDetails' || $_REQUEST['UserAction']=='admin_clientQuoteCover'))
			$t1Condition=' AND pbs.product_builder_section_id='.T5_SECTIONID;
		elseif($product_id == T4_PRODUCTID && ($_REQUEST['UserAction']=='admin_clientRiskDetails' || $_REQUEST['UserAction']=='admin_clientQuoteCover'))
			$t1Condition=' AND pbs.product_builder_section_id='.T4_SECTIONID;
		elseif($product_id == T8_PRODUCTID && ($_REQUEST['UserAction']=='admin_clientRiskDetails' || $_REQUEST['UserAction']=='admin_clientQuoteCover'))
			$t1Condition=' AND pbs.product_builder_section_id='.NON_DEL_T8V2_SECTIONID[1];
		elseif($product_id == TTHREEMGAM_PRODUCTID && ($_REQUEST['UserAction']=='admin_clientRiskDetails' || $_REQUEST['UserAction']=='admin_clientQuoteCover')) {
			$idStr = "'" . implode ( "', '", T3MGAM_ELPL_SECTIONID ) . "'";
			$t1Condition=' AND pbs.product_builder_section_id IN ( '.$idStr.')';
		}
		elseif($product_id == T3ACC_PRODUCTID && ($_REQUEST['UserAction']=='admin_clientRiskDetails' || $_REQUEST['UserAction']=='admin_clientQuoteCover'))
			$t1Condition=' AND pbs.product_builder_section_id='.T3ACC_SECTIONID;

		if($policyStartDate == '')
			$subQueryCondition = "effective_date <= NOW()";
		$SQL = "SELECT pbs.section_icon,pbs.section_description, MAX(pvs.section_version_id) AS section_version_id,pbs.product_builder_section_id
		FROM  " . CORE_DETAILS_PRODUCT_BUILDER_SECTION_TABLE . " AS pbs
		INNER JOIN " . CORE_DETAILS_PRODUCT_VERSION_SECTION . " AS pvs ON pbs.product_builder_section_id = pvs.section_id
		INNER JOIN " . CORE_DETAILS_PRODUCT_VERSION_SECTION_MAPPING . " AS pvsm ON pvsm.section_version_id = pvs.section_version_id
		INNER JOIN (SELECT MAX(product_version_id) AS product_version_id FROM " . CORE_DETAILS_PRODUCT_VERSION . "
			WHERE $subQueryCondition AND product_id =  $product_id) AS pvlatest ON pvsm.product_version_id = pvlatest.product_version_id
		WHERE pbs.is_deleted = '0' $t1Condition
		$condition GROUP BY pvs.section_id";

		$query = $GLOBALS['db']->sqlQuery($SQL);
		$result = $GLOBALS['db']->sqlFetchRowset($query);

		return $result;
	}
	function getCoverNameBySectionVersionId($sectionVersionId)
    {
        $SQL = "SELECT TPBS.section_name FROM " . CORE_DETAILS_PRODUCT_BUILDER_SECTION_TABLE . " TPBS, " . CORE_DETAILS_PRODUCT_VERSION_SECTION . " TPVS WHERE TPBS.product_builder_section_id=TPVS.section_id AND TPVS.section_version_id=$sectionVersionId";
        $query = $GLOBALS['db']->sqlQuery($SQL);
        $result = $GLOBALS['db']->sqlFetchRow($query);

        return ($result ? $result['section_name'] : 0);
    }
}
?>
