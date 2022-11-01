<div class="messages success-msg clearfix" id="success-msgmailsent" style="display: none;">
	<div style="display:none;color:red;" id="entaddfield_err_content"></div>
	<div class="alert-icon"><i class="fa fa-check-circle-o"></i>
	</div>
	<a href="#" class="alert-cross"><i class="fa fa-times"></i></a>
	<div class="alert-content"><strong>Mail Sent Succefully</strong></div>
</div>

{if $action neq "view" && $errorLogs != "" && $action neq "bind"}
	<div class="errorDiv">
		<ul>
			{foreach key=errorLogKey item=errorLog from=$errorLogs}
				<li><i class="fa fa-times-circle"></i>{$errorLogKey} {$errorLog}</li>
			{/foreach}
		</ul>
	</div>
{/if}

<form name="clientPlanDetailsAdd" id="clientPlanDetailsAdd" method="post" action="{$smarty.const.WEB_PATH}clients/admin_addEditPlanDetails" autocomplete="off">
	<input type="hidden" name="businessEventId" id="businessEventId" value="{$enBusinessEventId}" />
	<input type="hidden" name="clientId" id="clientId" value="{$clientId}" />
	<input type="hidden" name="riskEventId" id="riskEventId" value="{$riskEventId}" />
	<input type="hidden" name="versionId" id="versionId" value="{$versionId}" />
	<input type="hidden" name="fromType" id="fromType" value="{$fromType}" />

	<div class="allproduct questionSet tabGetQuote" id="allProduct_plan">
	
		{foreach item=quickQuoted key=ke from=$quickQuoteResponseDtls}
		
			<div class="accordiondiv copyproduct" role="tablist">
				<h3 title="Plan Detail" ><span class=""></span>Plan Details ({$ke+1})</h3>
				<div>
					<div class="bg-primary form-inner">
						<ul class="listing ">
							<li id="end_dateLi " class="type_policy_1">
								<div class="equal-height">
									
									<label class="medium">Type of Policy :<span class="asterisk">*</span></label>
									<div class="label_right">
									{if $businessEventDetails.type_of_policy eq '2'}
										<label> 
											<input type="checkbox"  name="type_of_policy_FH[{$ke}]" id="type_of_policy_FH_{$ke}" value="1" data-id="{$ke}" class="typ_pol_{$ke}" checked="checked" />
											{$client_data.firstname } {$client_data.surname } 
										</label>
										
										<label> 
											<input type="checkbox" name="type_of_policy_SH[{$ke}]"  data-id="{$ke}" class="typ_pol_second{$ke}"  id="type_of_policy_SH_{$ke}" value="2" {if $quickQuoted.type_of_policy eq '2'} checked="checked" {/if} />
											{$additional_client_data.firstname } {$additional_client_data.surname }             
										</label> 
									{else}
										<label> 
											<input type="checkbox"  name="type_of_policy_FH[{$ke}]"  data-id="{$ke}" class="typ_pol_{$ke}" value="1" id="type_of_policy_FH_{$ke}" checked="checked" />
											{$client_data.firstname } {$client_data.surname } 
										</label>
										<input type="checkbox" name="type_of_policy_SH[{$ke}]"  data-id="{$ke}" class="typ_pol_second{$ke}" value="2" id="type_of_policy_SH_{$ke}" style="display: none;"/>
									{/if}
									<div class="clearfix"></div>
									<!-- <input type="hidden" name="quickquote_response_id[]" value="{$quickQuoted.quickquote_response_id}" /> -->
									<input type="hidden" name="quickquote_response_id[{$ke}]" value="{$quickQuoted.quickquote_response_id}" />
									</div>
								</div>
							</li>
							<input type="hidden" name="premium_frequency[{$ke}]" value="1" class="full quuoteFormField" tabindex="5">
							<!-- <li>
								
								<div class="equal-height">
								  <label class="medium">Premium frequency :<span class="asterisk">*</span></label>
								  <div class="label_right">                        

									<select name="premium_frequency[{$ke}]" class="full quuoteFormField" tabindex="5" {if $fromType eq 'MTA'} readonly="readonly" {/if} >

									

									  {if $postValueArr.premium_frequency neq ''}
										<option value="1" {if $postValueArr.premium_frequency eq '1' || $postValueArr.premium_frequency eq '' } selected="selected" {/if} >Monthly</option>
										<option value="2" {if $postValueArr.premium_frequency eq '2'} selected="selected" {/if} >Annual</option>
									  {else}
										<option value="1" {if $quickQuoted.premium_frequency eq '1' || $quickQuoted.premium_frequency eq '' } selected="selected" {/if} >Monthly</option>
										<option value="2" {if $quickQuoted.premium_frequency eq '2'} selected="selected" {/if} >Annual</option>
									  {/if}
									</select>
									<div class="clearfix"></div>
									<p class="error" for="age" style="color: #ea4335"></p>
								  </div>
								</div>
							</li>  -->
							<li class="switchtermclass" {if ($quickQuoted.term_duration eq 0 || $quickQuoted.term_duration_age eq '') &&  ($quickQuoted.term_duration_age neq 0 && $quickQuoted.term_duration_age neq '') } style="display: none" {/if}>
								<div {if $quickQuoted.term_duration < 0} style="display: none" {/if}>
							  <label class="medium">{$smarty.const.POLICY_TERM}:<span class="asterisk">*</span></label>
							  <div class="label_right">
								<select name="term_duration[{$ke}]" id= "term_duration_{$ke}" class="full quuoteFormField" tabindex="5" {if $fromType eq 'MTA'} readonly="readonly" {/if} >
									<option value="">Select Duration</option>
									{if $post_data.term_duration neq ""}
										{html_options options = $termYears selected = $quickQuoted.term_duration}
									{else}
										{html_options options=$termYears selected = $quickQuoted.term_duration}
									{/if}
								</select>
								<input class="btn btn-primary btn-lg mb-0" value="switch to age at expiry" type="button" >
								<p class="error" for="term_duration">{$ErrMessage.term_duration.0}</p>
							  </div>
							</div>
							</li>                  
							<li class="swichageclass" {if $quickQuoted.term_duration_age eq 0 || $quickQuoted.term_duration_age eq '' } style="display: none" {/if}>
								<div class="equal-height ">
								  <label class="medium">Upto Age</label>
								  <div class="label_right"> 
									<input name="term_duration_age[{$ke}]" id="term_duration_age_{$ke}"  type="text" class="full"  value="{$quickQuoted.term_duration_age}" placeholder="Enter Age">
									<input class="btn btn-primary btn-lg mb-0" value="switch to term at expiry" type="button" >
									<div class="clearfix"></div>
									<p class="error" for="age" style="color: #ea4335"></p>
								  </div>
								</div>
							</li>
							<li>
								<div>
								<label class="medium mt-0">Type of cover :<span class="asterisk">*</span></label>
									<div class="label_right">
										<select required="" class="medium showdependent" name="pd_type_of_cover[{$ke}]">
											<option value="">Select</option>
											<option value="1" {if $quickQuoted.type_of_cover eq '1'}selected{/if}>Decreasing</option>
											<option value="2" {if $quickQuoted.type_of_cover eq '2'}selected{/if}>Level</option>
										</select>
										<div class="clearfix"></div>								

										<p class="error" for="pd_type_of_cover"></p>
									</div>
								</div>
							</li>
							<li>
								<div>
									<label class="medium mt-0">Policy type db :<span class="asterisk">*</span></label>
									<div class="label_right">
										<select required="" class="medium showdependent pd_type_of_policy_db policytype" name="pd_type_of_policy[{$ke}]" data-id="{$ke}" >
											<option value="">Select</option>
											<option value="1" {if $quickQuoted.policy_type eq '1'}selected{/if}>Term Life</option>
											<option value="2" {if $quickQuoted.policy_type eq '2'}selected{/if}>Term Life with Critical illness</option>
										</select>
										<div class="clearfix"></div>
										<span id="ageDisqualifies_{$ke}" style="color:red;"></span>
										<p class="error" for="pd_type_of_policy"></p>
									</div>
								</div>
							</li>
							<li class="protectionclass" {if $quickQuoted.budget eq 0 || $quickQuoted.budget eq '' } style="display: block" {/if}>
								<div>
									<label class="medium mt-0">Protection level: What is the amount of cover required? (In £ GB Pounds)<span class="asterisk">*</span></label>
									<div class="label_right">
										<input required="" name="pd_sum_insured[{$ke}]" id="pd_sum_insured_{$ke}" type="text" data-a-sep="," data-a-sign="GBP " data-d-group="3" data-m-dec="" class="medium numericOnly " data-validationtype="" onblur="onBlurMinimumLength('0.0000',this.id); onBlurMaximumValue('999999999.0000',this.id); " style="text-align:left" value="{$quickQuoted.suminsured}"  autocomplete="off">
										<input type="button" name="openPremium" value="Switch to premium amount" class="btn btn-primary">
										<div class="clearfix"></div>
										<span id="maxsuminsu_{$ke}" style="color:red;"></span>
										<p class="error" for="pd_sum_insured"></p>
									</div>
								</div>
							</li>
							<li class="premiumclass" {if $quickQuoted.budget eq 0 || $quickQuoted.budget eq '' } style="display: none" {/if} >
								<div>
									<label class="medium mt-0">Do you have an allocated budget a or premium amount?</label>
									<div class="label_right">
										<input required="" name="pd_budget_si[{$ke}]" type="text" data-a-sep="," data-a-sign="GBP " data-d-group="3" data-m-dec="" class="medium numericOnly " data-validationtype="" onblur="onBlurMinimumLength('0.0000',this.id); onBlurMaximumValue('999999999.0000',this.id); " style="text-align:left" value="{$quickQuote.budget}" autocomplete="off">
										<input type="button" name="protectionLevel" value="Switch to SI amount" class="btn btn-primary">

										<div class="clearfix"></div>
										<p class="error" for="pd_budget_si"></p>
									</div>
								</div>
							</li>
							<li class="totalpermdisbltyclass" style="display:block;" >
								<div id="pd_tpd_{$ke}" {if $quickQuoted.policy_type eq '2'} style="display:block;" {else} style="display:none;" {/if} >
									<label class="medium mt-0">Total Permanent Disability? <span class="asterisk">*</span></label>
									<div class="label_right">
										<select required="" class="medium showdependent" name="pd_tpd[{$ke}]">
											<option value="">Select</option>
											<option value="1" {if $quickQuoted.tpd eq '1'}selected{/if}>Yes</option>
											<option value="2" {if $quickQuoted.tpd eq '2'}selected{/if}>No</option>
										</select>
										<div class="clearfix"></div>
										<p class="error" for="pd_tpd"></p>
									</div>
								</div>
							</li>
							<li>
								<div style="display:none">
									<label class="medium mt-0">Waiver of Premium? :<span class="asterisk">*</span></label>
									<div class="label_right">
										<select required="" class="medium showdependent" name="pd_waiver_prem[{$ke}]">
											<option value="">Select</option>
											<option value="1" {if $quickQuoted.waiver_premium eq '1'}selected{/if}>Yes</option>
											<option value="2" {if $quickQuoted.waiver_premium eq '2'}selected{/if}>No</option>
										</select>
										<div class="clearfix"></div>
										<p class="error" for="pd_waiver_prem"></p>
									</div>
								</div>
							</li>
							<li>								
								<div>
									<input type="hidden" name="quickquote_response_ide" id="quickquote_response_id{$quickQuoted.quickquote_response_id}" value="{$quickQuoted.quickquote_response_id}" /> 
									<label class="medium mt-0">
										<!-- <a href="javascript:void(0);" id="remove{$quickQuoted.quickquote_response_id}" class="btn btn-primary" value="Remove" title="Remove">Delete</a> -->
										<input type="button" class="btn btn-primary delete-btn" id="delete-{$quickQuoted.quickquote_response_id}" data-id={$quickQuoted.quickquote_response_id} value="Delete">
									</label>
									<div class="label_right">
										<div class="paymentDetailsSection label_right" style="width:70%;">
												<ul class="listing">
													<li class="mb-0">
														<div class="viewSection2">
															<label class="medium" style="width:25%;">Premium:</label>
															<div class="label_right">
																<label class="lable-group-full">
																	<h2 class=""><span>GBP </span> {$quickQuoted.premium|round:2}</h2>
																	
																</label>
															</div>
														</div>
													</li>

												</ul>						
											</div>	
									</div>
								</div>								
																	
								
							</li>
						
						</ul>
					</div>
					<!--Db part-->
					
					
					
				</div>
			</div>
		
		{/foreach}
		
		
		<script type="text/javascript">	
	
			$('.delete-btn').click(function () {
				let quickquoteResponseId = $(this).data('id');
				//alert(quickquoteResponseId);
				var values = "quickquoteResponseId=" + quickquoteResponseId;
				var url = webpath + 'clients/removeQuotes';
				jQuery.ajax({
					type: "POST",
					url: url,
					data: values,
					async: true,
					dataType: "json",
					beforeSend: function () {
						$("#preloader").show();
						$("#status").show();
						$('#success-msgmailsent').hide();
					},
					error: function (errmsg) {
						console.log("errmsg", errmsg);
					},
					success: function (res) {
						location.reload();
					}
					}).done(function () {
						$("#preloader").hide();
						$("#status").hide();
					});
				
			});
		</script>
		<input type="hidden" name="planDetailsCounter" id="planDetailsCounter" value="{if $quickQuoted@total >= 0} {$quickQuoted@total} {else}0{/if}" />
		<!--Add New Plan Details Block Append-->
		<div class="field_wrapper"></div>
		<!-- End Block-->
	</div>
	{if $action != "view"}
		<div class="getQuoteBtnDiv">
			<div class="bg-primary form-inner" id="quickQuotePremiumDiv">
			<!-- <div class="bg-primary form-inner" {if $premiumToShow}{else} style="display:none;"{/if} id="quickQuotePremiumDiv"> -->
				<div class="paymentWrapper">
					<div class="paymentcolLeft">
						<h3>Quote Premium </h3>
						<div class="paymentDetailsSection">
							<ul class="listing">
								<li class="mb-0">
									<div class="viewSection2">
										<label class="medium">Total Premium:</label>
										<div class="label_right">
											<label class="lable-group-full">
												<h2 id="quickPremiumShow" class=""><span>GBP</span>{$premiumToShow}</h2>
												<input name="quickPremium" type="hidden" id="quickPremium" value="{$premiumToShow}">
											</label>
										</div>
									</div>
								</li>
							</ul>
							<hr>
							<ul class="listing">
								<li>
									<span id="contWthQt"></span>
								   <div>
										<input type="hidden" name="clientAge" id="clientAge" value="{$clientAge}" />
										<input type="hidden" name="additionalClientAge" id="additionalClientAge" value="{$additionalClientAge}" />
										<input type="button" name="getQuote" value="GET QUOTE" class="getQuote btn btn-primary">
										<input type="button" id="getCommSacrify" name="getCommSacrify" value="Commission Sacrifice" class="btn btn-primary">
										<input type="button" name="sendQuickQuote" value="SEND MAIL" class="sendQuickQuote btn btn-primary">
										<a href="javascript:void(0);" id="continueBtnWithQuote" class="btn btn-primary" >CONTINUE WITH THIS QUOTE</a>
										{if $fromType neq 'MTA' && $fromType neq 'NbAdj'}	
										<a href="javascript:void(0);" class="add_button btn btn-primary" title="Add field" value="Add More">Add More</a>
										{/if}
									</div> 
								</li>
							</ul>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>		
		</div>
	{/if}
</form>

<div id="commSacrificePopUp" style="display:none;">
	<ul class="listing">
		<li>
			<div>
				<label class="medium mt-0">Commission Sacrifice :</span></label>
				<div class="label_right" id="pd_comm_sacrfc_div">
					<input name="pd_comm_sacrfc" type="text" id="pd_comm_sacrfc" class="medium numericOnly " data-m-dec="" data-d-group="3" data-validationtype=" " onblur="onBlurMinimumLength('0.0000',this.id); onBlurMaximumValue('100.0000',this.id);"style="text-align:left" value="{$quickQuoteResponseDtls.commsn_sacrifice}" autocomplete="off">
					<div class="clearfix"></div>
					
					<p class="error" id="autonumercMinimumValuepd_comm_sacrfc" for="pd_comm_sacrfc"></p>
				</div>
			</div>
		</li>
	</ul>
	<ul class="listing">
		<li>
			<div style="text-align: center;">
				<input type="button" name="getQuote" value="GET QUOTE" class="getQuote btn btn-primary">
			</div> 
		</li>
	</ul>
</div>

<div id="clientDeclarationPopUp" style="display:none;">
<h3>I have read the following declaration to each client:</h3>
<h3>Before I can complete this purchase, I need you to confirm the following statements:</h3>

<ul class="listing">
		<li>
	      <p>You should ensure that you understand the questions you have been asked. If you do not understand anything, you should ask me for clarification or further information.</P>
		</li>
		 <li>
			<p>You know that information may not be routinely checked and we will rely on your answers being complete, accurate and up-to-date.</P>
		 </li>
		 <li>
			<p>You must inform us if there is any change in your health, occupation or lifestyle before your policy is accepted. Failure to do so may mean that your policy is cancelled, the terms are amended or the insurer may not pay out in the event of a claim.</P>
		 </li>
		 <li>
			<p>Where you have not understood anything, you have asked me for clarification or further information.</P>
		 </li>
		 <li>
			<p>Please confirm you have been informed relating to:<br><br>
                 Data Protection Act<br><br>
                 Money Laundering Regulations<br><br>
                 Medical Information<br><br>
            </P>
		 </li>
		 <li>
			<h3>I confirm I have explained the following to my client:</h3>
		 </li>
		 <li>
			<p>Where the cover offered includes a policy exclusion(s), my client is aware of this and they are willing to accept the terms. I am satisfied they have understood the limitations applied to their policy.</P>
		 </li>
		 <li>
			<p>They understand that they will not be able to claim under the policy until the policy start date, and that cover cannot be backdated to start on or before the date of any event which might lead to a claim.</P>
		 </li>
		 <li>
			<p>That should we require them to have a medical examination/additional diagnostic test to support their application, we can share their contact details with an appropriate examination supplier, and the supplier can share the results of the examination with Assura.</P>
		 </li>
		 <li>
			<p>If this is a dual  life application, they confirm that they have obtained the consent of any parties not present to provide their personal and sensitive information and for it to be used in the same way as their own, and they also consent that all plan and policy information can be shared with both applicants.</P>
		 </li>
		 <li>
			<p>They consent to us using the information provided to process their application and any future claim made on their policy.</P>
		 </li>
</ul>
	<ul class="listing">
		<li>
			<div style="text-align: center;">
			<a href="{$planDetaisContinueUrl}"  id="continueWithQuote" class="btn btn-primary">Confirm</a>
				<input type="button" name="" id="clientDeclarationCancel" value="Cancel" class="btn btn-primary">

			</div> 
		</li>
	</ul>
</div>
<!--Code for AddMore Button will show or Not-->
{if $quickQuoteResponseDtls|@count gt 0}
{literal}<script type="text/javascript">var planCounter = {/literal}{$quickQuoteResponseDtls|@count}{literal}</script>{/literal}
{else}
{literal}<script type="text/javascript">var planCounter = 0</script>{/literal}
{/if}

<div id="planTemplate" style="display:none">
	<div class="accordiondiv copyproduct" role="tablist">
		<h3 title="Plan Detail" ><span class=""></span>Plan Details</h3>
		<div>
			<div class="bg-primary form-inner">
				<ul class="listing ">
					<li class="type_policy_1">
						<div class="equal-height">
							
							<label class="medium">Type of Policy :<span class="asterisk">*</span></label>
							<div class="label_right">
							{if $businessEventDetails.type_of_policy eq '2'}
								<label> 
									<input type="checkbox" name="type_of_policy_FH[PLAN_COUNTER]" id="type_of_policy_FH_PLAN_COUNTER" value="1" checked="checked" readonly="readonly" />
									{$client_data.firstname } {$client_data.surname } 
								</label>
								
								<label> 
									<input type="checkbox" name="type_of_policy_SH[PLAN_COUNTER]" id="type_of_policy_SH_PLAN_COUNTER" value="2"/>
									{$additional_client_data.firstname } {$additional_client_data.surname }             
								</label> 
							{else}
								<label> 
									<input type="checkbox" name="type_of_policy_FH[PLAN_COUNTER]" id="type_of_policy_FH_PLAN_COUNTER" value="1" checked="checked" readonly="readonly" />
									{$client_data.firstname } {$client_data.surname } 
								</label>
								<input type="checkbox" name="type_of_policy_SH[PLAN_COUNTER]" id="type_of_policy_SH_PLAN_COUNTER"  value="2" style="display: none;"/>
							{/if}
							<div class="clearfix"></div>
							<input type="hidden" name="quickquote_response_id[PLAN_COUNTER]" value="" />
							</div>
						</div>
					</li>
					<input type="hidden" name="premium_frequency[PLAN_COUNTER]" value="1">
					<!-- <li>
						<div class="equal-height">
						<label class="medium">Premium frequency :</label>
							<div class="label_right">                        
								<select name="premium_frequency[PLAN_COUNTER]" class="full quuoteFormField" tabindex="5" >
									<option value="1" >Monthly</option>
									<option value="2" >Annual</option>
								</select>
							
								<div class="clearfix"></div>
								<p class="error" for="premium_frequency_PLAN_COUNTER" style="color: #ea4335"></p>
							</div>
						</div>
                	</li> -->
					<li class="switchtermclass" >
                    <div>
                      <label class="medium">Policy Term:<span class="asterisk">*</span></label>
                      <div class="label_right">
                        <select name="term_duration[PLAN_COUNTER]" id="term_duration_PLAN_COUNTER" class="full quuoteFormField" tabindex="5" >
                        	<option value="">Select Duration</option>							
							{html_options options=$termYears selected = $quickQuoted.term_duration}
                        </select>
                        <input class="btn btn-primary btn-lg mb-0 switchtoage" value="switch to age at expiry" type="button" >
                        <p class="error" for="term_duration_PLAN_COUNTER"></p>
                      </div>
                    </div>
                  </li>
                  
                   <li class="swichageclass" style="display: none">
                    <div class="equal-height ">
                      <label class="medium">Upto Age</label>
                      <div class="label_right"> 
                        <input name="term_duration_age[PLAN_COUNTER]" id="term_duration_age_PLAN_COUNTER" type="text" class="full"  value="" placeholder="Enter Age">
                        <input class="btn btn-primary btn-lg mb-0 switchtoterm" value="switch to term at expiry" type="button" >
                        <div class="clearfix"></div>
                        <p class="error" for="term_duration_age_PLAN_COUNTER" style="color: #ea4335"></p>
                      </div>
                    </div>
                  </li>

					<li>
						<div>
							<label class="medium mt-0">Type of cover :<span class="asterisk">*</span></label>
							<div class="label_right" >
								<select required="" class="medium showdependent" name="pd_type_of_cover[PLAN_COUNTER]">
									<option value="">Select</option>
									<option value="1" >Decreasing</option>
									<option value="2" >Level</option>
								</select>
								<div class="clearfix"></div>
								<p class="error" for="pd_type_of_cover_PLAN_COUNTER"></p>
							</div>
						</div>
					</li>
					<li>
						<div>
							<label class="medium mt-0">Policy type dyn:<span class="asterisk">*</span></label>
							<div class="label_right" >
								<select required="" class="medium showdependent policytype pd_type_of_policy_dyn" id="pd_type_of_policy_PLAN_COUNTER" data-id="PLAN_COUNTER" name="pd_type_of_policy[PLAN_COUNTER]" onchange="getval_PLAN_COUNTER(this);">
									<option value="">Select</option>
									<option value="1" >Term Life</option>
									<option value="2" >Term Life with Critical illness</option>
                                </select>
                                <div class="clearfix"></div>
								<span id="ageDisqualifies_PLAN_COUNTER" style="color:red;"></span>
                                <p class="error" for="pd_type_of_policy_PLAN_COUNTER"></p>
							</div>
						</div>
					</li>
					<li class="protectionclass" >
						<div>
							<label class="medium mt-0">Protection level: What is the amount of cover required? (In £ GB Pounds)<span class="asterisk">*</span></label>
							<div class="label_right" >
								<input required="" name="pd_sum_insured[PLAN_COUNTER]" id ="pd_sum_insured_PLAN_COUNTER" type="text" data-a-sep="," data-a-sign="GBP " data-d-group="3" data-m-dec="" class="medium numericOnly " data-validationtype="" onblur="onBlurMinimumLength('0.0000',0); onBlurMaximumValue('999999999.0000',0); " style="text-align:left" value=""  autocomplete="off">
								<input type="button" name="openPremium" value="Switch to premium amount" class="btn btn-primary">
								<div class="clearfix"></div>
								<span id="maxsuminsu_PLAN_COUNTER" style="color:red;"></span>
								<span id="autonumercMinimumValuePLAN_COUNTER"></span>
								<p class="error" for="pd_sum_insured_PLAN_COUNTER"></p>
							</div>
						</div>
					</li>
					
					<li class="premiumclass" style="display: none" >
						<div>
							<label class="medium mt-0">Do you have an allocated budget a or premium amount?</label>
							<div class="label_right">
								<input required="" name="pd_budget_si[PLAN_COUNTER]" type="text" data-a-sep="," data-a-sign="GBP " data-d-group="3" data-m-dec="" class="medium numericOnly " data-validationtype="" onblur="onBlurMinimumLength('0.0000',this.id); onBlurMaximumValue('999999999.0000',this.id); " style="text-align:left" value="" autocomplete="off">
								<input type="button" name="protectionLevel" value="Switch to SI amount" class="btn btn-primary">

								<div class="clearfix"></div>
								<p class="error" for="pd_budget_si_PLAN_COUNTER"></p>
							</div>
						</div>
					</li>
					<li class="totalpermdisbltyclass">
						<div id="pd_tpd_PLAN_COUNTER" style="display:none">
							<label class="medium mt-0">Total Permanent Disability? <span class="asterisk">*</span></label>
							<div class="label_right">
								<select required="" class="medium showdependent"  name="pd_tpd[PLAN_COUNTER]">
									<option value="">Select</option>
									<option value="1">Yes</option>
									<option value="2">No</option>
								</select>
								<div class="clearfix"></div>
								<p class="error" for="pd_tpd_PLAN_COUNTER"></p>
							</div>
						</div>
					</li>
					  <!-- <li>
						<div>
							<label class="medium mt-0">Waiver of Premium? :<span class="asterisk">*</span></label>
					 		<div class="label_right">
					 			<select required="" class="medium showdependent" name="pd_waiver_prem[PLAN_COUNTER]">
					 				<option value="">Select</option>
					 				<option value="1" >Yes</option>
					 				<option value="2" >No</option>
					 			</select>
					 			<div class="clearfix"></div>
					 			<p class="error" for="pd_waiver_prem_PLAN_COUNTER"></p>
					 		</div>
					 	</div>
					 </li> -->
					
					<li>
						<label class="medium mt-0"><a href="javascript:void(0);" class="remove_button btn btn-primary" value="Remove" title="Remove">Delete</a></label>
					</li>
					
					<script>
					$(document).ready(function(){
					$(".policytype").change(function(){
					var counter=$(this).attr("data-id"); // will return the string "123"
					var type = $(this).val();
					var name='pd_tpd_'+counter;
					if (type==1){
					$('#'+name).hide();
					}else{
					$('#'+name).css('display','block');
					}
					});
					});
					</script>				
					
				

				</ul>
			</div>
		</div>
	</div>
</div>
{literal}
<script type="text/javascript">			 
			 $(".pd_type_of_policy_dyn").change(function(){			
            console.log($(this).attr('data-id'));
		});
			</script>
<script type="text/javascript">
// $('#pd_type_of_policy_0').on('change', function() {
//   alert( this.value );
// });

</script>
<script type="text/javascript">

	$(document).ready(function(){
		$('#continueBtnWithQuote').click(function(){
		
	       $('#clientDeclarationPopUp').dialog('open');
	 });

	 $('#clientDeclarationCancel').click(function(){
		$('#clientDeclarationPopUp').dialog('close');
     });

	 $("#clientDeclarationPopUp").dialog({
			title: "Client Declaration",
			autoOpen: false,
			height: 500,
			width: 800,
			modal: true,
			responsive: true,
			open: function(event, ui) {
				$(".ui-dialog-titlebar-close").removeAttr('title');
			}
		});

		$('.numericOnly').autoNumeric('init');		
		const MAX_NUMBER_OF_PLAN_DETAILS = 10;
		let planDetailsCounter = $('#planDetailsCounter').val();
		var fieldHTML = $('#planTemplate').html();	
		if(planDetailsCounter == 0)
		{
			appendNewPlanDetailBlock(planDetailsCounter, fieldHTML);
			planDetailsCounter++;
			$('#planDetailsCounter').val(planDetailsCounter);
		}
		$('.add_button').unbind('click');
		$('.add_button').click(function(){
			

			let planDetailsCounter = $('#planDetailsCounter').val();
			if(planDetailsCounter < MAX_NUMBER_OF_PLAN_DETAILS)
			{
				appendNewPlanDetailBlock(planDetailsCounter, fieldHTML);
				planDetailsCounter++;
				$('#planDetailsCounter').val(planDetailsCounter);
			}
			else
			{
				//Prevent To Add new Block Message Implementation
			}
		});
		//Once remove button is clicked
		//$('.field_wrapper').on('click', '.remove_button', function(e){
		//	e.preventDefault();
		//	$(this).closest('.copyproduct').remove(); //Remove field html
		//});
	
		function appendNewPlanDetailBlock(planDetailsCounter,fieldHTML)
		{
			let fieldHTMLContent = fieldHTML.replace(/PLAN_COUNTER/g,planDetailsCounter.trim());
			$('.field_wrapper').append(fieldHTMLContent);
			
			$('.numericOnly').autoNumeric('init');
			var icons = {
				header: "ui-icon-circle-arrow-e",
				activeHeader: "ui-icon-circle-arrow-s"
			};			
			$(".accordiondiv").show().accordion({
				autoHeight: false,
				navigation: true,
				collapsible: true,
				icons: icons,
				heightStyle: "content"
			});
			$(".switchtoage").click(function () {
				$(".switchtermclass").hide();
				$(".swichageclass").show();

			});
			$(".switchtoterm").click(function () {
				$(".swichageclass").val(0);
				$(".switchtermclass").show();				
				
				$(".swichageclass").hide();
			});
			$("#pd_type_of_policy").change(function(){
			chktpd();
			});
			
			$( ".remove_button" ).unbind('click');
			$( ".remove_button" ).click(function() {
				let planDetailsCounter = $('#planDetailsCounter').val();
				planDetailsCounter--; //Decrement field counter
				$('#planDetailsCounter').val(planDetailsCounter);
				$(this).closest('.copyproduct').remove();
			});
		}

		$("#openPremium").click(function () {
			$(".premiumclass").show();
			$(".protectionclass").hide();
		});

		$("#protectionLevel").click(function () {
			$(".protectionclass").show();
			$(".premiumclass").hide();
		});

		$("#commSacrificePopUp").dialog({
			title: "Commission Sacrifice",
			autoOpen: false,
			height: 300,
			width: 400,
			modal: true,
			responsive: true,
			open: function(event, ui) {
				$(".ui-dialog-titlebar-close").removeAttr('title');
			}
		});
		
		$('#getCommSacrify').click(function(){
			$('#commSacrificePopUp').dialog('open');
		});

		$("#switchtoage").click(function () { 
			$(".switchtermclass").hide();
			$(".swichageclass").show();
		});
		
		$("#switchtoterm").click(function () { 
			$(".swichageclass").hide();
			$(".switchtermclass").show();
		});
		  
		var icons = {
			header: "ui-icon-circle-arrow-e",
			activeHeader: "ui-icon-circle-arrow-s"
		};
		
		$(".accordiondiv").show().accordion({
			autoHeight: false,
			navigation: true,
			collapsible: true,
			icons: icons,
			heightStyle: "content"
		});

		chktpd();
		$("#pd_type_of_policy").change(function(){
			chktpd();
		});
		
		//Lokenath check age New Quick Quote Call
		$(".getQuote").click(function () {
			var hasError = false;
			//$(".policytype").each(function(){
			$(".allproduct").find(".policytype").each(function(){
				var sequence = $(this).attr('data-id');
				var policy_type= $(this).val();
				var sum_insured = $('#pd_sum_insured_'+sequence).val();
				var conver_sumInsu = sum_insured.replace(/,/g, '');
				const final_sumInsu = conver_sumInsu.replaceAll("GBP", "")
				
				var client_age = $('#clientAge').val();
				var policy_term = $('#term_duration_'+sequence).val();
				var age = $('#term_duration_age_'+sequence).val();
				var type_of_policy_FH = $('#type_of_policy_FH_'+sequence).is(':checked'); 
    			var type_of_policy_SH = $('#type_of_policy_SH_'+sequence).is(':checked');
				
				if(policy_term >0 && age >0 ){
					alert('Please keep only one either policy terms or upto age');
				}
				if(final_sumInsu > 1000000){
					//alert('Sum insured checked')
					$('#maxsuminsu_'+sequence).html('Please chose amount less than or equal to 1000000');
					hasError = true;
				}
				if(age > 0){
							
						if(policy_type == 1 &&  (age < 18 || age > 70)){
						//alert('Please check your first policy upto age greater than 18 or less than or equal 70');
						$('#ageDisqualifies_'+sequence).html('Please check first policy upto age greater than 18 or less than or equal 70');
						hasError = true;
						}
						if(policy_type == 2 &&  (age < 18 || age > 65)){
						$('#ageDisqualifies_'+sequence).html('Please check second policy upto age greater than 18 or less than or equal 65');
						hasError = true;
						}
					}				
				if(type_of_policy_FH==true){
					
					if(policy_type == 1 && (client_age < 18 || client_age > 70)){
						// alert('Please check your first policy age greater than 18 or less than or equal 70');												
						 $('#ageDisqualifies_'+sequence).html('Please check your first policy age greater than 18 or less than or equal 70');
						 hasError = true;
						}else if(policy_type == 2 && (client_age < 18 || client_age > 65)){
						// alert('Please check your first policy age greater than 18 or less than or equal 65 for critical illness')							
						$('#ageDisqualifies_'+sequence).html('Please check your second policy age greater than 18 or less than or equal 65 for critical illness');
						hasError = true;		
						}
				}
				if(type_of_policy_FH == true && type_of_policy_SH == true ){
					var additionalClientAge = $('#additionalClientAge').val();
					if(policy_type == 1 && (client_age < 18 || client_age > 70)){
						// alert('Please check your first policy age greater than 18 or less than or equal 70');												
						$('#ageDisqualifies_'+sequence).html('Please check your first policy age greater than 18 or less than or equal 70');
						hasError = true;
					}else if(policy_type == 2 && (client_age < 18 || client_age > 65)){
						// alert('Please check your first policy age greater than 18 or less than or equal 65 for critical illness')							
							 $('#ageDisqualifies_'+sequence).html('Please check your second policy age greater than 18 or less than or equal 65 for critical illness');
							 hasError = true;
						}
					if(policy_type == 1 &&  (additionalClientAge < 18 || additionalClientAge > 70)){
						//alert('Please check your second policy age greater than 18 or less than or equal 70');
					$('#ageDisqualifies_'+sequence).html('Please check second policy age greater than 18 or less than or equal 70');
					hasError = true;
					}
					if(policy_type == 2 &&  (additionalClientAge < 18 || additionalClientAge > 65)){
						//alert('Please check your second policy age greater than 18 or less than or equal 65');
					$('#ageDisqualifies_'+sequence).html('Please check second policy age greater than 18 or less than or equal 65');
					hasError = true;
					}
				}
				console.log('sequence=',sequence);
				console.log('sum_insured=',sum_insured);
				console.log('policy_type=',policy_type);
				console.log('type_of_policy_FH=',type_of_policy_FH);
				console.log('type_of_policy_SH=',type_of_policy_SH);
				
  			});
			if(hasError){
				return false;
			}
				
			var pd_type_of_cover= $('#pd_type_of_cover').val();
			
			if(pd_type_of_cover==''){	
				$('#pd_type_of_cover_error').html("Please select type of cover.");
				return false;
			}else{
				$('#pd_type_of_cover_error').html("");
			}

			/*var type_of_policy= $('#type_of_policy').val();
			
			if(type_of_policy==''){	
				$('#type_of_policy_error').html("Please select type of policy.");
				return false;
			}else{
				$('#type_of_policy_error').html("");
			}*/

			var premium_frequency= $('#premium_frequency').val();
			
			if(premium_frequency==''){	
				$('#premium_frequency_error').html("Please select premium frequency.");
				return false;
			}else{
				$('#premium_frequency_error').html("");
			}

			var pd_type_of_policy= $('.pd_type_of_policy').val();
			if(pd_type_of_policy==''){
				$('#pd_type_of_policy_error').html("Please select policy type.");
				return false;
			}else{
				$('#pd_type_of_policy_error').html("");
			}

			
			var pd_sum_insured= $('#pd_sum_insured').val();
			if(pd_sum_insured==''){
				$('#pd_sum_insured_error').html("Please enter protection level.");
				return false;
			}else{
				$('#pd_sum_insured_error').html("");
			}
            
			var pd_tpd= $('#pd_tpd').val();
			if(pd_tpd==''){
				$('#pd_tpd_error').html("Please select total permanent disability.");
				return false;
			}else{
				$('#pd_tpd_error').html("");
			}
            
			var pd_waiver_prem= $('#pd_waiver_prem').val();
			if(pd_waiver_prem==''){
				$('#pd_waiver_prem_error').html("Please select waiver of premium.");
				return false;
			}else{
				$('#pd_waiver_prem_error').html("");
			}

			//var hasError = false;
			onQuickGetQuote();
			$('#commSacrificePopUp').dialog('close');
		});

		$('.tabGetQuote').show();
		$('.getQuoteBtnDiv').show();

		$("#continueWithQuote").click(function ( event ) {
			var premium = $('#quickPremium').val();
			if (premium != '') {
				premium = premium.replace("GBP ", "");
			}

			if (premium != '' && premium > 0) {
				$('#applicantDiv').hide();
				$('.tabGetQuote').hide();
				$('.tabGetUWQ').show();

				$('.getQuoteBtnDiv').hide();
				$('.riskBtnDiv').show();
				$('.progress li').each(function (index) {
					$(this).removeClass('is-active');
					if ($(this).attr('data-step') == 1 || $(this).attr('data-step') == 2) {
						$(this).addClass('is-visited');
					}
					if ($(this).attr('data-step') == 'U') {
						$(this).removeClass('is-visited');
						$(this).addClass('is-active');
					}
				});
				$('.stepUnderwriter').addClass('is-active');
				$('html, body').animate({
					scrollTop: $("body").offset().top
				}, 2000);
			}
			else {
				event.preventDefault();
				//alert('Invalid Quote. Please try again later.');
				$('#contWthQt').html('<p>Premium value should be greater than 0 </p>');
			}

		});

		$(".sendQuickQuote").click(function () {
			sendQuickQuote();
		});

		$("#BackToQuote").click(function () {
			$('#applicantDiv').show();
			$('.tabGetQuote').show();
			$('.getQuoteBtnDiv').show();

			$('.tabGetUWQ').hide();
			$('.riskBtnDiv').hide();

			$('.progress li').each(function (index) {
				$(this).removeClass('is-active');
				if ($(this).attr('data-step') == 1 || $(this).attr('data-step') == 'U') {
					$(this).addClass('is-visited');
				}
				if ($(this).attr('data-step') == 2) {
					$(this).removeClass('is-visited');
					$(this).addClass('is-active');
				}
			});

		});

		$('.progress li').each(function (index) {
			$(this).removeClass('is-active');
			if ($(this).attr('data-step') == 1 || $(this).attr('data-step') == 2) {
				$(this).addClass('is-visited');
			}
			if ($(this).attr('data-step') == 'U') {
				var self = this;
				$('.progress li').each(function () {
					if ($(this).attr('data-step') == 3 && ($(this).hasClass('is-visited'))) {
						$(self).addClass('is-visited');
					}
				});
			}
			if ($(this).attr('data-step') == 2) {
				$(this).removeClass('is-visited');
				$(this).addClass('is-active');
			}
			if ($(this).attr('data-step') == 3 && ($(this).hasClass('is-active'))) {
				$(this).addClass('is-visited');
			}
		});

	});
	
	function chktpd()
	{
		var type_of_policy = $("#pd_type_of_policy").val();
		if(type_of_policy == '2') {
			$("#pd_tpd_li").show();
		}
		else {
			$("#pd_tpd_li").hide();
		}
	}
	
	function onBlurMinimumLength(minimumValue,id)
	{
		var value = $('#'+id).val();
		value = parseFloat($('#'+id).autoNumeric('get'));
		
		if(minimumValue > value)
		{
			$('#autonumercMinimumValue'+id).html("Value cannot be under "+minimumValue);
			$('#autonumercMinimumValue'+id).fadeIn().delay(10000).fadeOut();
			$('#'+id).val('');
			$('#'+id).attr('value', '');
		}
	}

	function onBlurMaximumValue(maximumValue,id)
	{
		
		var value = $('#'+id).val();
		value = parseFloat($('#'+id).autoNumeric('get'));
		if(maximumValue < value)
		{
			$('#autonumercMinimumValue'+id).html("Value cannot be more than "+maximumValue);
			$('#autonumercMinimumValue'+id).fadeIn().delay(10000).fadeOut();
			$('#'+id).val('');
			$('#'+id).attr('value', '');
		}
	}

	function onQuickGetQuote()
	{
		var comm_sacrfc = $('#pd_comm_sacrfc').val();	
		var values = $('#clientPlanDetailsAdd').serializeArray();
		values.push({ name: "comm_sacrfc", value: comm_sacrfc });
		var url = webpath + 'clients/callQuickQuoteDividendLifeCoverBre';
		jQuery.ajax({
			type: "POST",
			url: url,
			data: values,
			async: true,
			beforeSend: function () {
				$("#preloader").show();
				$("#status").show();
			},
			error: function (errmsg) {
				//alert(errmsg);
			},
			success: function (msg) {
				if (msg != false) {

					//var currLoc = $(location).attr('href');
					//window.location.href=currLoc; 
					window.location.reload();
					
					/*const responseArr = JSON.parse(msg);
					if (responseArr['basePremium'] && responseArr['basePremium'] != '') {
						$("#quickPremium").val('GBP ' + parseFloat(responseArr['basePremium']).toFixed(2));
						$("#quickPremiumShow").html('<span>GBP</span>'+parseFloat(responseArr['basePremium']).toFixed(2));
						
						$("#quickQuotePremiumDiv").show();
						$("#getQuote").hide();
					}*/
				}
				else
					alert("ERROR");
			}
		}).done(function () {
			$("#preloader").hide();
			$("#status").hide();
		});
	}

	function sendQuickQuote() 
	{
		
		var businessEventId = $('#businessEventId').val();
		var url = webpath + 'clients/sendQuickQuote';
		var values = "businessEventId=" + businessEventId;
		jQuery.ajax({
			type: "POST",
			url: url,
			data: values,
			async: true,
			dataType: "json",
			beforeSend: function () {
				$("#preloader").show();
				$("#status").show();
				$('#success-msgmailsent').hide();
			},
			error: function (errmsg) {
				console.log("errmsg", errmsg);
			},
			success: function (res) {
				if(res.SUCCESS) {
					console.log('Mail sent successfully.');
					$('#success-msgmailsent').show();
					$('html, body').animate({
						scrollTop: $("body").offset().top
					}, 2000);
				}
				else
					console.log("ERROR", res);
			}
		}).done(function () {
			$("#preloader").hide();
			$("#status").hide();
		});
	}

	$(document).ready(function(){
  $(".policytype").change(function(){
	var counter=$(this).attr("data-id"); // will return the string "123"
	var type = $(this).val();
	var name='pd_tpd_'+counter;
    if (type==1){
		$('#'+name).hide();
    }else{
		$('#'+name).css('display','block');
	}
  });
});
	

</script>
{/literal}
	