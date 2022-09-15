<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, user-scalable=no">
		<title>Connecting...</title>
		<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
		<!--[if lt IE 9]>
		<script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		<script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<div class="col-xs-12">
			<h1 class="text-center">Connecting to Payment...</h1>
			<div class="text-center">
				<i class="fa fa-circle-o-notch fa-spin fa-5x fa-fw"></i>
			</div>
			<form id="scpayform" action="{$action}" method="POST">
				<input type="hidden" name="MerchantID" value="{$MerchantID}">
				<input type="hidden" name="CurrencyCode" value="{$CurrencyCode}">
				<input type="hidden" name="TxnAmount" value="{$TxnAmount}">
				<input type="hidden" name="MerchantOrderNo" value="{$MerchantOrderNo}">
				<input type="hidden" name="MerchantOrderDesc" value="{$MerchantOrderDesc}">
				<input type="hidden" name="MerchantRef1" value="{$MerchantRef1}">
				<input type="hidden" name="MerchantRef2" value="{$MerchantRef2}">
				<input type="hidden" name="MerchantRef3" value="{$MerchantRef3}">
				<input type="hidden" name="CustReference" value="{$CustReference}">
				<input type="hidden" name="CustName" value="{$CustName}">
				<input type="hidden" name="CustEmail" value="{$CustEmail}">
				<input type="hidden" name="CustPhoneNo" value="{$CustPhoneNo}">
				<input type="hidden" name="CustAddress1" value="{$CustAddress1}">
				<input type="hidden" name="CustAddress2" value="{$CustAddress2}">
				<input type="hidden" name="CustCountryCode" value="{$CustCountryCode}">
				<input type="hidden" name="CustAddressState" value="{$CustAddressState}">
				<input type="hidden" name="CustAddressCity" value="{$CustAddressCity}">
				<input type="hidden" name="RedirectUrl" value="{$RedirectUrl}">
				<input type="hidden" name="SCSign" value="{$SCSign}">
				
			</form>
		</div><!-- 12 -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		{literal}
		<script>
		$(document).ready(function(){
			$('#scpayform').submit();
		});
		</script>
		{/literal}
	</body>
</html>