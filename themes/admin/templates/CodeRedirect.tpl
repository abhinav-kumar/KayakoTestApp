<!DOCTYPE html>
<html>
<head>
<script language ="javascript" type="text/javascript">
function SetValueInParent(){
	<{if $_error }>
		window.close();
	<{/if}>
	<{if $_code }>
		window.opener.location.hash = 'bs_auth_code=' + '<{$_code}>';
	<{/if}>
	//close in one seconf
	setTimeout("window.close()",1000);
	return true;
}
</script>
</head>
<body onload='SetValueInParent();'>
	Please Wait....
</body>
</html>